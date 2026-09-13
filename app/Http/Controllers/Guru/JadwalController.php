<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /** Jadwal mengajar guru seminggu, dikelompokkan per hari (Senin–Jumat). */
    public function index(): View
    {
        $guru = auth()->user()->guru ?? abort(403, 'Akun tidak terhubung ke data guru.');

        $jadwalPerHari = $guru->jadwals()
            ->with('mapel', 'kelas')
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        // Jadwal piket ikut ditampilkan di halaman yang sama -- guru sering lupa
        // kalau jadwal piket & jadwal mengajar dipisah 2 halaman/menu berbeda.
        $piketPerHari = $guru->jadwalPikets()->orderBy('mulai')->get()->groupBy('hari');

        return view('guru.jadwal', compact('jadwalPerHari', 'piketPerHari'));
    }
}
