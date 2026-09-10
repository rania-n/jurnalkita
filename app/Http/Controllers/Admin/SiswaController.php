<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:siswas,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswas', 'nis')->ignore($request->id)->withoutTrashed()],
            'nama' => ['required', 'string', 'max:255'],
            'no_absen' => ['nullable', 'integer', 'min:1', 'max:99'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'jabatan' => ['required', 'in:anggota,pengurus'],
        ]);

        $siswa = $request->filled('id') ? Siswa::findOrFail($data['id']) : new Siswa;
        $baru = ! $siswa->exists;

        $siswa->fill($data)->save();

        AuditLog::catat($baru ? 'siswa.tambah' : 'siswa.ubah', "Siswa: {$siswa->nama}", $siswa);

        return back()->with('success', $baru ? 'Siswa ditambahkan.' : 'Siswa diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        $nama = $siswa->nama;
        $siswa->delete();

        AuditLog::catat('siswa.hapus', "Hapus siswa: {$nama}", $siswa);

        return back()->with('success', 'Siswa dihapus.');
    }
}
