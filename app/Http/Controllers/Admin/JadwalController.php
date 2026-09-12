<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'ruang' => ['nullable', 'string', 'max:50', Rule::in(config('akademik.ruangan'))],
        ]);

        // Guru yang sedang piket tidak mengajar (jadwal piket berbasis shift jam,
        // bukan sehari penuh) — cegah admin double-booking guru yang sama.
        if ($pesan = $this->konflikPiket($data['guru_id'], $data['hari'], $data['jam_ke_mulai'], $data['jam_ke_selesai'])) {
            return back()->with('error', $pesan)->withInput();
        }

        $jadwal = $request->filled('id') ? Jadwal::findOrFail($data['id']) : new Jadwal;
        $baru = ! $jadwal->exists;

        $jadwal->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Jadwal' : 'Ubah Jadwal', "Jadwal {$jadwal->hari} kelas #{$jadwal->kelas_id}", $jadwal);

        return back()->with('success', $baru ? 'Jadwal ditambahkan.' : 'Jadwal diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        AuditLog::catat('Hapus Jadwal', "Hapus jadwal #{$jadwal->id}", $jadwal);

        return back()->with('success', 'Jadwal dihapus.');
    }

    /** Cek apakah jam jadwal (jam ke-) bentrok dengan shift piket guru di hari yang sama. */
    private function konflikPiket(int $guruId, string $hari, int $jamMulai, int $jamSelesai): ?string
    {
        $kategori = $hari === 'jumat' ? 'jumat' : 'senin_kamis';

        $rentang = JamPelajaran::where('kategori', $kategori)
            ->whereBetween('jam_ke', [$jamMulai, $jamSelesai])
            ->selectRaw('min(mulai) as mulai, max(selesai) as selesai')
            ->first();

        $mulaiJadwal = $rentang?->getRawOriginal('mulai');
        $selesaiJadwal = $rentang?->getRawOriginal('selesai');
        if (! $mulaiJadwal || ! $selesaiJadwal) {
            return null; // jam pelajaran belum diatur admin -> tidak bisa dicek, jangan blokir
        }

        $bentrok = JadwalPiket::where('guru_id', $guruId)->where('hari', $hari)->get()
            ->first(function (JadwalPiket $p) use ($mulaiJadwal, $selesaiJadwal) {
                if (! $p->mulai || ! $p->selesai) {
                    return true; // piket tanpa jam spesifik -> dianggap sehari penuh
                }

                return $p->mulai->format('H:i:s') < $selesaiJadwal && $p->selesai->format('H:i:s') > $mulaiJadwal;
            });

        if (! $bentrok) {
            return null;
        }

        $jamPiket = $bentrok->mulai ? "jam {$bentrok->mulai->format('H:i')}–{$bentrok->selesai->format('H:i')}" : 'sehari penuh';

        return "Guru ini piket hari {$hari} ({$jamPiket}) — sesuai ketentuan, guru piket tidak mengajar saat bertugas. Pilih guru lain atau ganti jamnya.";
    }
}
