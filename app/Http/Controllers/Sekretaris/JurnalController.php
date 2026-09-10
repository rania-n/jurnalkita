<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\Jurnal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JurnalController extends Controller
{
    private function kelas()
    {
        $user = auth()->user();

        abort_unless($user->isSekretaris(), 403, 'Hanya pengurus kelas yang dapat mengakses ini.');

        return $user->kelasSekretaris() ?? abort(403, 'Akun tidak terhubung ke kelas.');
    }

    private function pastikanKelasSaya(Jurnal $jurnal): void
    {
        abort_unless($jurnal->jadwal->kelas_id === $this->kelas()->id, 403);
    }

    public function index(Request $request): View
    {
        $kelas = $this->kelas();
        $status = $request->get('status');

        $jurnals = Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
            ->with('jadwal.mapel', 'guru')
            ->when($status, fn ($q) => $q->where('status_verifikasi', $status))
            ->latest('tanggal')->latest('id')
            ->paginate(15);

        return view('sekretaris.jurnal.index', [
            'jurnals' => $jurnals,
            'kelas' => $kelas,
            'status' => $status,
            'jumlahPending' => Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
                ->where('status_verifikasi', 'pending')->count(),
        ]);
    }

    public function show(Jurnal $jurnal): View
    {
        $this->pastikanKelasSaya($jurnal);
        $jurnal->load('jadwal.mapel', 'guru', 'absensis.siswa');

        return view('sekretaris.jurnal.show', compact('jurnal'));
    }

    public function verifikasi(Jurnal $jurnal, Request $request): RedirectResponse
    {
        $this->pastikanKelasSaya($jurnal);

        $data = $request->validate([
            'keputusan' => ['required', 'in:terima,revisi'],
            'catatan' => ['nullable', 'string', 'max:500', 'required_if:keputusan,revisi'],
        ]);

        $jurnal->update([
            'status_verifikasi' => $data['keputusan'] === 'terima' ? 'terverifikasi' : 'revisi',
            'verifikator_id' => auth()->user()->siswa->id,
            'catatan_verifikasi' => $data['catatan'] ?? null,
        ]);

        AuditLog::catat(
            $data['keputusan'] === 'terima' ? 'jurnal.verifikasi' : 'jurnal.minta_revisi',
            "Jurnal #{$jurnal->id} — ".($data['keputusan'] === 'terima' ? 'terverifikasi' : 'minta revisi'),
            $jurnal
        );

        return redirect()->route('sekretaris.jurnal.index')
            ->with('success', $data['keputusan'] === 'terima' ? 'Jurnal diverifikasi.' : 'Permintaan revisi dikirim ke guru.');
    }

    /* ---------------------------------------- Jurnal pengganti (guru tidak sempat) */
    public function createPengganti(): View
    {
        $kelas = $this->kelas();
        $hariIni = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? null;

        $jadwals = $kelas->jadwals()->with('mapel', 'guru')
            ->when($hariIni, fn ($q) => $q->where('hari', $hariIni))
            ->orderBy('jam_ke_mulai')->get();

        return view('sekretaris.jurnal.pengganti', compact('kelas', 'jadwals'));
    }

    public function storePengganti(Request $request): RedirectResponse
    {
        $kelas = $this->kelas();

        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'jam_ke_mulai' => ['required', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'status_guru' => ['required', 'in:tugas,tidak_hadir'],   // pengganti tidak boleh "hadir"
            'materi' => ['required', 'string'],
            'tugas_tambahan' => ['nullable', 'string'],
        ]);

        $jadwal = Jadwal::findOrFail($data['jadwal_id']);
        abort_unless($jadwal->kelas_id === $kelas->id, 403);

        $sudahAda = Jurnal::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
        if ($sudahAda) {
            return redirect()->route('sekretaris.jurnal.show', $sudahAda)
                ->with('info', 'Jurnal untuk jadwal ini hari ini sudah ada.');
        }

        $jurnal = DB::transaction(function () use ($data, $jadwal) {
            $jurnal = Jurnal::create([
                ...$data,
                'guru_id' => $jadwal->guru_id,
                'tanggal' => now()->toDateString(),
                'diisi_oleh_pengurus' => true,
                'status_verifikasi' => 'terverifikasi',
                'verifikator_id' => auth()->user()->siswa->id,
            ]);

            foreach ($jadwal->kelas->siswas as $siswa) {
                Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $siswa->id, 'status' => 'hadir']);
            }

            return $jurnal;
        });

        AuditLog::catat('jurnal.pengganti', "Pengurus kelas mengisi jurnal pengganti #{$jurnal->id}", $jurnal);

        return redirect()->route('sekretaris.jurnal.show', $jurnal)
            ->with('success', 'Jurnal pengganti tersimpan. Guru akan melihatnya di riwayat.');
    }
}
