<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Wali kelas bukan role terpisah (kayak piket) -- guru biasa yang jadi wali_id di
 * kelas manapun. Fiturnya sengaja dibatasi ke rekap kehadiran doang (bukan CRUD
 * penuh), mirip App\Http\Controllers\Sekretaris\KelasController::rekap() tapi dari
 * sisi guru.
 */
class WaliKelasController extends Controller
{
    /** Kalau cuma wali 1 kelas, langsung tampilkan rekapnya. Kalau lebih, pilih dulu. */
    public function index(): View
    {
        $kelasList = auth()->user()->kelasWaliList();
        abort_if($kelasList->isEmpty(), 403, 'Anda bukan wali kelas manapun.');

        if ($kelasList->count() === 1) {
            return $this->rekap($kelasList->first());
        }

        return view('guru.wali-kelas.pilih', compact('kelasList'));
    }

    public function rekap(Kelas $kelas): View
    {
        abort_unless($kelas->wali_id === auth()->user()->guru?->id, 403, 'Anda bukan wali kelas ini.');

        // request() dipakai (bukan Request $request di-inject) soalnya method
        // ini juga dipanggil LANGSUNG dari index() -- kalau di-type-hint,
        // panggilan manual $this->rekap($kelas) di atas nggak dapat instance
        // Request-nya.
        // Kosong (belum difilter) = tampilkan SELURUH riwayat, bukan
        // dibatasi bulan berjalan -- konsisten sama Rekap Kehadiran Siswa (Waka).
        $dari = request()->filled('dari') ? Carbon::parse(request()->date('dari')) : null;
        $sampai = request()->filled('sampai') ? Carbon::parse(request()->date('sampai')) : null;

        $siswas = $kelas->siswas()->orderBy('no_absen')->get();

        $mode = request()->query('mode') === 'hari' ? 'hari' : 'mapel';
        $absensis = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereHas('jurnal', fn ($q) => $q
                ->when($dari, fn ($q2) => $q2->whereDate('tanggal', '>=', $dari))
                ->when($sampai, fn ($q2) => $q2->whereDate('tanggal', '<=', $sampai)))
            ->with('jurnal:id,tanggal')
            ->get();

        $rekap = $mode === 'hari'
            ? $absensis->groupBy('siswa_id')->map(fn ($rows) => $rows
                ->groupBy(fn (Absensi $absensi) => $absensi->jurnal->tanggal->toDateString())
                ->map(fn ($barisHari) => collect(['alpha', 'sakit', 'izin', 'dispensasi', 'hadir'])
                    ->first(fn ($status) => $barisHari->contains('status', $status)))
                ->countBy())
            : $absensis->groupBy('siswa_id')->map(fn ($rows) => $rows->countBy('status'));

        $adaKelasLain = auth()->user()->kelasWaliList()->count() > 1;

        return view('guru.wali-kelas.rekap', compact('kelas', 'siswas', 'rekap', 'adaKelasLain', 'dari', 'sampai', 'mode'));
    }

    public function siswaFragment(Kelas $kelas, Siswa $siswa): View
    {
        abort_unless($kelas->wali_id === auth()->user()->guru?->id, 403, 'Anda bukan wali kelas ini.');
        abort_unless($siswa->kelas_id === $kelas->id, 404);

        $dari = request()->filled('dari') ? Carbon::parse(request()->date('dari')) : null;
        $sampai = request()->filled('sampai') ? Carbon::parse(request()->date('sampai')) : null;
        $absensis = $siswa->absensis()
            ->whereHas('jurnal', fn ($query) => $query
                ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
                ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai)))
            ->with('jurnal.jadwal.mapel')
            ->get()
            ->sortByDesc(fn (Absensi $absensi) => $absensi->jurnal->tanggal->toDateString().sprintf('%02d', $absensi->jurnal->jam_ke_mulai));

        return view('guru.wali-kelas._siswa-fragment', compact('siswa', 'absensis'));
    }

    /**
     * Jurnal harian kelas -- wali kelas cuma LIHAT (bukan pengurus kelas,
     * nggak berwenang memeriksa/verifikasi). Kosong (belum difilter) =
     * tampilkan SEMUA riwayat, kotak Dari/Sampai juga kosong -- konsisten
     * sama pola Riwayat Jurnal guru.
     */
    public function jurnal(Kelas $kelas, Request $request): View
    {
        abort_unless($kelas->wali_id === auth()->user()->guru?->id, 403, 'Anda bukan wali kelas ini.');

        $dari = $request->query('dari');
        $sampai = $request->query('sampai');
        $statusGuru = in_array($request->query('status_guru'), ['hadir', 'tidak_hadir'], true)
            ? $request->query('status_guru') : '';

        $baseQuery = fn () => Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai));

        // Jumlah per tab (Semua/Hadir/Tidak Hadir) ikut rentang tanggal yang
        // lagi aktif, TAPI TANPA filter status_guru -- biar tiap tab nunjukin
        // angka aslinya, sama pola kayak $jumlahTab di Riwayat Jurnal guru.
        $jumlahTab = [
            'semua' => $baseQuery()->count(),
            'hadir' => $baseQuery()->where('status_guru', 'hadir')->count(),
            'tidak_hadir' => $baseQuery()->where('status_guru', 'tidak_hadir')->count(),
        ];

        $jurnals = $baseQuery()
            ->with('jadwal.mapel', 'guru')
            ->when($statusGuru, fn ($q) => $q->where('status_guru', $statusGuru))
            ->orderByRaw("CASE WHEN status_guru != 'tidak_hadir' AND status_verifikasi = 'pending' THEN 0 ELSE 1 END")
            ->latest('tanggal')->latest('id')
            ->paginate(15)->withQueryString();

        $adaKelasLain = auth()->user()->kelasWaliList()->count() > 1;

        return view('guru.wali-kelas.jurnal', compact('kelas', 'jurnals', 'dari', 'sampai', 'adaKelasLain', 'statusGuru', 'jumlahTab'));
    }

    public function jurnalFragment(Jurnal $jurnal): View
    {
        abort_unless($jurnal->jadwal->kelas->wali_id === auth()->user()->guru?->id, 403, 'Anda bukan wali kelas ini.');

        return view('sekretaris.jurnal._detail-fragment', ['jurnal' => $jurnal, 'readOnly' => true]);
    }
}
