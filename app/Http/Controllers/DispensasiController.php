<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\User;
use App\Notifications\DispensasiDiputuskan;
use App\Support\WaLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DispensasiController extends Controller
{
    private function pastikanPiket(): void
    {
        abort_unless(auth()->user()->isPiket(), 403, 'Hanya guru piket yang dapat mengakses ini.');
    }

    /**
     * Dispensasi itu ranahnya guru PIKET + Waka (+ admin buat oversight) -- bukan
     * "milik" guru yang mengajukan doang, tapi juga BUKAN buat semua guru. Guru yang
     * nggak pernah kebagian piket sama sekali tidak relevan lihat ini.
     */
    private function pastikanBolehLihat(): void
    {
        $user = auth()->user();
        abort_unless(
            in_array($user->role, ['waka', 'admin'], true) || $user->isPiket(),
            403,
            'Hanya guru piket, Waka Kesiswaan, dan admin yang bisa melihat dispensasi.'
        );
    }

    /**
     * Query dasar dispensasi + filter dari request. Dipakai bersama oleh index()
     * (dipaginasi) dan ekspor() (diambil semua).
     */
    private function terfilter(Request $request)
    {
        $tab = $request->get('tab', 'semua');

        $query = Dispensasi::with('siswa.kelas', 'pengaju')
            ->where('status_piket', 'approved')
            ->latest('tanggal')->latest('id');

        $query->when($tab === 'menunggu', fn ($q) => $q->where('status_akhir', 'pending'))
            ->when($tab === 'disetujui', fn ($q) => $q->masihBerlaku())
            ->when($tab === 'kadaluarsa', fn ($q) => $q->kadaluarsa())
            ->when($tab === 'ditolak', fn ($q) => $q->where('status_akhir', 'rejected'))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->date('sampai')))
            ->when($request->filled('guru_id'), fn ($q) => $q->where('diajukan_oleh_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas(
                'siswa', fn ($q2) => $q2->where('kelas_id', $request->integer('kelas_id'))
            ))
            ->when($request->filled('cari'), fn ($q) => $q->whereHas(
                'siswa', fn ($q2) => $q2->where('nama', 'like', '%'.$request->string('cari').'%')
                    ->orWhere('nis', 'like', '%'.$request->string('cari').'%')
            ));

        return $query;
    }

    public function index(Request $request): View
    {
        $this->pastikanBolehLihat();
        $user = $request->user();

        return view('dispensasi.index', [
            'items' => $this->terfilter($request)->paginate(15)->withQueryString(),
            'tab' => $request->get('tab', 'semua'),
            'bolehAjukan' => $user->isPiket(),
            'bolehEkspor' => in_array($user->role, ['waka', 'admin'], true) || $user->isPiket(),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    /** Ekspor laporan dispensasi (kegiatan piket) sebagai CSV, ikut filter yang sedang aktif. */
    public function ekspor(Request $request)
    {
        $this->pastikanBolehLihat();

        $rows = $this->terfilter($request)->get();

        $namaFile = 'laporan-dispensasi-'.now()->format('Y-m-d_His').'.csv';

        AuditLog::catat('Ekspor Laporan Dispensasi', "Ekspor laporan dispensasi ({$rows->count()} baris)");

        return Response::streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Nama Siswa', 'Kelas', 'Jam', 'Alasan', 'Diajukan Oleh (Piket)', 'Status Piket', 'Status Waka', 'Status Akhir', 'Catatan Waka', 'Bukti']);

            foreach ($rows as $d) {
                fputcsv($out, [
                    $d->tanggal->format('Y-m-d').($d->multiHari() ? ' s/d '.$d->tanggal_selesai->format('Y-m-d') : ''),
                    $d->siswa->nama,
                    $d->siswa->kelas?->nama ?? '-',
                    $d->labelJam(),
                    $d->alasan,
                    $d->pengaju->name,
                    $d->status_piket,
                    $d->status_waka,
                    $d->status_akhir,
                    $d->catatan_waka ?? '-',
                    $d->surat_path ? url(Storage::url($d->surat_path)) : 'Tidak ada',
                ]);
            }

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv']);
    }

    public function create(): View
    {
        $this->pastikanPiket();

        return view('dispensasi.create', [
            'kelasList' => Kelas::aktif()
                ->with(['siswas' => fn ($q) => $q->where('status', 'aktif')->select('id', 'kelas_id', 'nama', 'nis')])
                ->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanPiket();

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            // Kosong = 1 hari saja. Diisi = dispensasi berlaku beberapa hari sekaligus.
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal'],
            // jam_ke_mulai wajib ADA kalau jam_ke_selesai diisi, tapi mulai boleh sendirian
            // (artinya "dari jam segini sampai selesai hari itu").
            'jam_ke_mulai' => ['nullable', 'required_with:jam_ke_selesai', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['nullable', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'alasan' => ['required', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $dispensasi = Dispensasi::create([
            ...collect($data)->except('surat')->all(),
            'diajukan_oleh_id' => auth()->id(),
            'surat_path' => $request->file('surat')?->store('dispensasi-surat', 'public'),
            // Pengaju = guru piket, jadi tahap piket otomatis lolos.
            'status_piket' => 'approved',
            'piket_id' => auth()->id(),
        ]);
        $dispensasi->segarkanStatusAkhir();

        AuditLog::catat('Ajukan Dispensasi', "Ajukan dispensasi siswa #{$dispensasi->siswa_id}", $dispensasi);

        // Langsung ke halaman detail dengan tanda "kirim=1" -> otomatis kebuka WhatsApp
        // (lihat show()), jadi piket nggak perlu tap tombol "Kirim Link" secara terpisah.
        return redirect()->route('dispensasi.show', [$dispensasi, 'kirim' => 1])
            ->with('success', 'Dispensasi diajukan. Menunggu persetujuan Waka Kesiswaan.');
    }

    public function show(Request $request, Dispensasi $dispensasi): View
    {
        $this->pastikanBolehLihat();
        $user = auth()->user();

        // Bukan lagi dibatasi kepemilikan (siapa yang mengajukan) -- tapi tetap
        // dibatasi ranahnya piket+waka+admin lewat pastikanBolehLihat() di atas.

        $dispensasi->load('siswa.kelas', 'pengaju', 'waka');

        // Waka juga gantian shift per hari (kayak guru piket) -- link WA diarahkan ke
        // yang beneran bertugas hari ini, bukan asal Waka pertama di database.
        $waka = User::wakaUntukHariIni();
        $waLinkWaka = null;
        if ($dispensasi->status_waka === 'pending' && $waka) {
            $tautan = SuratDispensasiController::tautanPersetujuan($dispensasi, $waka);
            $waLinkWaka = WaLink::url($waka->no_hp, "Permohonan dispensasi siswa:\n\n"
                ."Nama: {$dispensasi->siswa->nama}\n"
                ."Kelas: {$dispensasi->siswa->kelas?->nama}\n"
                ."Alasan: {$dispensasi->alasan}\n\n"
                ."Setujui/tolak lewat tautan ini:\n{$tautan}");
        }

        $waLinkSiswa = null;
        if ($dispensasi->status_akhir === 'approved' && $dispensasi->no_hp) {
            $tautanSurat = SuratDispensasiController::tautanSurat($dispensasi);
            $waLinkSiswa = WaLink::url($dispensasi->no_hp, "Dispensasi kamu sudah *disetujui*.\n\n"
                ."Tunjukkan surat ini ke satpam saat keluar sekolah:\n{$tautanSurat}");
        }

        return view('dispensasi.show', [
            'dispensasi' => $dispensasi,
            'bisaWaka' => $user->role === 'waka' && $dispensasi->status_waka === 'pending',
            'bisaBatal' => $dispensasi->diajukan_oleh_id === $user->id
                && $dispensasi->status_waka === 'pending',
            'waLinkWaka' => $waLinkWaka,
            'waLinkSiswa' => $waLinkSiswa,
            // Baru saja diajukan (?kirim=1) -> langsung dibukakan WhatsApp-nya, piket
            // nggak perlu tap tombol "Kirim Link" lagi. Tetap harus tap "Kirim" di
            // dalam WhatsApp sendiri -- itu batasan wa.me, tidak bisa dikirim otomatis
            // tanpa API berbayar.
            'autoKirimWa' => $request->boolean('kirim') && $waLinkWaka,
        ]);
    }

    /**
     * Batalkan pengajuan (soft delete).
     * Hanya oleh guru piket yang mengajukan, dan selama Waka belum memutuskan.
     */
    public function destroy(Dispensasi $dispensasi): RedirectResponse
    {
        $this->pastikanPiket();
        abort_unless(
            $dispensasi->diajukan_oleh_id === auth()->id(),
            403,
            'Hanya guru piket yang mengajukan yang bisa membatalkan.'
        );
        abort_unless(
            $dispensasi->status_waka === 'pending',
            403,
            'Sudah diputuskan Waka Kesiswaan, tidak bisa dibatalkan.'
        );

        $nama = $dispensasi->siswa->nama;
        $dispensasi->delete();

        AuditLog::catat('Batalkan Dispensasi', "Batalkan dispensasi {$nama}", $dispensasi);

        return redirect()->route('dispensasi.index')->with('success', 'Pengajuan dispensasi dibatalkan.');
    }

    public function approveWaka(Dispensasi $dispensasi, Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'waka', 403);
        abort_unless($dispensasi->status_waka === 'pending', 403);

        $data = $request->validate([
            'keputusan' => ['required', 'in:approved,rejected'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $dispensasi->update([
            'status_waka' => $data['keputusan'],
            'waka_id' => auth()->id(),
            'catatan_waka' => $data['catatan'] ?? null,
        ]);
        $dispensasi->segarkanStatusAkhir();

        AuditLog::catat('Keputusan Waka Dispensasi', "Waka {$data['keputusan']} dispensasi #{$dispensasi->id}", $dispensasi);

        $dispensasi->pengaju?->notify(new DispensasiDiputuskan($dispensasi));

        return redirect()->route('dispensasi.index')->with(
            'success',
            $dispensasi->status_akhir === 'approved'
                ? 'Dispensasi disetujui. Presensi siswa otomatis diperbarui.'
                : 'Keputusan Waka disimpan.'
        );
    }
}
