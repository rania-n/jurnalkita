<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CatatanTerlambat;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Support\QrDispensasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SatpamController extends Controller
{
    public function dashboard(): View
    {
        $riwayatScan = AuditLog::where('aksi', 'Scan QR Dispensasi')
            ->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->latest('created_at')
            ->get();

        $riwayatTerlambat = CatatanTerlambat::with('siswa.kelas')
            ->where('dicatat_oleh_id', auth()->id())
            ->whereDate('tanggal', today())
            ->latest('jam_datang')
            ->get();

        return view('satpam.dashboard', compact('riwayatScan', 'riwayatTerlambat'));
    }

    /** Form catat siswa telat masuk gerbang pagi -- "buku piket ketertiban". */
    public function terlambatCreate(): View
    {
        $kelasList = Kelas::aktif()
            ->with(['siswas' => fn ($q) => $q->where('status', 'aktif')->select('id', 'kelas_id', 'nama', 'nis')])
            ->orderedByHierarchy()->get();

        // Diratakan jadi 1 daftar buat kotak "cari siswa" -- sama pola kayak
        // DispensasiController@create, biar nggak usah pilih kelas dulu.
        $siswaList = $kelasList->flatMap(fn ($k) => $k->siswas->map(fn ($s) => [
            'id' => $s->id, 'nama' => $s->nama, 'nis' => $s->nis, 'kelas' => $k->nama,
        ]))->sortBy('nama')->values();

        return view('satpam.terlambat-create', ['siswaList' => $siswaList]);
    }

    public function terlambatStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'jam_datang' => ['required', 'date_format:H:i'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $catatan = CatatanTerlambat::create([
            ...$data,
            'tanggal' => today(),
            'dicatat_oleh_id' => auth()->id(),
        ]);

        AuditLog::catat(
            'Catat Siswa Terlambat',
            "{$catatan->siswa->nama} terlambat jam {$data['jam_datang']}",
            $catatan
        );

        return redirect()->route('satpam.dashboard')->with('success', 'Catatan keterlambatan disimpan.');
    }

    /**
     * Hapus catatan keterlambatan salah input. Dibatasi punya sendiri & hari ini
     * saja -- catatan hari sebelumnya dibiarkan jadi jejak, nggak boleh dihapus
     * lagi (sama semangatnya kayak jurnal yang sudah diverifikasi).
     */
    public function terlambatDestroy(CatatanTerlambat $catatan): RedirectResponse
    {
        abort_unless($catatan->dicatat_oleh_id === auth()->id(), 403);
        abort_unless($catatan->tanggal->isToday(), 403, 'Catatan hari sebelumnya tidak bisa dihapus lagi.');

        $nama = $catatan->siswa->nama;
        $catatan->delete();

        AuditLog::catat('Hapus Catatan Terlambat', "Hapus catatan terlambat {$nama}", $catatan);

        return redirect()->route('satpam.dashboard')->with('success', 'Catatan keterlambatan dihapus.');
    }

    /**
     * Hasil scan QR dari surat dispensasi. Dibuka dari kamera HP satpam (aplikasi kamera
     * bawaan yang baca QR ke URL ini) -- bukan scanner di dalam web, jadi tidak perlu JS.
     */
    public function scan(Request $request): View
    {
        $id = $request->integer('id');
        $token = (string) $request->get('token');

        $dispensasi = Dispensasi::with('siswa.kelas')->find($id);

        $valid = $dispensasi
            && $dispensasi->status_akhir === 'approved'
            && $dispensasi->berlakuPada()
            && QrDispensasi::valid($id, $token);

        AuditLog::catat(
            'Scan QR Dispensasi',
            $valid
                ? "Scan valid — {$dispensasi->siswa->nama} ({$dispensasi->siswa->kelas?->nama})"
                : 'Scan tidak valid / kedaluwarsa'.($dispensasi ? " — dispensasi #{$id}" : ''),
            $dispensasi
        );

        return view('satpam.hasil-scan', [
            'valid' => $valid,
            'dispensasi' => $valid ? $dispensasi : null,
        ]);
    }
}
