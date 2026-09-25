<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\PresensiPiket;
use App\Models\Siswa;
use App\Support\Versi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Monitor Piket — bukan jadwal piket pribadi (lihat guru/piket.blade.php),
 * tapi pantauan guru piket: hari ini, tiap jam pelajaran di tiap kelas,
 * gurunya masuk/tidak hadir, atau jurnalnya belum diisi sama sekali.
 * Dikelompokkan per kelas ATAU per guru (bar pilih, bukan filter dropdown).
 */
class PiketController extends Controller
{
    private const LABEL_STATUS = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];

    private function pastikanBolehLihat(): void
    {
        $user = auth()->user();
        abort_unless(
            in_array($user->role, ['waka', 'admin'], true) || $user->isPiket(),
            403,
            'Hanya guru piket yang dapat mengakses ini.'
        );
    }

    private function pastikanBolehInputPresensi(): void
    {
        abort_unless(auth()->user()?->isPiket(), 403, 'Hanya guru piket yang dapat mencatat surat izin siswa.');
    }

    public function presensiSiswa(Request $request): View
    {
        $this->pastikanBolehInputPresensi();

        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->date('tanggal')) : today();
        $kelasList = Kelas::orderedByHierarchy()->get();
        $kelas = $request->filled('kelas_id') ? Kelas::findOrFail($request->integer('kelas_id')) : null;
        $siswas = $kelas?->siswas()->where('status', 'aktif')->orderBy('no_absen')->get() ?? collect();
        $catatanPresensi = PresensiPiket::whereDate('tanggal', $tanggal)
            ->when($kelas, fn ($query) => $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelas->id)))
            ->with('siswa.kelas', 'dicatatOleh')
            ->latest('updated_at')
            ->get();
        $presensiTerpilih = $request->filled('siswa_id')
            ? PresensiPiket::where('siswa_id', $request->integer('siswa_id'))
                ->whereDate('tanggal', $tanggal)
                ->when($kelas, fn ($query) => $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelas->id)))
                ->first()
            : null;

        return view('piket.presensi-siswa', compact('tanggal', 'kelasList', 'kelas', 'siswas', 'catatanPresensi', 'presensiTerpilih'));
    }

    public function simpanPresensiSiswa(Request $request): RedirectResponse
    {
        $this->pastikanBolehInputPresensi();

        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'status' => ['required', 'in:sakit,izin'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $siswa = Siswa::where('status', 'aktif')->findOrFail($data['siswa_id']);
        $suratPath = $request->file('surat')?->store('presensi-piket', 'public');

        DB::transaction(function () use ($data, $siswa, $suratPath) {
            $presensi = PresensiPiket::updateOrCreate(
                ['siswa_id' => $siswa->id, 'tanggal' => $data['tanggal']],
                [
                    'status' => $data['status'],
                    'catatan' => $data['catatan'] ?? null,
                    'surat_path' => $suratPath ?? PresensiPiket::where('siswa_id', $siswa->id)
                        ->whereDate('tanggal', $data['tanggal'])->value('surat_path'),
                    'dicatat_oleh_id' => auth()->id(),
                ]
            );

            $jurnalsHariIni = Jurnal::whereDate('tanggal', $data['tanggal'])
                ->whereHas('jadwal', fn ($query) => $query->where('kelas_id', $siswa->kelas_id))
                ->with('absensis')
                ->get();

            foreach ($jurnalsHariIni as $jurnal) {
                Absensi::updateOrCreate(
                    ['jurnal_id' => $jurnal->id, 'siswa_id' => $siswa->id],
                    ['status' => $presensi->status, 'catatan' => $presensi->catatan]
                );
            }
        });

        AuditLog::catat('Catat Presensi Siswa oleh Piket', "{$siswa->nama} dicatat {$data['status']} pada {$data['tanggal']}");

        return redirect()->route('piket.presensi-siswa.index', [
            'tanggal' => $data['tanggal'],
            'kelas_id' => $siswa->kelas_id,
            'siswa_id' => $siswa->id,
        ])->with('success', "Presensi {$siswa->nama} tersimpan dan disamakan ke jurnal kelas pada tanggal tersebut.");
    }

    public function index(Request $request): View
    {
        $this->pastikanBolehLihat();

        $tanggal = $this->tanggal($request);
        $mode = $request->get('mode') === 'guru' ? 'guru' : 'kelas';
        $baris = $this->baris($tanggal);

        $grup = $baris
            ->groupBy(fn ($b) => $mode === 'guru' ? $b['jadwal']->guru_id : $b['jadwal']->kelas_id)
            ->map(function (Collection $rows, $id) use ($mode) {
                $contoh = $rows->first()['jadwal'];

                return [
                    'id' => $id,
                    'label' => $mode === 'guru' ? $contoh->guru->nama : $contoh->kelas->nama,
                    'rows' => $rows->sortBy(fn ($b) => $b['jadwal']->jam_ke_mulai)->values(),
                    'rekap' => $rows->countBy('status'),
                ];
            })
            ->sortBy('label')
            ->values();

        return view('piket.monitor', [
            'tanggal' => $tanggal,
            'mode' => $mode,
            'grup' => $grup,
            'rekapTotal' => $baris->countBy('status'),
        ]);
    }

    /** Endpoint ringan buat di-poll (initAutoRefresh()) -- lihat App\Support\Versi. */
    public function versi(Request $request): JsonResponse
    {
        $this->pastikanBolehLihat();

        $tanggal = $this->tanggal($request);
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? null;
        $jadwalIds = $hari ? Jadwal::where('hari', $hari)->pluck('id') : collect();

        return response()->json([
            'versi' => Versi::dari(Jurnal::whereIn('jadwal_id', $jadwalIds)->whereDate('tanggal', $tanggal)),
        ]);
    }

    /** Ekspor ringkas satu hari penuh, semua kelompok — cuma baris jam/status, tanpa presensi. */
    public function ekspor(Request $request)
    {
        $this->pastikanBolehLihat();

        $tanggal = $this->tanggal($request);
        $baris = $this->baris($tanggal);

        AuditLog::catat('Ekspor Ringkasan Piket', "Ekspor ringkas monitor piket {$tanggal->toDateString()} ({$baris->count()} baris)");

        return Response::streamDownload(function () use ($baris) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Jam', 'Kelas', 'Mata Pelajaran', 'Guru', 'Status', 'Materi']);

            foreach ($baris as $b) {
                fputcsv($out, [
                    $b['tanggal'], "JP {$b['jadwal']->jam_ke_mulai}-{$b['jadwal']->jam_ke_selesai}",
                    $b['jadwal']->kelas->nama, $b['jadwal']->mapel->nama, $b['jadwal']->guru->nama,
                    $b['statusLabel'], $b['jurnal']->materi ?? '-',
                ]);
            }

            fclose($out);
        }, 'monitor-piket-ringkas-'.$tanggal->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Ekspor lengkap satu kelompok (satu kelas ATAU satu guru) pada tanggal tsb:
     * tiap jadwal + materi/metode + daftar presensi siswa satu-satu.
     */
    public function eksporDetail(Request $request, string $tipe, int $id)
    {
        $this->pastikanBolehLihat();
        abort_unless(in_array($tipe, ['kelas', 'guru'], true), 404);

        $tanggal = $this->tanggal($request);
        $baris = $this->baris($tanggal, denganPresensi: true)
            ->filter(fn ($b) => ($tipe === 'guru' ? $b['jadwal']->guru_id : $b['jadwal']->kelas_id) === $id)
            ->sortBy(fn ($b) => $b['jadwal']->jam_ke_mulai);

        abort_if($baris->isEmpty(), 404);

        $label = $tipe === 'guru' ? $baris->first()['jadwal']->guru->nama : $baris->first()['jadwal']->kelas->nama;
        $kolomLawan = $tipe === 'guru' ? 'Kelas' : 'Guru';

        AuditLog::catat('Ekspor Detail Piket', "Ekspor detail monitor piket — {$tipe} {$label}, {$tanggal->toDateString()}");

        return Response::streamDownload(function () use ($baris, $tipe) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Jam', 'Mata Pelajaran', $tipe === 'guru' ? 'Kelas' : 'Guru', 'Status Guru', 'Materi', 'Metode', 'No. Absen', 'Nama Siswa', 'Status Siswa', 'Catatan Siswa']);

            foreach ($baris as $b) {
                $jadwal = $b['jadwal'];
                $jam = "JP {$jadwal->jam_ke_mulai}-{$jadwal->jam_ke_selesai}";
                $lawan = $tipe === 'guru' ? $jadwal->kelas->nama : $jadwal->guru->nama;

                if (! $b['jurnal']) {
                    fputcsv($out, [$jam, $jadwal->mapel->nama, $lawan, 'Belum Diisi', '-', '-', '-', '-', '-', '-']);

                    continue;
                }

                $jurnal = $b['jurnal'];
                $absensis = $jurnal->absensis->sortBy('siswa.no_absen');

                if ($absensis->isEmpty()) {
                    fputcsv($out, [$jam, $jadwal->mapel->nama, $lawan, $b['statusLabel'], $jurnal->materi, $jurnal->metode ?? '-', '-', '-', '-', '-']);

                    continue;
                }

                foreach ($absensis as $a) {
                    fputcsv($out, [
                        $jam, $jadwal->mapel->nama, $lawan, $b['statusLabel'], $jurnal->materi, $jurnal->metode ?? '-',
                        $a->siswa->no_absen ?? '-', $a->siswa->nama, ucfirst($a->status), $a->catatan ?? '-',
                    ]);
                }
            }

            fclose($out);
        }, "monitor-piket-{$tipe}-".Str::slug($label).'-'.$tanggal->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Fragment HTML (bukan halaman penuh) buat popup "lihat detail" di Monitor
     * Piket -- guru piket/waka/admin boleh lihat jurnal GURU LAIN di sini
     * (read-only, beda dari JurnalController::show() yang dikunci milik sendiri).
     */
    public function jurnalDetail(Jurnal $jurnal): View
    {
        $this->pastikanBolehLihat();
        $jurnal->load('jadwal.kelas', 'jadwal.mapel', 'guru', 'absensis.siswa');

        return view('piket._jurnal-detail-fragment', compact('jurnal'));
    }

    private function tanggal(Request $request): Carbon
    {
        return $request->filled('tanggal')
            ? Carbon::parse($request->date('tanggal'))
            : today();
    }

    /**
     * Satu baris per jadwal (kelas+jam) pada hari yang sama dengan tanggal $tanggal,
     * digabung dengan jurnal (kalau sudah diisi) pada tanggal itu persis.
     */
    private function baris(Carbon $tanggal, bool $denganPresensi = false): Collection
    {
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? null;

        if (! $hari) {
            return collect(); // Sabtu/Minggu — tidak ada jadwal pelajaran.
        }

        $jadwals = Jadwal::where('hari', $hari)->with('kelas', 'mapel', 'guru')->get();

        $jurnalQuery = Jurnal::whereIn('jadwal_id', $jadwals->pluck('id'))->whereDate('tanggal', $tanggal);
        if ($denganPresensi) {
            $jurnalQuery->with('absensis.siswa');
        }
        $jurnals = $jurnalQuery->get()->keyBy('jadwal_id');

        return $jadwals
            ->sortBy([['kelas.nama', 'asc'], ['jam_ke_mulai', 'asc']])
            ->map(function (Jadwal $jadwal) use ($jurnals, $tanggal) {
                $jurnal = $jurnals->get($jadwal->id);

                return [
                    'jadwal' => $jadwal,
                    'jurnal' => $jurnal,
                    'tanggal' => $tanggal->toDateString(),
                    'status' => $jurnal->status_guru ?? 'belum_diisi',
                    'statusLabel' => $jurnal ? (self::LABEL_STATUS[$jurnal->status_guru] ?? $jurnal->status_guru) : 'Belum Diisi',
                ];
            })
            ->values();
    }
}
