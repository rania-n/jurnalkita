<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Jadwal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:jadwals,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mapels,id'],
            'guru_id' => ['required', 'exists:gurus,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat'],
            'jam_ke_mulai' => ['required', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'ruang' => ['nullable', 'string', 'max:50'],
        ]);

        $jadwal = $request->filled('id') ? Jadwal::findOrFail($data['id']) : new Jadwal;
        $baru = ! $jadwal->exists;

        $jadwal->fill($data)->save();

        AuditLog::catat($baru ? 'jadwal.tambah' : 'jadwal.ubah', "Jadwal {$jadwal->hari} kelas #{$jadwal->kelas_id}", $jadwal);

        return back()->with('success', $baru ? 'Jadwal ditambahkan.' : 'Jadwal diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        AuditLog::catat('jadwal.hapus', "Hapus jadwal #{$jadwal->id}", $jadwal);

        return back()->with('success', 'Jadwal dihapus.');
    }
}
