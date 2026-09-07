<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    // Menampilkan daftar data siswa beserta data kelasnya
    public function index()
    {
        $siswa = Siswa::with('kelas')->get();
        $kelas = Kelas::all(); // Dibutuhkan untuk pilihan dropdown di form
        return view('siswa.index', compact('siswa', 'kelas'));
    }

    // Menyimpan data siswa baru
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|max:20|unique:siswa,nis',
            'namasiswa' => 'required|string|max:100',
            'kelasid' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        Siswa::create($request->all());

        return redirect()->back()->with('success', 'Data siswa berhasil ditambahkan!');
    }

    // Memperbarui data siswa
    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $request->validate([
            'nis' => 'required|string|max:20|unique:siswa,nis,' . $id,
            'namasiswa' => 'required|string|max:100',
            'kelasid' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $siswa->update($request->all());

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui!');
    }

    // Menghapus data siswa
    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();

        return redirect()->back()->with('success', 'Data siswa berhasil dihapus!');
    }
}
