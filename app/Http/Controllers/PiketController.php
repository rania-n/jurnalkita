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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

        $totalHadir = $baris->filter(fn ($b) => str_contains(strtolower($b['statusLabel']), 'hadir') && ! str_contains(strtolower($b['statusLabel']), 'tidak'))->count();
        $totalTidakHadir = $baris->filter(fn ($b) => str_contains(strtolower($b['statusLabel']), 'tidak'))->count();
        $totalBelumDiisi = $baris->filter(fn ($b) => str_contains(strtolower($b['statusLabel']), 'belum'))->count();

        $rows = $baris->map(function ($b) {
            return [
                'tanggal' => $b['tanggal'],
                'jamKe' => "{$b['jadwal']->jam_ke_mulai}-{$b['jadwal']->jam_ke_selesai}",
                'kelas' => $b['jadwal']->kelas->nama,
                'mapel' => $b['jadwal']->mapel->nama,
                'guru' => $b['jadwal']->guru->nama,
                'statusLabel' => $b['statusLabel'],
                'materi' => $b['jurnal']->materi ?? '-',
            ];
        })->values()->all();

        $pdf = Pdf::loadView('pdf.monitor-piket-ringkas', [
            'judul' => 'Laporan Ringkasan Monitor Piket',
            'tanggal' => $tanggal,
            'rekap' => [
                'baris' => $rows,
                'totalHadir' => $totalHadir,
                'totalTidakHadir' => $totalTidakHadir,
                'totalBelumDiisi' => $totalBelumDiisi,
            ],
        ])->setPaper('a4', 'portrait');

        return $pdf->download('monitor-piket-ringkas-'.$tanggal->toDateString().'.pdf');
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

        AuditLog::catat('Ekspor Detail Piket', "Ekspor detail monitor piket — {$tipe} {$label}, {$tanggal->toDateString()}");

        $barisData = [];
        foreach ($baris as $b) {
            $jadwal = $b['jadwal'];
            $jam = "JP {$jadwal->jam_ke_mulai}-{$jadwal->jam_ke_selesai}";
            $lawan = $tipe === 'guru' ? $jadwal->kelas->nama : $jadwal->guru->nama;

            if (! $b['jurnal']) {
                $barisData[] = [
                    'jam' => $jam,
                    'mapel' => $jadwal->mapel->nama,
                    'lawan' => $lawan,
                    'statusGuru' => 'Belum Diisi',
                    'materi' => '-',
                    'metode' => '-',
                    'noAbsen' => '-',
                    'siswa' => '-',
                    'statusSiswa' => '-',
                    'catatanSiswa' => '-',
                ];

                continue;
            }

            $jurnal = $b['jurnal'];
            $absensis = $jurnal->absensis->sortBy('siswa.no_absen');

            if ($absensis->isEmpty()) {
                $barisData[] = [
                    'jam' => $jam,
                    'mapel' => $jadwal->mapel->nama,
                    'lawan' => $lawan,
                    'statusGuru' => $b['statusLabel'],
                    'materi' => $jurnal->materi,
                    'metode' => $jurnal->metode ?? '-',
                    'noAbsen' => '-',
                    'siswa' => '-',
                    'statusSiswa' => '-',
                    'catatanSiswa' => '-',
                ];

                continue;
            }

            foreach ($absensis as $a) {
                $barisData[] = [
                    'jam' => $jam,
                    'mapel' => $jadwal->mapel->nama,
                    'lawan' => $lawan,
                    'statusGuru' => $b['statusLabel'],
                    'materi' => $jurnal->materi,
                    'metode' => $jurnal->metode ?? '-',
                    'noAbsen' => $a->siswa->no_absen ?? '-',
                    'siswa' => $a->siswa->nama,
                    'statusSiswa' => ucfirst($a->status),
                    'catatanSiswa' => $a->catatan ?? '-',
                ];
            }
        }

        $pdf = Pdf::loadView('pdf.monitor-piket-detail', [
            'judul' => "Laporan Detail Monitor Piket - {$label}",
            'tanggal' => $tanggal,
            'tipe' => $tipe,
            'targetNama' => $label,
            'baris' => $barisData,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("monitor-piket-{$tipe}-".Str::slug($label).'-'.$tanggal->toDateString().'.pdf');
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
