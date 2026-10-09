<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Support\QrDispensasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SatpamController extends Controller
{
    /**
     * Hasil scan QR dari surat dispensasi. Dibuka dari kamera HP satpam (aplikasi kamera
     * bawaan yang baca QR ke URL ini) -- bukan scanner di dalam web, jadi tidak perlu JS.
     */
    public function dashboard(Request $request): View
    {
        $berlakuHariIni = Dispensasi::with('siswa.kelas')
            ->berlakuHariIni()
            ->whereIn('jenis', ['izin_keluar', 'lomba'])
            ->orderBy('id')
            ->get();

        $dispensasiKeluar = $berlakuHariIni
            ->where('jenis', 'izin_keluar')
            ->whereNull('waktu_kembali')
            ->values();

        return view('dashboards.satpam', compact('dispensasiKeluar', 'berlakuHariIni'));
    }

    public function konfirmasiKembali(Dispensasi $dispensasi, Request $request): RedirectResponse
    {
        abort_unless(
            $dispensasi->status_akhir === 'approved' && $dispensasi->jenis === 'izin_keluar',
            404
        );

        if (! $dispensasi->waktu_kembali) {
            $dispensasi->update(['waktu_kembali' => now()]);
            AuditLog::catat('Konfirmasi Siswa Kembali', "Siswa {$dispensasi->siswa->nama} dikonfirmasi kembali ke sekolah", $dispensasi);
        }

        return redirect()->route('satpam.dashboard')->with('success', 'Siswa berhasil dikonfirmasi kembali ke sekolah.');
    }

    public function scan(Request $request): View
    {
        $id = $request->integer('id');
        $token = (string) $request->get('token');

        $dispensasi = Dispensasi::with('siswa.kelas')->find($id);

        $valid = $dispensasi
            && $dispensasi->status_akhir === 'approved'
            && $dispensasi->berlakuPada()
            && QrDispensasi::valid($id, $token);
        $anggota = $valid ? $dispensasi->anggotaKelompok() : collect();

        AuditLog::catat(
            'Scan QR Dispensasi',
            $valid
                ? 'Scan valid — '.$anggota->pluck('siswa.nama')->join(', ')
                : 'Scan tidak valid / kedaluwarsa'.($dispensasi ? " — dispensasi #{$id}" : ''),
            $dispensasi
        );

        return view('satpam.hasil-scan', [
            'valid' => $valid,
            'dispensasi' => $valid ? $dispensasi : null,
            'anggota' => $anggota,
        ]);
    }
}
