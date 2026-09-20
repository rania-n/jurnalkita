<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Kelas;
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
        if ($pesan = $this->alasanTidakBolehDihapus($guru)) {
            return back()->with('error', $pesan);
        }

        $nama = $guru->nama;
        $guru->delete();

        AuditLog::catat('Hapus Guru', "Hapus data guru: {$nama}", $guru);

        return back()->with('success', 'Data guru dihapus.');
    }

    /**
     * Guru soft-delete doang -- FK cascadeOnDelete/nullOnDelete di migration nggak
     * pernah kepicu (itu cuma jalan pas hard delete beneran). Jadi kalau guru yang
     * masih ngajar/piket/jadi wali langsung dihapus, jadwal/kelas terkait diam-diam
     * nyantol ke guru "hantu" -- harus dibereskan admin dulu sebelum boleh dihapus.
     */
    private function alasanTidakBolehDihapus(Guru $guru): ?string
    {
        $masalah = [];

        $ngajar = Jadwal::where('guru_id', $guru->id)->orWhere('guru_pendamping_id', $guru->id)->count();
        if ($ngajar > 0) {
            $masalah[] = "masih punya {$ngajar} jadwal mengajar";
        }

        $piket = JadwalPiket::where('guru_id', $guru->id)->count();
        if ($piket > 0) {
            $masalah[] = "masih punya {$piket} jadwal piket";
        }

        $kelasWali = Kelas::where('wali_id', $guru->id)->pluck('nama');
        if ($kelasWali->isNotEmpty()) {
            $masalah[] = 'masih jadi wali kelas '.$kelasWali->implode(', ');
        }

        if (empty($masalah)) {
            return null;
        }

        return "Guru {$guru->nama} belum bisa dihapus: ".implode('; ', $masalah).'. Ubah/hapus dulu jadwal & status wali kelasnya sebelum menghapus data guru ini.';
    }
}
