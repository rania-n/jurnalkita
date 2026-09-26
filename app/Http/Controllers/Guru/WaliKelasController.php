<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Kelas;
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

        $siswas = $kelas->siswas()->orderBy('no_absen')->get();

        $rekap = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereHas('jurnal', fn ($q) => $q->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year))
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->countBy('status'));

        $adaKelasLain = auth()->user()->kelasWaliList()->count() > 1;

        return view('guru.wali-kelas.rekap', compact('kelas', 'siswas', 'rekap', 'adaKelasLain'));
    }

    /**
     * Jurnal harian kelas -- wali kelas cuma LIHAT (bukan pengurus kelas,
     * nggak berwenang memeriksa/verifikasi). Default hari ini, karena yang
     * paling relevan buat wali kelas adalah "apa yang terjadi di kelasnya
     * hari ini", bukan riwayat panjang seperti punya pengurus kelas.
     */
    public function jurnal(Kelas $kelas, Request $request): View
    {
        abort_unless($kelas->wali_id === auth()->user()->guru?->id, 403, 'Anda bukan wali kelas ini.');

        $dari = $request->query('dari', today()->toDateString());
        $sampai = $request->query('sampai') ?: $dari;

        $jurnals = Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
            ->with('jadwal.mapel', 'guru')
            ->whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->orderByRaw("CASE WHEN status_guru != 'tidak_hadir' AND status_verifikasi = 'pending' THEN 0 ELSE 1 END")
            ->latest('tanggal')->latest('id')
            ->paginate(15)->withQueryString();

        $adaKelasLain = auth()->user()->kelasWaliList()->count() > 1;

        return view('guru.wali-kelas.jurnal', compact('kelas', 'jurnals', 'dari', 'sampai', 'adaKelasLain'));
    }

    public function jurnalFragment(Jurnal $jurnal): View
    {
        abort_unless($jurnal->jadwal->kelas->wali_id === auth()->user()->guru?->id, 403, 'Anda bukan wali kelas ini.');

        return view('sekretaris.jurnal._detail-fragment', ['jurnal' => $jurnal, 'readOnly' => true]);
    }
}
