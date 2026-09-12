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
        return view('satpam.terlambat-create', [
            'kelasList' => Kelas::aktif()
                ->with(['siswas' => fn ($q) => $q->where('status', 'aktif')->select('id', 'kelas_id', 'nama', 'nis')])
                ->orderBy('nama')->get(),
        ]);
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
