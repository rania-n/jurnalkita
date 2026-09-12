<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Support\QrDispensasi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SatpamController extends Controller
{
    public function dashboard(): View
    {
        $riwayatHariIni = AuditLog::where('aksi', 'Scan QR Dispensasi')
            ->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->latest('created_at')
            ->get();

        return view('satpam.dashboard', compact('riwayatHariIni'));
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
