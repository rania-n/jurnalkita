<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\User;
use App\Notifications\DispensasiBaru;
use App\Notifications\DispensasiDiputuskan;
use App\Support\NotifikasiDispensasi;
use App\Support\Versi;
use App\Support\WaLink;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'Hanya guru piket, Waka, dan admin yang bisa melihat dispensasi.'
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
            ->kelompokUtama()
            ->latest('tanggal')->latest('id');

        $query->when($tab === 'menunggu', fn ($q) => $q->where('status_akhir', 'pending'))
            ->when($tab === 'disetujui', fn ($q) => $q->masihBerlaku())
            ->when($tab === 'kadaluarsa', fn ($q) => $q->kadaluarsa())
            ->when($tab === 'ditolak', fn ($q) => $q->where('status_akhir', 'rejected'))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->date('sampai')))
            ->when($request->filled('guru_id'), fn ($q) => $q->where('diajukan_oleh_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($q) => $q->where(function ($filter) use ($request) {
                $filter->whereHas('siswa', fn ($siswa) => $siswa->where('kelas_id', $request->integer('kelas_id')))
                    ->orWhereIn('kelompok_id', Dispensasi::select('kelompok_id')->whereNotNull('kelompok_id')
                        ->whereHas('siswa', fn ($siswa) => $siswa->where('kelas_id', $request->integer('kelas_id'))));
            }))
            ->when($request->filled('cari'), fn ($q) => $q->where(function ($filter) use ($request) {
                $kata = '%'.$request->string('cari').'%';
                $cariSiswa = fn ($siswa) => $siswa->where('nama', 'like', $kata)->orWhere('nis', 'like', $kata);
                $filter->whereHas('siswa', $cariSiswa)
                    ->orWhereIn('kelompok_id', Dispensasi::select('kelompok_id')->whereNotNull('kelompok_id')->whereHas('siswa', $cariSiswa));
            }));

        return $query;
    }

    public function index(Request $request): View
    {
        $this->pastikanBolehLihat();
        $user = $request->user();

        // Sapu dispensasi pending yang udah kelewat tanggal (Waka nggak sempat
        // mutusin) jadi otomatis batal, biar tab Menunggu/Ditolak selalu akurat
        // tiap kali halaman ini dibuka -- lihat Dispensasi::batalkanSemuaKadaluarsa().
        Dispensasi::batalkanSemuaKadaluarsa();

        // Baru saja ngajuin (?kirim_wa=<id>, lihat store()) -> link WA-nya
        // di-generate di sini & dibukakan otomatis lewat JS di view. Sengaja
        // TIDAK redirect ke halaman detail buat ini (dulu gitu) -- meta-refresh
        // ke wa.me nambah 2 entri history baru, jadi kalau piket pencet "back"
        // abis batal kirim WA malah nyasar balik ke form kosong, bukan ke sini.
        $waLinkAutoKirim = null;
        if ($request->filled('kirim_wa')) {
            $dispensasiBaru = Dispensasi::find($request->integer('kirim_wa'));
            if ($dispensasiBaru && $dispensasiBaru->diajukan_oleh_id === $user->id) {
                $waLinkAutoKirim = $this->waLinkUntukWaka($dispensasiBaru);
            }
        }

        // Hitung jumlah per tab (tanpa filter tab, tapi ikut filter lain)
        $baseQuery = fn () => Dispensasi::with('siswa.kelas', 'pengaju')
            ->where('status_piket', 'approved')
            ->kelompokUtama()
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->date('sampai')))
            ->when($request->filled('kelas_id'), fn ($q) => $q->where(function ($filter) use ($request) {
                $filter->whereHas('siswa', fn ($siswa) => $siswa->where('kelas_id', $request->integer('kelas_id')))
                    ->orWhereIn('kelompok_id', Dispensasi::select('kelompok_id')->whereNotNull('kelompok_id')
                        ->whereHas('siswa', fn ($siswa) => $siswa->where('kelas_id', $request->integer('kelas_id'))));
            }))
            ->when($request->filled('cari'), fn ($q) => $q->where(function ($filter) use ($request) {
                $kata = '%'.$request->string('cari').'%';
                $cariSiswa = fn ($siswa) => $siswa->where('nama', 'like', $kata)->orWhere('nis', 'like', $kata);
                $filter->whereHas('siswa', $cariSiswa)
                    ->orWhereIn('kelompok_id', Dispensasi::select('kelompok_id')->whereNotNull('kelompok_id')->whereHas('siswa', $cariSiswa));
            }));

        $jumlahTab = [
            'semua' => $baseQuery()->count(),
            'menunggu' => $baseQuery()->where('status_akhir', 'pending')->count(),
            'disetujui' => $baseQuery()->masihBerlaku()->count(),
            'kadaluarsa' => $baseQuery()->kadaluarsa()->count(),
            'ditolak' => $baseQuery()->where('status_akhir', 'rejected')->count(),
        ];

        // Halaman detail dispensasi (dispensasi.show) udah dihapus -- semua
        // "lihat detail" sekarang lewat popup di halaman ini. Tempat lain
        // yang dulu redirect/link ke situ (habis keputusan Waka, notifikasi,
        // dst) sekarang ke sini bawa ?lihat=<id>, popup-nya kebuka otomatis
        // -- nggak peduli dispensasinya ada di halaman pagination yang mana.
        $lihatDispensasi = $request->filled('lihat')
            ? Dispensasi::with('siswa')->find($request->integer('lihat'))
            : null;

        return view('dispensasi.index', [
            'items' => $this->terfilter($request)->paginate(15)->withQueryString(),
            'tab' => $request->get('tab', 'semua'),
            'bolehAjukan' => $user->isPiket(),
            'bolehEkspor' => in_array($user->role, ['waka', 'admin'], true) || $user->isPiket(),
            'kelasList' => Kelas::orderedByHierarchy()->get(),
            'jumlahTab' => $jumlahTab,
            'waLinkAutoKirim' => $waLinkAutoKirim,
            'lihatDispensasi' => $lihatDispensasi,
        ]);
    }

    /** Endpoint ringan buat di-poll (initAutoRefresh()) -- lihat App\Support\Versi. */
    public function versi(): JsonResponse
    {
        $this->pastikanBolehLihat();

        // Scope-nya HARUS sama dengan terfilter() -- kalau beda, fingerprint-nya
        // bisa berubah padahal tampilan nggak (atau sebaliknya). Contoh: kalau
        // dihitung tanpa kelompokUtama(), tiap kali ada anggota grup baru,
        // count-nya naik dan banner "ada data baru" nongol padahal daftar yang
        // tampil tetap sama (anggota grup disembunyikan, cuma 1 baris utama
        // yang tampil).
        return response()->json([
            'versi' => Versi::dari(Dispensasi::where('status_piket', 'approved')->kelompokUtama()),
        ]);
    }

    /** Ekspor laporan dispensasi (kegiatan piket) sebagai PDF, ikut filter yang sedang aktif. */
    public function ekspor(Request $request)
    {
        $this->pastikanBolehLihat();

        $rows = $this->terfilter($request)->get();

        $namaFile = 'laporan-dispensasi-'.now()->format('Y-m-d_His').'.pdf';

        AuditLog::catat('Ekspor Laporan Dispensasi', "Ekspor laporan dispensasi ({$rows->count()} baris)");

        $filterInfo = [];
        if ($request->filled('status')) {
            $filterInfo[] = 'Status: '.ucfirst($request->status);
        }
        if ($request->filled('kelas_id')) {
            $kelas = Kelas::find($request->kelas_id);
            if ($kelas) {
                $filterInfo[] = 'Kelas: '.$kelas->nama;
            }
        }
        if ($request->filled('tanggal')) {
            $filterInfo[] = 'Tanggal: '.$request->tanggal;
        }

        $pdf = Pdf::loadView('pdf.laporan-dispensasi', [
            'judul' => 'Laporan Dispensasi Siswa',
            'daftar' => $rows,
            'filterInfo' => implode(' | ', $filterInfo),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($namaFile);
    }

    public function create(): View
    {
        $this->pastikanPiket();

        $kelasList = Kelas::aktif()
            ->with(['siswas' => fn ($q) => $q->where('status', 'aktif')->select('id', 'kelas_id', 'nama', 'nis')])
            ->orderedByHierarchy()->get();

        // Diratakan jadi 1 daftar buat kotak "cari siswa" -- ketik nama/NIS
        // langsung, nggak perlu tahu/pilih kelasnya dulu (dulu 1 <select>
        // raksasa dikelompokkan per kelas, capek nyarinya kalau lupa kelasnya).
        $siswaList = $kelasList->flatMap(fn ($k) => $k->siswas->map(fn ($s) => [
            'id' => $s->id, 'nama' => "{$s->nama} · {$k->nama} · {$s->nis}",
        ]))->sortBy('nama')->values();

        return view('dispensasi.create', ['siswaList' => $siswaList]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanPiket();

        if (! $request->has('siswa_ids') && $request->filled('siswa_id')) {
            $request->merge(['siswa_ids' => [$request->input('siswa_id')]]);
        }

        $data = $request->validate([
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            // Kosong = 1 hari saja. Diisi = dispensasi berlaku beberapa hari sekaligus.
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal'],
            // jam_ke_mulai wajib ADA kalau jam_ke_selesai diisi, tapi mulai boleh sendirian
            // (artinya "dari jam segini sampai selesai hari itu").
            'jam_ke_mulai' => ['nullable', 'required_with:jam_ke_selesai', 'integer', 'min:1', 'max:20'],
            'jam_ke_selesai' => ['nullable', 'integer', 'min:1', 'max:20', 'gte:jam_ke_mulai'],
            'alasan' => ['required', 'string'],
            'jenis' => ['nullable', 'string', 'in:izin_keluar,lomba'],
            'surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $jenis = $data['jenis'] ?? 'izin_keluar'; // Default to izin_keluar since this is the Izin Keluar form now

        if (in_array($jenis, ['sakit', 'izin_terlambat']) && ! $request->hasFile('surat')) {
            return back()->withInput()->withErrors(['surat' => 'Bukti surat wajib diunggah untuk jenis dispensasi ini.']);
        }

        $suratPath = $request->file('surat')?->store('dispensasi-surat', 'public');
        $dataPengajuan = collect($data)->except(['siswa_ids', 'surat'])->all();
        $dataPengajuan['jenis'] = $jenis;
        $kelompokId = count($data['siswa_ids']) > 1 ? (string) Str::uuid() : null;
        $dispensasis = DB::transaction(function () use ($data, $dataPengajuan, $suratPath, $kelompokId) {
            return collect($data['siswa_ids'])->map(function ($siswaId) use ($dataPengajuan, $suratPath, $kelompokId) {
                $dispensasi = Dispensasi::create([
                    ...$dataPengajuan,
                    'kelompok_id' => $kelompokId,
                    'siswa_id' => $siswaId,
                    'diajukan_oleh_id' => auth()->id(),
                    'surat_path' => $suratPath,
                    'status_piket' => 'approved',
                    'piket_id' => auth()->id(),
                    'status_waka' => $dataPengajuan['jenis'] === 'lomba' ? 'approved' : 'pending',
                ]);
                $dispensasi->segarkanStatusAkhir();
                AuditLog::catat('Ajukan Dispensasi', "Ajukan dispensasi siswa #{$dispensasi->siswa_id}", $dispensasi);

                return $dispensasi;
            });
        });

        $dispensasi = $dispensasis->firstOrFail();

        if ($jenis === 'lomba') {
            // Lomba auto-approve: guru pengajar, piket, & pengurus kelas tetap
            // dapat notifikasi in-app walau nggak lewat persetujuan Waka.
            NotifikasiDispensasi::saatDisetujui($dispensasi);
        } else {
            foreach (User::where('role', 'waka')->get() as $waka) {
                $waka->notify(new DispensasiBaru($dispensasi));
            }
            NotifikasiDispensasi::saatDiajukan($dispensasi);
        }

        // Balik ke Riwayat (bukan halaman detail) -- link WA-nya dibukakan
        // otomatis dari SANA (lihat index()), biar piket nggak perlu tap
        // "Kirim Link" lagi TAPI juga nggak nyangkut di halaman detail/form
        // kalau kirim WA-nya dibatalkan (lihat catatan di index()). 'lihat'
        // ikut dikirim juga biar popup detailnya langsung kebuka otomatis di
        // atas Riwayat (pola yang sama kayak abis Waka mutusin), jadi piket
        // langsung lihat ringkasannya tanpa harus tap "Lihat" manual lagi.
        $pesan = $jenis === 'lomba'
            ? $dispensasis->count().' siswa dicatat untuk kegiatan Lomba/Dinas. Pengajuan otomatis disetujui tanpa perlu Waka.'
            : $dispensasis->count().' siswa diajukan untuk izin keluar. Menunggu persetujuan Waka.';

        return redirect()->route('dispensasi.index', [
            'kirim_wa' => $jenis !== 'lomba' ? $dispensasi->id : null,
            'lihat' => $dispensasi->id,
        ])->with('success', $pesan);
    }

    /** Link WA ke Waka yang bertugas hari ini buat minta persetujuan dispensasi ini. */
    private function waLinkUntukWaka(Dispensasi $dispensasi): ?string
    {
        if ($dispensasi->status_waka !== 'pending') {
            return null;
        }

        // Waka juga gantian shift per hari (kayak guru piket) -- link WA diarahkan ke
        // yang beneran bertugas hari ini, bukan asal Waka pertama di database.
        $waka = User::wakaUntukHariIni();
        if (! $waka) {
            return null;
        }

        $tautan = SuratDispensasiController::tautanPersetujuan($dispensasi, $waka);

        $anggota = $dispensasi->anggotaKelompok();
        $namaSiswa = $anggota->map(fn ($item) => $item->siswa->nama.' ('.($item->siswa->kelas?->nama ?? '—').')')->join(', ');

        return WaLink::url($waka->no_hp, "Permohonan dispensasi {$anggota->count()} siswa:\n\n"
            ."Nama: {$namaSiswa}\n"
            ."Alasan: {$dispensasi->alasan}\n\n"
            ."Setujui/tolak lewat tautan ini:\n{$tautan}");
    }

    /**
     * Detail dispensasi -- SELALU popup (fragment HTML tanpa layout), dibuka
     * dari Riwayat Dispensasi lewat AJAX. Nggak ada lagi halaman penuh buat
     * ini -- sengaja dihapus (dulu ada, masih bisa diakses langsung lewat
     * URL walau harusnya cuma popup, bikin bingung).
     */
    public function showFragment(Dispensasi $dispensasi): View
    {
        $this->pastikanBolehLihat();
        $user = auth()->user();

        $dispensasi->batalkanKalauKadaluarsa();
        $dispensasi->load('siswa.kelas', 'pengaju', 'waka');
        $waLinkWaka = $this->waLinkUntukWaka($dispensasi);

        $waLinkSiswa = null;
        if ($dispensasi->status_akhir === 'approved' && $dispensasi->no_hp) {
            $tautanSurat = SuratDispensasiController::tautanSurat($dispensasi);
            $waLinkSiswa = WaLink::url($dispensasi->no_hp, "Dispensasi sudah *disetujui*.\n\n"
                ."Tunjukkan surat ini ke satpam saat keluar sekolah:\n{$tautanSurat}");
        }

        return view('dispensasi._detail-fragment', [
            'dispensasi' => $dispensasi,
            'bisaWaka' => $user->role === 'waka' && $dispensasi->status_waka === 'pending',
            'bisaBatal' => $dispensasi->diajukan_oleh_id === $user->id
                && $dispensasi->status_waka === 'pending',
            // Piket kadang lupa isi No. HP pas ngajuin (field-nya opsional) --
            // boleh diisi belakangan kapan saja (nggak cuma sekali di form
            // awal), oleh piket ATAU Waka, bukan cuma pengaju aslinya.
            'bisaUbahNoHp' => $dispensasi->status_akhir === 'approved'
                && ($user->role === 'waka' || $user->isPiket()),
            'waLinkWaka' => $waLinkWaka,
            'waLinkSiswa' => $waLinkSiswa,
        ]);
    }

    /**
     * Isi/ubah No. HP belakangan -- kalau kosong pas ngajuin (field-nya
     * opsional), satu-satunya cara "kirim bukti via WA" jadi kepakai adalah
     * ngisi ini SEKARANG, nggak peduli status persetujuan lagi apa.
     */
    public function updateNoHp(Dispensasi $dispensasi, Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->role === 'waka' || $user->isPiket(), 403, 'Hanya guru piket dan Waka yang bisa mengisi ini.');
        abort_unless($dispensasi->status_akhir === 'approved', 403, 'Nomor WhatsApp dapat diisi setelah dispensasi disetujui.');

        $data = $request->validate([
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
        ]);

        $dispensasi->anggotaKelompok()->each->update($data);

        return redirect()->route('dispensasi.index', ['lihat' => $dispensasi->id])
            ->with('success', 'Nomor WhatsApp disimpan. Bukti dispensasi sekarang dapat dikirim.');
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
        $dispensasi->batalkanKalauKadaluarsa();
        abort_unless(
            $dispensasi->status_waka === 'pending',
            403,
            'Sudah diputuskan Waka, tidak bisa dibatalkan.'
        );

        $anggota = $dispensasi->anggotaKelompok();
        $nama = $anggota->pluck('siswa.nama')->join(', ');
        $anggota->each->delete();

        AuditLog::catat('Batalkan Dispensasi', "Batalkan dispensasi {$nama}", $dispensasi);

        return redirect()->route('dispensasi.index')->with('success', 'Pengajuan dispensasi dibatalkan.');
    }

    public function approveWaka(Dispensasi $dispensasi, Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'waka', 403);

        if ($dispensasi->batalkanKalauKadaluarsa()) {
            return redirect()->route('dispensasi.index', ['lihat' => $dispensasi->id])
                ->with('error', 'Dispensasi ini sudah kedaluwarsa (melewati tanggal berlaku tanpa keputusan) dan otomatis dibatalkan.');
        }
        abort_unless($dispensasi->status_waka === 'pending', 403);

        $data = $request->validate([
            'keputusan' => ['required', 'in:approved,rejected'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $anggota = $dispensasi->anggotaKelompok();
        DB::transaction(function () use ($anggota, $data) {
            foreach ($anggota as $item) {
                $item->update([
                    'status_waka' => $data['keputusan'],
                    'waka_id' => auth()->id(),
                    'catatan_waka' => $data['catatan'] ?? null,
                ]);
                $item->segarkanStatusAkhir();
            }
        });
        $dispensasi->refresh();

        AuditLog::catat('Keputusan Waka Dispensasi', "Waka {$data['keputusan']} dispensasi #{$dispensasi->id}", $dispensasi);

        $dispensasi->pengaju?->notify(new DispensasiDiputuskan($dispensasi));

        if ($dispensasi->status_akhir === 'approved') {
            // Guru pengajar (SiswaDispensasiDiKelasAnda) + piket + pengurus
            // kelas -- lihat App\Support\NotifikasiDispensasi.
            NotifikasiDispensasi::saatDisetujui($dispensasi);
        }

        return redirect()->route('dispensasi.index', ['lihat' => $dispensasi->id])->with(
            'success',
            $dispensasi->status_akhir === 'approved'
                ? "Dispensasi {$anggota->count()} siswa disetujui. Presensi otomatis diperbarui."
                : 'Keputusan Waka disimpan.'
        );
    }
}
