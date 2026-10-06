<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Support\QrDispensasi;
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
        $dispensasiKeluar = Dispensasi::with('siswa.kelas')
            ->where('status_akhir', 'approved')
            ->where('jenis', 'izin_keluar')
            ->whereDate('tanggal', today())
            ->whereNull('waktu_kembali')
            ->get();

        return view('dashboards.satpam', compact('dispensasiKeluar'));
    }

    public function konfirmasiKembali(Dispensasi $dispensasi, Request $request)
    {
        $dispensasi->update(['waktu_kembali' => now()]);

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
