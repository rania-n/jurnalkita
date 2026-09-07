<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapel = Mapel::all();
        return view('mapel.index', compact('mapel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kodemapel' => 'required|string|max:20|unique:mapel,kodemapel',
            'namamapel' => 'required|string|max:100',
        ]);

        Mapel::create($request->all());

        return redirect()->back()->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);
        
        $request->validate([
            'kodemapel' => 'required|string|max:20|unique:mapel,kodemapel,' . $id,
            'namamapel' => 'required|string|max:100',
        ]);

        $mapel->update($request->all());

        return redirect()->back()->with('success', 'Mata pelajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->delete();

        return redirect()->back()->with('success', 'Mata pelajaran berhasil dihapus!');
    }
}
