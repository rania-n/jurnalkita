<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Guru;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:gurus,id'],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
        ]);

        $guru = $request->filled('id') ? Guru::findOrFail($data['id']) : new Guru;
        $baru = ! $guru->exists;

        $guru->fill(['nama' => $data['nama'], 'nip' => $data['nip'] ?? null, 'no_hp' => $data['no_hp'] ?? null]);
        $guru->save();

        if ($request->filled('mapel_id')) {
            $guru->mapels()->syncWithoutDetaching([$data['mapel_id']]);
        }

        AuditLog::catat($baru ? 'guru.tambah' : 'guru.ubah', "Data guru: {$guru->nama}", $guru);

        return back()->with('success', $baru ? 'Guru ditambahkan.' : 'Data guru diperbarui.');
    }

    public function destroy(Guru $guru): RedirectResponse
    {
        $nama = $guru->nama;
        $guru->delete();

        AuditLog::catat('guru.hapus', "Hapus data guru: {$nama}", $guru);

        return back()->with('success', 'Data guru dihapus.');
    }
}
