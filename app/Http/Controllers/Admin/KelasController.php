<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:kelas,id'],
            'tingkat' => ['required', Rule::in(config('akademik.tingkat'))],
            'jurusan' => ['required', Rule::in(array_keys(config('akademik.jurusan')))],
            'nomor' => ['nullable', 'integer', 'min:1', 'max:20'],
            'wali_id' => ['nullable', 'exists:gurus,id'],
        ]);

        // Nama kelas dibuat otomatis: "X RPL 1"
        $data['nama'] = trim("{$data['tingkat']} {$data['jurusan']} ".($data['nomor'] ?? ''));

        $kelas = $request->filled('id') ? Kelas::findOrFail($data['id']) : new Kelas;
        $baru = ! $kelas->exists;

        $kelas->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Kelas' : 'Ubah Kelas', "Kelas: {$kelas->nama}", $kelas);

        return back()->with('success', $baru ? 'Kelas ditambahkan.' : 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $nama = $kelas->nama;
        $kelas->delete();

        AuditLog::catat('Hapus Kelas', "Hapus kelas: {$nama}", $kelas);

        return back()->with('success', 'Kelas dihapus.');
    }
}
