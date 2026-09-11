<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Support\Waktu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JurnalController extends Controller
{
    private function guru()
    {
        return auth()->user()->guru ?? abort(403, 'Akun tidak terhubung ke data guru.');
    }

    private function milikSendiri(Jurnal $jurnal): void
    {
        abort_unless($jurnal->guru_id === $this->guru()->id, 403);
    }

    /* --------------------------------------------------------------- Riwayat */
    public function index(): View
    {
        $jurnals = $this->guru()->jurnals()
            ->with('jadwal.kelas', 'jadwal.mapel')
            ->latest('tanggal')->latest('id')
            ->paginate(15);

        return view('guru.jurnal.index', compact('jurnals'));
    }

    /* ------------------------------------------------------------ Form baru */
    public function create(Request $request): View
    {
        $guru = $this->guru();
        $hariIni = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? null;
        $jadwals = $hariIni
            ? $guru->jadwals()->with('kelas', 'mapel')->where('hari', $hariIni)->orderBy('jam_ke_mulai')->get()
            : collect();

        // Semua jadwal guru (fallback kalau tidak ada jadwal hari ini)
        $semuaJadwal = $guru->jadwals()->with('kelas', 'mapel')->orderBy('hari')->orderBy('jam_ke_mulai')->get();

        $jadwalTerpilih = $request->filled('jadwal')
            ? $semuaJadwal->firstWhere('id', (int) $request->jadwal)
            : null;

        return view('guru.jurnal.create', [
            'jadwals' => $jadwals->isNotEmpty() ? $jadwals : $semuaJadwal,
            'jadwalTerpilih' => $jadwalTerpilih,
            'jpSekarang' => Waktu::jpSekarang(),
            'jpMaks' => 13,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guru = $this->guru();

        // Jam mulai terkunci ke jam pelajaran sekarang (tidak bisa di-backdate dari form).
        $request->merge(['jam_ke_mulai' => Waktu::jpSekarang()]);

        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'jam_ke_mulai' => ['required', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'status_guru' => ['required', 'in:hadir,tugas,tidak_hadir'],
            'materi' => ['required', 'string'],
            'metode' => ['nullable', 'string', 'max:255'],
            'tugas_tambahan' => ['nullable', 'string'],
        ]);

        $jadwal = Jadwal::findOrFail($data['jadwal_id']);
        abort_unless($jadwal->guru_id === $guru->id, 403);

        // Cegah jurnal ganda untuk jadwal yang sama di hari yang sama.
        $sudahAda = Jurnal::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
        if ($sudahAda) {
            return redirect()->route('jurnal.show', $sudahAda)
                ->with('info', 'Jurnal untuk jadwal ini hari ini sudah dibuat.');
        }

        $siswaDispensasi = $this->siswaDispensasiHariIni(
            $jadwal->kelas_id, now()->toDateString(), $data['jam_ke_mulai'], $data['jam_ke_selesai']
        );

        $jurnal = DB::transaction(function () use ($data, $jadwal, $guru, $siswaDispensasi) {
            $jurnal = Jurnal::create([
                ...$data,
                'guru_id' => $guru->id,
                'tanggal' => now()->toDateString(),
            ]);

            // Absensi otomatis: default hadir, atau dispensasi bila ada dispensasi disetujui.
            foreach ($jadwal->kelas->siswas as $siswa) {
                $dispen = $siswaDispensasi->contains($siswa->id);
                Absensi::create([
                    'jurnal_id' => $jurnal->id,
                    'siswa_id' => $siswa->id,
                    'status' => $dispen ? 'dispensasi' : 'hadir',
                    'catatan' => $dispen ? 'Dispensasi (otomatis dari sistem)' : null,
                ]);
            }

            return $jurnal;
        });

        AuditLog::catat('jurnal.tambah', "Jurnal {$jadwal->mapel->nama} — {$jadwal->kelas->nama}", $jurnal);

        return redirect()->route('jurnal.presensi', $jurnal)
            ->with('success', 'Jurnal tersimpan. Sesuaikan presensi siswa bila perlu.');
    }

    /* -------------------------------------------------------- Editor presensi */
    public function presensi(Jurnal $jurnal): View
    {
        $this->milikSendiri($jurnal);
        $jurnal->load('jadwal.kelas', 'jadwal.mapel', 'absensis.siswa');

        return view('guru.jurnal.presensi', compact('jurnal'));
    }

    public function presensiSave(Jurnal $jurnal, Request $request): RedirectResponse
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa diubah.');

        $data = $request->validate([
            'presensi' => ['required', 'array'],
            'presensi.*.status' => ['required', 'in:hadir,sakit,izin,alpha,dispensasi'],
            'presensi.*.catatan' => ['nullable', 'string', 'max:255'],
            'foto_bukti' => ['nullable', 'image', 'max:4096'],
        ]);

        DB::transaction(function () use ($jurnal, $data, $request) {
            foreach ($data['presensi'] as $absensiId => $isi) {
                $jurnal->absensis()->where('id', $absensiId)->update([
                    'status' => $isi['status'],
                    'catatan' => $isi['catatan'] ?? null,
                ]);
            }

            if ($request->hasFile('foto_bukti')) {
                $jurnal->update(['foto_bukti' => $request->file('foto_bukti')->store('jurnal-bukti', 'public')]);
            }

            $this->kembalikanKePending($jurnal);
        });

        AuditLog::catat('jurnal.presensi', "Simpan presensi jurnal #{$jurnal->id}", $jurnal);

        return redirect()->route('jurnal.show', $jurnal)->with('success', 'Jurnal & absensi tersimpan.');
    }

    /* ---------------------------------------------------------------- Detail */
    public function show(Jurnal $jurnal): View
    {
        $this->milikSendiri($jurnal);
        $jurnal->load('jadwal.kelas', 'jadwal.mapel', 'absensis.siswa', 'verifikator');

        return view('guru.jurnal.show', compact('jurnal'));
    }

    public function update(Jurnal $jurnal, Request $request): RedirectResponse
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa diubah.');

        // Jam mulai tidak ikut diubah — pakai nilai jurnal untuk validasi jam selesai.
        $request->merge(['jam_ke_mulai' => $jurnal->jam_ke_mulai]);

        $data = $request->validate([
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'status_guru' => ['required', 'in:hadir,tugas,tidak_hadir'],
            'materi' => ['required', 'string'],
            'metode' => ['nullable', 'string', 'max:255'],
            'tugas_tambahan' => ['nullable', 'string'],
        ]);

        $jurnal->update($data);
        $this->kembalikanKePending($jurnal);
        AuditLog::catat('jurnal.ubah', "Ubah jurnal #{$jurnal->id}", $jurnal);

        return redirect()->route('jurnal.show', $jurnal)->with('success', 'Jurnal diperbarui.');
    }

    /** Hapus jurnal (soft delete). Hanya selama belum diverifikasi pengurus kelas. */
    public function destroy(Jurnal $jurnal): RedirectResponse
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa dihapus.');

        $label = $jurnal->jadwal->mapel->nama.' — '.$jurnal->jadwal->kelas->nama;
        $jurnal->delete();

        AuditLog::catat('jurnal.hapus', "Hapus jurnal #{$jurnal->id} ({$label})", $jurnal);

        return redirect()->route('jurnal.index')->with('success', 'Jurnal dihapus.');
    }

    /** Setelah guru merevisi, jurnal kembali antre untuk diperiksa pengurus kelas. */
    private function kembalikanKePending(Jurnal $jurnal): void
    {
        if ($jurnal->status_verifikasi === 'revisi') {
            $jurnal->update([
                'status_verifikasi' => 'pending',
                'catatan_verifikasi' => null,
                'verifikator_id' => null,
            ]);
        }
    }

    /**
     * ID siswa yang punya dispensasi disetujui pada tanggal & rentang jam tertentu.
     * Satu query untuk seluruh kelas (hindari N+1 saat membuat absensi).
     */
    private function siswaDispensasiHariIni(int $kelasId, string $tanggal, int $jamMulai, int $jamSelesai): Collection
    {
        return Dispensasi::whereDate('tanggal', $tanggal)
            ->where('status_akhir', 'approved')
            ->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId))
            ->where(fn ($q) => $q->whereNull('jam_ke_mulai')
                ->orWhere(fn ($q2) => $q2->where('jam_ke_mulai', '<=', $jamSelesai)
                    ->where('jam_ke_selesai', '>=', $jamMulai)))
            ->pluck('siswa_id');
    }
}
