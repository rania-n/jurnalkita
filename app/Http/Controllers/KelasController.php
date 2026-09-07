<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    // Menampilkan daftar data kelas
    public function index()
    {
        $kelas = Kelas::all();
        return view('kelas.index', compact('kelas'));
    }

    // Menyimpan data kelas baru
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|unique:kelas,id',
            'namakelas' => 'required|string|max:50',
            'tingkat' => 'required|in:X,XI,XII',
        ]);

        Kelas::create($request->all());

        return redirect()->back()->with('success', 'Data kelas berhasil ditambahkan!');
    }

    // Memperbarui data kelas
    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        
        $request->validate([
            'namakelas' => 'required|string|max:50',
            'tingkat' => 'required|in:X,XI,XII',
        ]);

        $kelas->update($request->all());

        return redirect()->back()->with('success', 'Data kelas berhasil diperbarui!');
    }

    // Menghapus data kelas
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()->back()->with('success', 'Data kelas berhasil dihapus!');
    }
}