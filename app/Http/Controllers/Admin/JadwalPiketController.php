<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\JadwalPiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JadwalPiketController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:jadwal_pikets,id'],
            'guru_id' => ['required', 'exists:gurus,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat'],
            'mulai' => ['nullable', 'date_format:H:i'],
            'selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:mulai'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $piket = $request->filled('id') ? JadwalPiket::findOrFail($data['id']) : new JadwalPiket;
        $baru = ! $piket->exists;

        $piket->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Jadwal Piket' : 'Ubah Jadwal Piket', "Jadwal piket {$piket->hari}", $piket);

        return back()->with('success', $baru ? 'Jadwal piket ditambahkan.' : 'Jadwal piket diperbarui.');
    }

    public function destroy(JadwalPiket $jadwalPiket): RedirectResponse
    {
        $jadwalPiket->delete();

        AuditLog::catat('Hapus Jadwal Piket', "Hapus jadwal piket #{$jadwalPiket->id}", $jadwalPiket);

        return back()->with('success', 'Jadwal piket dihapus.');
    }
}
