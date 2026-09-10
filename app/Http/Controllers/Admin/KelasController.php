<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:kelas,id'],
            'nama' => ['required', 'string', 'max:100'],
            'tingkat' => ['required', 'in:X,XI,XII'],
            'jurusan' => ['nullable', 'string', 'max:50'],
            'wali_id' => ['nullable', 'exists:gurus,id'],
        ]);

        $kelas = $request->filled('id') ? Kelas::findOrFail($data['id']) : new Kelas;
        $baru = ! $kelas->exists;

        $kelas->fill([
            'nama' => $data['nama'],
            'tingkat' => $data['tingkat'],
            'jurusan' => $data['jurusan'] ?? null,
            'wali_id' => $data['wali_id'] ?? null,
        ])->save();

        AuditLog::catat($baru ? 'kelas.tambah' : 'kelas.ubah', "Kelas: {$kelas->nama}", $kelas);

        return back()->with('success', $baru ? 'Kelas ditambahkan.' : 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $nama = $kelas->nama;
        $kelas->delete();

        AuditLog::catat('kelas.hapus', "Hapus kelas: {$nama}", $kelas);

        return back()->with('success', 'Kelas dihapus.');
    }
}
