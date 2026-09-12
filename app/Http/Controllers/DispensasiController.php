<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\User;
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
     * Query dasar dispensasi sesuai peran + filter dari request.
     * Dipakai bersama oleh index() (dipaginasi) dan ekspor() (diambil semua).
     */
    private function terfilter(Request $request)
    {
        $user = $request->user();
        $tab = $request->get('tab', 'semua');

        $query = Dispensasi::with('siswa.kelas', 'pengaju')->latest('tanggal')->latest('id');

        if ($user->role === 'waka') {
            // Waka: hanya yang sudah lolos piket
            $query->where('status_piket', 'approved');
        } else {
            // Guru: hanya pengajuan yang ia buat sendiri (sebagai piket).
            $query->where('diajukan_oleh_id', $user->id);
        }

        $query->when($tab === 'menunggu', fn ($q) => $q->where('status_akhir', 'pending'))
            ->when($tab === 'disetujui', fn ($q) => $q->where('status_akhir', 'approved'))
            ->when($tab === 'ditolak', fn ($q) => $q->where('status_akhir', 'rejected'))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->date('sampai')))
            ->when($request->filled('guru_id'), fn ($q) => $q->where('diajukan_oleh_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas(
                'siswa', fn ($q2) => $q2->where('kelas_id', $request->integer('kelas_id'))
            ));

        return $query;
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dispensasi.index', [
            'items' => $this->terfilter($request)->paginate(15)->withQueryString(),
            'tab' => $request->get('tab', 'semua'),
            'bolehAjukan' => $user->isPiket(),
            'bolehEkspor' => $user->role === 'waka' || $user->isPiket(),
            // Filter guru piket cuma relevan buat waka (guru piket cuma lihat punyanya sendiri).
            'guruPiketList' => $user->role === 'waka'
                ? User::where('role', 'guru')->whereHas('guru.jadwalPikets')->orderBy('name')->get()
                : collect(),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    /** Ekspor laporan dispensasi (kegiatan piket) sebagai CSV, ikut filter yang sedang aktif. */
    public function ekspor(Request $request)
    {
        abort_unless($request->user()->role === 'waka' || $request->user()->isPiket(), 403);

        $rows = $this->terfilter($request)->get();

        $namaFile = 'laporan-dispensasi-'.now()->format('Y-m-d_His').'.csv';

        AuditLog::catat('Ekspor Laporan Dispensasi', "Ekspor laporan dispensasi ({$rows->count()} baris)");

        return Response::streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Nama Siswa', 'Kelas', 'Jam', 'Alasan', 'Diajukan Oleh (Piket)', 'Status Piket', 'Status Waka', 'Status Akhir', 'Catatan Waka', 'Bukti']);

            foreach ($rows as $d) {
                fputcsv($out, [
                    $d->tanggal->format('Y-m-d'),
                    $d->siswa->nama,
                    $d->siswa->kelas?->nama ?? '-',
                    $d->jam_ke_mulai ? "JP {$d->jam_ke_mulai}-{$d->jam_ke_selesai}" : 'Sehari penuh',
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
            'kelasList' => Kelas::with('siswas:id,kelas_id,nama,nis')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanPiket();

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            'jam_ke_mulai' => ['nullable', 'required_with:jam_ke_selesai', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['nullable', 'required_with:jam_ke_mulai', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
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

        return redirect()->route('dispensasi.index')
            ->with('success', 'Dispensasi diajukan. Menunggu persetujuan Waka Kesiswaan.');
    }

    public function show(Dispensasi $dispensasi): View
    {
        $user = auth()->user();

        // Guru piket hanya boleh melihat pengajuannya sendiri; waka boleh semua.
        abort_unless($user->role === 'waka' || $dispensasi->diajukan_oleh_id === $user->id, 403);

        $dispensasi->load('siswa.kelas', 'pengaju', 'waka');

        return view('dispensasi.show', [
            'dispensasi' => $dispensasi,
            'bisaWaka' => $user->role === 'waka' && $dispensasi->status_waka === 'pending',
            'bisaBatal' => $dispensasi->diajukan_oleh_id === $user->id
                && $dispensasi->status_waka === 'pending',
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

        return redirect()->route('dispensasi.index')->with(
            'success',
            $dispensasi->status_akhir === 'approved'
                ? 'Dispensasi disetujui. Presensi siswa otomatis diperbarui.'
                : 'Keputusan Waka disimpan.'
        );
    }
}
