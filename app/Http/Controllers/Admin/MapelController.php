<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:mapels,id'],
            // Kode boleh dikosongkan -> dibuatkan otomatis dari nama, tapi tetap boleh
            // diketik manual/diedit kalau admin mau kode tertentu.
            'kode' => ['nullable', 'string', 'max:20', Rule::unique('mapels', 'kode')->ignore($request->id)->withoutTrashed()],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $mapel = $request->filled('id') ? Mapel::findOrFail($data['id']) : new Mapel;
        $baru = ! $mapel->exists;

        $kode = ($data['kode'] ?? null) ?: $this->kodeOtomatis($data['nama'], $mapel->id);

        $mapel->fill(['kode' => $kode, 'nama' => $data['nama']])->save();

        AuditLog::catat($baru ? 'Tambah Mapel' : 'Ubah Mapel', "Mapel: {$mapel->nama}", $mapel);

        return back()->with('success', $baru ? 'Mata pelajaran ditambahkan.' : 'Mata pelajaran diperbarui.');
    }

    /** Dari nama mapel: 1 kata -> 3 huruf depan, banyak kata -> inisial tiap kata. Ditambah angka kalau bentrok. */
    private function kodeOtomatis(string $nama, ?int $kecualiId): string
    {
        $kata = collect(preg_split('/\s+/', trim($nama)))
            ->reject(fn ($k) => in_array(mb_strtolower($k), ['dan', 'di', 'ke', 'dari', 'untuk']))
            ->values();

        $dasar = $kata->count() > 1
            ? $kata->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('')
            : mb_strtoupper(mb_substr($kata->first() ?? 'MP', 0, 3));

        $kode = $dasar;
        $i = 1;
        while (Mapel::where('kode', $kode)->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))->exists()) {
            $kode = $dasar.(++$i);
        }

        return $kode;
    }

    public function destroy(Mapel $mapel): RedirectResponse
    {
        $masalah = [];

        $jumlahJadwal = Jadwal::where('mapel_id', $mapel->id)->count();
        if ($jumlahJadwal > 0) {
            $masalah[] = "dipakai {$jumlahJadwal} jadwal pelajaran";
        }

        $jumlahGuruUtama = Guru::where('mapel_utama_id', $mapel->id)->count();
        if ($jumlahGuruUtama > 0) {
            $masalah[] = "jadi mapel utama {$jumlahGuruUtama} guru";
        }

        $jumlahGuruTambahan = $mapel->gurus()->count();
        if ($jumlahGuruTambahan > 0) {
            $masalah[] = "jadi mapel tambahan {$jumlahGuruTambahan} guru";
        }

        if (! empty($masalah)) {
            return back()->with('error', "Mapel {$mapel->nama} belum bisa dihapus: masih ".implode('; ', $masalah).'.');
        }

        $nama = $mapel->nama;
        $mapel->delete();

        AuditLog::catat('Hapus Mapel', "Hapus mapel: {$nama}", $mapel);

        return back()->with('success', 'Mata pelajaran dihapus.');
    }
}
