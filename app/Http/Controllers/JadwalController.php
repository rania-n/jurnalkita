<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwal = Jadwal::with(['kelas', 'mapel'])->get();
        $kelas = Kelas::all();
        $mapel = Mapel::all();
        return view('jadwal.index', compact('jadwal', 'kelas', 'mapel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelasid' => 'required|exists:kelas,id',
            'mapelid' => 'required|exists:mapel,id',
            'guruutamaid' => 'required|integer',
            'ruang' => 'required|string|max:50',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat',
            'jamkemulai' => 'required|integer',
            'jamkeselesai' => 'required|integer',
        ]);

        Jadwal::create($request->all());

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }
}