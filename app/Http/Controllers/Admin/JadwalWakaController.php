<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\JadwalWaka;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalWakaController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:jadwal_wakas,id'],
            'user_id' => ['required', 'exists:users,id', Rule::exists('users', 'id')->where('role', 'waka')],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat'],
        ]);

        $jadwal = $request->filled('id') ? JadwalWaka::findOrFail($data['id']) : new JadwalWaka;
        $baru = ! $jadwal->exists;

        $jadwal->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Jadwal Waka' : 'Ubah Jadwal Waka', "Jadwal waka {$jadwal->hari}", $jadwal);

        return back()->with('success', $baru ? 'Jadwal waka ditambahkan.' : 'Jadwal waka diperbarui.');
    }

    public function destroy(JadwalWaka $jadwalWaka): RedirectResponse
    {
        $jadwalWaka->delete();

        AuditLog::catat('Hapus Jadwal Waka', "Hapus jadwal waka #{$jadwalWaka->id}", $jadwalWaka);

        return back()->with('success', 'Jadwal waka dihapus.');
    }
}
