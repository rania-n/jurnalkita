<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\View\View;

/**
 * Detail satu siswa dari sisi guru -- cuma rekap kehadiran (read-only), bukan
 * CRUD -- sama semangatnya kayak WaliKelasController.
 */
class SiswaController extends Controller
{
    public function show(Siswa $siswa): View
    {
        $guru = auth()->user()->guru;

        // Guru cuma boleh lihat siswa yang pernah/sedang dia ajar (kelas dari
        // jadwal mengajarnya) -- bukan siswa sembarang di sekolah.
        $bolehLihat = $guru && $guru->jadwals()->where('kelas_id', $siswa->kelas_id)->exists();
        abort_unless($bolehLihat, 403, 'Anda tidak mengajar kelas siswa ini.');

        $siswa->load('kelas');

        $absensis = Absensi::where('siswa_id', $siswa->id)
            ->with('jurnal.jadwal.mapel')
            ->whereHas('jurnal')
            ->get()
            ->sortByDesc(fn ($a) => $a->jurnal->tanggal)
            ->values();

        $rekap = $absensis->countBy('status');

        return view('guru.siswa.show', compact('siswa', 'absensis', 'rekap'));
    }
}
