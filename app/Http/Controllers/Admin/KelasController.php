<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
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

        $tahunAktif = TahunAjaran::aktif();
        $kelasDalamTahunAktif = $baru || ($tahunAktif
            ? $kelas->tahun_ajaran_id === $tahunAktif->id
            : $kelas->tahun_ajaran_id === null);
        if ($kelasDalamTahunAktif && $data['tingkat'] === 'XII') {
            $data['status'] = 'pkl';
        }

        $kelas->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Kelas' : 'Ubah Kelas', "Kelas: {$kelas->nama}", $kelas);

        return back()->with('success', $baru ? 'Kelas ditambahkan.' : 'Kelas diperbarui.');
    }

    public function updateStatusBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'pkl'])],
            'tingkat' => ['nullable', 'required_without:kelas_ids', Rule::in(config('akademik.tingkat'))],
            'kelas_ids' => ['nullable', 'required_without:tingkat', 'array', 'min:1'],
            'kelas_ids.*' => ['required', 'integer', 'distinct', 'exists:kelas,id'],
        ]);

        $query = Kelas::aktif();
        if (isset($data['tingkat'])) {
            $query->where('tingkat', $data['tingkat']);
        } else {
            $query->whereIn('id', $data['kelas_ids']);
        }

        $kelas = $query->get(['id', 'nama', 'tingkat']);
        $jumlahDiminta = isset($data['tingkat']) ? $kelas->count() : count($data['kelas_ids']);
        if ($kelas->isEmpty() || $kelas->count() !== $jumlahDiminta) {
            return back()->with('error', 'Pilih kelas yang masih berada di tahun ajaran aktif. Data kelas arsip tidak diubah.');
        }

        if ($data['status'] === 'aktif' && $kelas->contains('tingkat', 'XII')) {
            return back()->with('error', 'Kelas XII tahun ajaran aktif harus berstatus PKL.');
        }

        Kelas::whereIn('id', $kelas->pluck('id'))->update(['status' => $data['status']]);

        $labelStatus = $data['status'] === 'pkl' ? 'PKL' : 'Aktif';
        $labelTarget = isset($data['tingkat']) ? "semua kelas tingkat {$data['tingkat']}" : $kelas->pluck('nama')->implode(', ');
        AuditLog::catat('Ubah Status Kelas Massal', "{$labelTarget} diubah ke status {$labelStatus}.");

        return back()->with('success', "Status {$kelas->count()} kelas diubah ke {$labelStatus}.");
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
