<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    /**
     * K3: rangkuman satu kelas dalam satu halaman -- dulu roster, jadwal, dan
     * wali kepisah di 3 menu berbeda (Data Siswa, Jadwal Pelajaran, kolom Wali
     * di Data Kelas), admin harus buka satu-satu buat lihat gambaran lengkap.
     */
    public function show(Kelas $kelas): View
    {
        $kelas->load('wali');

        $siswas = $kelas->siswas()->orderBy('no_absen')->get();

        $jadwalPerHari = $kelas->jadwals()
            ->with('mapel', 'guru')
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        $hariLabel = config('akademik.hari');

        return view('admin.kelas.show', compact('kelas', 'siswas', 'jadwalPerHari', 'hariLabel'));
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:kelas,id'],
            'tingkat' => ['required', Rule::in(config('akademik.tingkat'))],
            'jurusan' => ['required', Rule::in(array_keys(config('akademik.jurusan')))],
            'nomor' => ['nullable', 'integer', 'min:1', 'max:20'],
            'wali_id' => ['required', 'exists:gurus,id'],
            'status' => ['required', Rule::in(['aktif', 'pkl'])],
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
        $jumlahSiswa = Siswa::where('kelas_id', $kelas->id)->count();
        if ($jumlahSiswa > 0) {
            return back()->with('error', "Kelas {$kelas->nama} masih punya {$jumlahSiswa} siswa. Pindahkan atau hapus dulu data siswanya sebelum menghapus kelas ini.");
        }

        $jumlahJadwal = Jadwal::where('kelas_id', $kelas->id)->count();
        if ($jumlahJadwal > 0) {
            return back()->with('error', "Kelas {$kelas->nama} masih punya {$jumlahJadwal} jadwal pelajaran. Hapus dulu jadwalnya sebelum menghapus kelas ini.");
        }

        $nama = $kelas->nama;
        $kelas->delete();

        AuditLog::catat('Hapus Kelas', "Hapus kelas: {$nama}", $kelas);

        return back()->with('success', 'Kelas dihapus.');
    }
}
