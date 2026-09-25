<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\User;
use App\Support\QrDispensasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * Surat dispensasi — dikirim ke siswa lewat WA (link bertanda-tangan, tanpa login).
 * Kalau statusnya approved, halaman ini nampilkan QR yang berganti tiap 10 detik
 * buat ditunjukkan ke satpam saat siswa keluar sekolah.
 */
class SuratDispensasiController extends Controller
{
    public function show(Request $request, Dispensasi $dispensasi): View
    {
        $user = $request->user();
        $bolehLihatLogin = $user && (
            in_array($user->role, ['waka', 'admin'], true)
            || $dispensasi->diajukan_oleh_id === $user->id
        );

        abort_unless($request->hasValidSignature() || $bolehLihatLogin, 403, 'Tautan tidak valid atau sudah kedaluwarsa.');

        $dispensasi->load('siswa.kelas', 'pengaju', 'waka');

        $qrUrl = null;
        if ($dispensasi->status_akhir === 'approved' && $dispensasi->berlakuPada()) {
            $token = QrDispensasi::token($dispensasi->id);
            $tujuan = route('satpam.scan', ['id' => $dispensasi->id, 'token' => $token]);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data='.urlencode($tujuan);
        }

        return view('dispensasi.surat', [
            'dispensasi' => $dispensasi,
            'qrUrl' => $qrUrl,
            'detikSisa' => QrDispensasi::detikSisa(),
        ]);
    }

    /**
     * Fragment HTML (bukan halaman penuh) buat popup "Lihat Surat + QR" --
     * cuma buat guru piket/waka/admin yang login & punya akses (BUKAN lewat
     * tanda-tangan URL kayak show(), karena ini dipanggil dari dalam app
     * yang udah ada sesi login). Link yang dikirim ke SISWA lewat WA tetap
     * pakai show() (signed URL, halaman biasa) -- siswa belum punya sesi.
     */
    public function showFragment(Request $request, Dispensasi $dispensasi): View
    {
        $user = $request->user();
        abort_unless(
            in_array($user->role, ['waka', 'admin'], true)
                || $user->piketHariIni()
                || $dispensasi->diajukan_oleh_id === $user->id,
            403
        );

        $dispensasi->load('siswa.kelas', 'pengaju', 'waka');

        $qrUrl = null;
        if ($dispensasi->status_akhir === 'approved' && $dispensasi->berlakuPada()) {
            $token = QrDispensasi::token($dispensasi->id);
            $tujuan = route('satpam.scan', ['id' => $dispensasi->id, 'token' => $token]);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data='.urlencode($tujuan);
        }

        return view('dispensasi._surat-fragment', [
            'dispensasi' => $dispensasi,
            'qrUrl' => $qrUrl,
        ]);
    }

    /** Buat piket: bikin tautan surat bertanda-tangan buat dibagikan ke siswa lewat WA. */
    public static function tautanSurat(Dispensasi $dispensasi): string
    {
        return URL::temporarySignedRoute(
            'dispensasi.surat', now()->addDays(3), ['dispensasi' => $dispensasi->id]
        );
    }

    /** Buat piket: tautan persetujuan buat Waka — tanpa login, berlaku 48 jam. */
    public static function tautanPersetujuan(Dispensasi $dispensasi, User $waka): string
    {
        return URL::temporarySignedRoute(
            'dispensasi.persetujuan', now()->addHours(48), ['dispensasi' => $dispensasi->id, 'waka' => $waka->id]
        );
    }

    /** Halaman persetujuan buat Waka lewat link WA — tanpa login, dicek pakai tanda tangan URL. */
    public function persetujuan(Request $request, Dispensasi $dispensasi): View
    {
        abort_unless($request->hasValidSignature(), 403, 'Tautan tidak valid atau sudah kedaluwarsa.');

        $otomatisKadaluarsa = $dispensasi->batalkanKalauKadaluarsa();
        $dispensasi->load('siswa.kelas', 'pengaju');

        return view('dispensasi.persetujuan-wa', [
            'dispensasi' => $dispensasi,
            'sudahDiputuskan' => $dispensasi->status_waka !== 'pending',
            'otomatisKadaluarsa' => $otomatisKadaluarsa,
        ]);
    }

    public function prosesPersetujuan(Request $request, Dispensasi $dispensasi): View
    {
        abort_unless($request->hasValidSignature(), 403, 'Tautan tidak valid atau sudah kedaluwarsa.');
        abort_unless(! $dispensasi->batalkanKalauKadaluarsa(), 409, 'Dispensasi ini sudah kadaluarsa (melewati tanggal berlaku tanpa keputusan) dan otomatis dibatalkan.');
        abort_unless($dispensasi->status_waka === 'pending', 409, 'Sudah diputuskan sebelumnya.');

        $data = $request->validate(['keputusan' => ['required', 'in:approved,rejected']]);

        $dispensasi->update([
            'status_waka' => $data['keputusan'],
            'waka_id' => $request->integer('waka'),
            'catatan_waka' => 'Diputuskan lewat link WhatsApp (tanpa login).',
        ]);
        $dispensasi->segarkanStatusAkhir();

        AuditLog::catat(
            'Keputusan Waka Dispensasi (WA)',
            "Waka {$data['keputusan']} dispensasi #{$dispensasi->id} lewat link WhatsApp",
            $dispensasi
        );

        return view('dispensasi.persetujuan-wa-selesai', [
            'dispensasi' => $dispensasi,
            'keputusan' => $data['keputusan'],
        ]);
    }
}
