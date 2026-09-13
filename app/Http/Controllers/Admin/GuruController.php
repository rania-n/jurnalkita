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
            'mapel_utama_id' => ['nullable', 'exists:mapels,id'],
            'mapel_tambahan' => ['nullable', 'array'],
            'mapel_tambahan.*' => ['exists:mapels,id'],
        ]);

        $guru = $request->filled('id') ? Guru::findOrFail($data['id']) : new Guru;
        $baru = ! $guru->exists;

        // no_hp SENGAJA tidak disentuh di sini -- diisi sekali lewat Manajemen Akun
        // ("Buat Akun"/"Ubah Akun"), biar nggak ada 2 tempat isi nomor yang beda.
        $guru->fill([
            'nama' => $data['nama'],
            'nip' => $data['nip'] ?? null,
            'mapel_utama_id' => $data['mapel_utama_id'] ?? null,
        ])->save();

        // Mapel tambahan = pilihan multi, minus mapel utama.
        $tambahan = collect($data['mapel_tambahan'] ?? [])
            ->reject(fn ($id) => $id == $guru->mapel_utama_id)
            ->all();
        $guru->mapels()->sync($tambahan);

        AuditLog::catat($baru ? 'Tambah Guru' : 'Ubah Guru', "Data guru: {$guru->nama}", $guru);

        return back()->with('success', $baru ? 'Guru ditambahkan.' : 'Data guru diperbarui.');
    }

    public function destroy(Guru $guru): RedirectResponse
    {
        $nama = $guru->nama;
        $guru->delete();

        AuditLog::catat('Hapus Guru', "Hapus data guru: {$nama}", $guru);

        return back()->with('success', 'Data guru dihapus.');
    }
}
