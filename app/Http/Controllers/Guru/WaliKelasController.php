<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\CatatanTerlambat;
use App\Models\Kelas;
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

        $terlambat = CatatanTerlambat::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
            ->get()
            ->countBy('siswa_id');

        $adaKelasLain = auth()->user()->kelasWaliList()->count() > 1;

        return view('guru.wali-kelas.rekap', compact('kelas', 'siswas', 'rekap', 'terlambat', 'adaKelasLain'));
    }
}
