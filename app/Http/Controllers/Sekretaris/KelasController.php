<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    private function kelas(): Kelas
    {
        return auth()->user()->kelasSekretaris() ?? abort(403, 'Akun tidak terhubung ke kelas.');
    }

    /** V1: daftar siswa sekelas, read-only. */
    public function siswa(): View
    {
        $kelas = $this->kelas();
        $siswas = $kelas->siswas()->orderBy('no_absen')->get();

        return view('sekretaris.siswa', compact('kelas', 'siswas'));
    }

    /** V2: jadwal pelajaran kelas, seminggu, dikelompokkan per hari. */
    public function jadwal(Request $request): View
    {
        $kelas = $this->kelas();
        $hari = $request->query('hari', 'semua');

        $jadwalPerHari = $kelas->jadwals()
            ->with('mapel', 'guru')
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        if ($hari !== 'semua') {
            // filter(), bukan only() -- Eloquent\Collection::only() ngasumsiin
            // itemnya Model (manggil getKey()), meledak di atas hasil groupBy()
            // yang itemnya sub-Collection. Lihat Guru\JadwalController.
            $jadwalPerHari = $jadwalPerHari->filter(fn ($v, $k) => $k === $hari);
        }

        return view('sekretaris.jadwal', compact('kelas', 'jadwalPerHari', 'hari'));
    }

    /** Rekap kehadiran kelas bulan berjalan, per siswa. */
    public function rekap(): View
    {
        $kelas = $this->kelas();

        $siswas = $kelas->siswas()->orderBy('no_absen')->get();

        $rekap = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereHas('jurnal', fn ($q) => $q->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year))
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->countBy('status'));

        return view('sekretaris.rekap', compact('kelas', 'siswas', 'rekap'));
    }
}
