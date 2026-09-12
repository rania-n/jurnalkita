<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Monitor Piket — bukan jadwal piket pribadi (lihat guru/piket.blade.php),
 * tapi pantauan guru piket: hari ini, tiap jam pelajaran di tiap kelas,
 * gurunya masuk/tugas luar/tidak hadir, atau jurnalnya belum diisi sama sekali.
 * Dikelompokkan per kelas ATAU per guru (bar pilih, bukan filter dropdown).
 */
class PiketController extends Controller
{
    private const LABEL_STATUS = ['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir'];

    private function pastikanBolehLihat(): void
    {
        $user = auth()->user();
        abort_unless($user->role === 'waka' || $user->isPiket(), 403, 'Hanya guru piket yang dapat mengakses ini.');
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
            'shiftPiket' => $this->shiftPiketHariItu($tanggal),
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

    /** Roster guru piket hari itu (shift jam, bukan sehari penuh) — buat ditampilkan di atas Monitor Piket. */
    private function shiftPiketHariItu(Carbon $tanggal): Collection
    {
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? null;
        if (! $hari) {
            return collect();
        }

        return JadwalPiket::where('hari', $hari)->with('guru')->orderBy('mulai')->get();
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
