<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

/**
 * Monitor Piket — bukan jadwal piket pribadi (lihat guru/piket.blade.php),
 * tapi pantauan guru piket: hari ini, tiap jam pelajaran di tiap kelas,
 * gurunya masuk/tugas luar/tidak hadir, atau jurnalnya belum diisi sama sekali.
 */
class PiketController extends Controller
{
    private function pastikanBolehLihat(): void
    {
        $user = auth()->user();
        abort_unless($user->role === 'waka' || $user->isPiket(), 403, 'Hanya guru piket yang dapat mengakses ini.');
    }

    public function index(Request $request): View
    {
        $this->pastikanBolehLihat();

        $tanggal = $this->tanggal($request);

        return view('piket.monitor', [
            'tanggal' => $tanggal,
            'baris' => $this->baris($tanggal, $request),
            'kelasList' => Kelas::orderBy('nama')->get(),
            'guruList' => Guru::whereNotNull('user_id')->orderBy('nama')->get(),
        ]);
    }

    public function ekspor(Request $request)
    {
        $this->pastikanBolehLihat();

        $tanggal = $this->tanggal($request);
        $baris = $this->baris($tanggal, $request);

        AuditLog::catat('piket.ekspor', "Ekspor monitor piket {$tanggal->toDateString()} ({$baris->count()} baris)");

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
        }, 'monitor-piket-'.$tanggal->toDateString().'.csv', ['Content-Type' => 'text/csv']);
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
    private function baris(Carbon $tanggal, Request $request): Collection
    {
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? null;

        if (! $hari) {
            return collect(); // Sabtu/Minggu — tidak ada jadwal pelajaran.
        }

        $jadwals = Jadwal::where('hari', $hari)
            ->with('kelas', 'mapel', 'guru')
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('guru_id'), fn ($q) => $q->where('guru_id', $request->integer('guru_id')))
            ->get()
            ->sortBy([['kelas.nama', 'asc'], ['jam_ke_mulai', 'asc']]);

        $jurnals = Jurnal::whereIn('jadwal_id', $jadwals->pluck('id'))
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('jadwal_id');

        $labelStatus = [
            'hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir',
        ];

        return $jadwals->map(function (Jadwal $jadwal) use ($jurnals, $tanggal, $labelStatus) {
            $jurnal = $jurnals->get($jadwal->id);

            return [
                'jadwal' => $jadwal,
                'jurnal' => $jurnal,
                'tanggal' => $tanggal->toDateString(),
                'status' => $jurnal->status_guru ?? 'belum_diisi',
                'statusLabel' => $jurnal ? ($labelStatus[$jurnal->status_guru] ?? $jurnal->status_guru) : 'Belum Diisi',
            ];
        })->values();
    }
}
