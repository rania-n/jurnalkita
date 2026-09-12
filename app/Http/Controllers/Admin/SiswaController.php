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
            // Cuma 1 pengurus kelas per kelas (akunnya boleh dipakai di banyak HP
            // sekaligus, itu bukan masalah -- yang dibatasi jumlah ORANGnya).
            'jabatan' => ['required', 'in:anggota,pengurus', function ($attribute, $value, $fail) use ($request) {
                if ($value !== 'pengurus') {
                    return;
                }
                $sudahAdaPengurus = Siswa::where('kelas_id', $request->input('kelas_id'))
                    ->where('jabatan', 'pengurus')
                    ->when($request->filled('id'), fn ($q) => $q->where('id', '!=', $request->integer('id')))
                    ->exists();
                if ($sudahAdaPengurus) {
                    $fail('Kelas ini sudah punya pengurus kelas. Ubah pengurus lama jadi "Anggota" dulu kalau mau ganti.');
                }
            }],
            'status' => ['nullable', 'in:aktif,lulus,pindah'],
        ]);

        $siswa = $request->filled('id') ? Siswa::findOrFail($data['id']) : new Siswa;
        $baru = ! $siswa->exists;

        $siswa->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Siswa' : 'Ubah Siswa', "Siswa: {$siswa->nama}", $siswa);

        return back()->with('success', $baru ? 'Siswa ditambahkan.' : 'Siswa diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        $nama = $siswa->nama;
        $siswa->delete();

        AuditLog::catat('Hapus Siswa', "Hapus siswa: {$nama}", $siswa);

        return back()->with('success', 'Siswa dihapus.');
    }
}
