<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:mapels,id'],
            'kode' => ['required', 'string', 'max:20', Rule::unique('mapels', 'kode')->ignore($request->id)->withoutTrashed()],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $mapel = $request->filled('id') ? Mapel::findOrFail($data['id']) : new Mapel;
        $baru = ! $mapel->exists;

        $mapel->fill(['kode' => $data['kode'], 'nama' => $data['nama']])->save();

        AuditLog::catat($baru ? 'mapel.tambah' : 'mapel.ubah', "Mapel: {$mapel->nama}", $mapel);

        return back()->with('success', $baru ? 'Mata pelajaran ditambahkan.' : 'Mata pelajaran diperbarui.');
    }

    public function destroy(Mapel $mapel): RedirectResponse
    {
        $nama = $mapel->nama;
        $mapel->delete();

        AuditLog::catat('mapel.hapus', "Hapus mapel: {$nama}", $mapel);

        return back()->with('success', 'Mata pelajaran dihapus.');
    }
}
