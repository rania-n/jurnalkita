<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalWaka;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /** Jadwal mengajar guru seminggu, dikelompokkan per hari (Senin–Jumat). */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $guru = $user->guru ?? abort(403, 'Akun tidak terhubung ke data guru.');
        $hari = $request->query('hari', 'semua');

        $jadwalPerHari = $guru->jadwals()
            ->with('mapel', 'kelas')
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        // Jadwal piket ikut ditampilkan di halaman yang sama -- guru sering lupa
        // kalau jadwal piket & jadwal mengajar dipisah 2 halaman/menu berbeda.
        $piketPerHari = $guru->jadwalPikets()->orderBy('mulai')->get()->groupBy('hari');

        // Giliran piket Waka Kesiswaan -- tabel BEDA dari jadwal piket guru
        // biasa (JadwalWaka, bukan JadwalPiket; nggak ada jam, sepanjang hari),
        // jadi harus diambil terpisah. Cuma relevan buat akun role waka.
        $jadwalWakaPerHari = $user->role === 'waka'
            ? JadwalWaka::where('user_id', $user->id)->get()->groupBy('hari')
            : collect();

        if ($hari !== 'semua') {
            // Bukan Collection::only() -- hasil groupBy() itemnya sub-Collection
            // (bukan Model), sedangkan Eloquent\Collection::only() mengasumsikan
            // itemnya Model (manggil getKey()) dan meledak. filter() aman buat
            // kedua jenis Collection.
            $jadwalPerHari = $jadwalPerHari->filter(fn ($v, $k) => $k === $hari);
            $piketPerHari = $piketPerHari->filter(fn ($v, $k) => $k === $hari);
            $jadwalWakaPerHari = $jadwalWakaPerHari->filter(fn ($v, $k) => $k === $hari);
        }

        return view('guru.jadwal', compact('jadwalPerHari', 'piketPerHari', 'jadwalWakaPerHari', 'hari'));
    }
}
