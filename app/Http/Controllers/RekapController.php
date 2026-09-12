<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

/**
 * Rekap kedisiplinan siswa lintas waktu — buat Waka & Admin.
 * Beda dari Monitor Piket (yang per-hari, semua kelas): ini per-siswa, rentang tanggal.
 */
class RekapController extends Controller
{
    private const AMBANG_ALPHA = 3; // alpha >= ini dianggap perlu perhatian

    public function siswa(Request $request): View
    {
        [$dari, $sampai, $siswas, $rekap] = $this->data($request);

        return view('rekap.siswa', [
            'siswas' => $siswas,
            'rekap' => $rekap,
            'dari' => $dari,
            'sampai' => $sampai,
            'kelasList' => Kelas::orderBy('nama')->get(),
            'ambangAlpha' => self::AMBANG_ALPHA,
        ]);
    }

    public function eksporSiswa(Request $request)
    {
        [$dari, $sampai, $siswas, $rekap] = $this->data($request);

        AuditLog::catat('Ekspor Rekap Siswa', "Ekspor rekap kehadiran siswa {$dari->toDateString()} s/d {$sampai->toDateString()}");

        return Response::streamDownload(function () use ($siswas, $rekap) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kelas', 'No. Absen', 'Nama', 'NIS', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi']);

            foreach ($siswas as $s) {
                $r = $rekap[$s->id] ?? collect();
                fputcsv($out, [
                    $s->kelas?->nama ?? '-', $s->no_absen ?? '-', $s->nama, $s->nis,
                    $r['hadir'] ?? 0, $r['sakit'] ?? 0, $r['izin'] ?? 0, $r['alpha'] ?? 0, $r['dispensasi'] ?? 0,
                ]);
            }

            fclose($out);
        }, 'rekap-kehadiran-siswa-'.$dari->toDateString().'-sd-'.$sampai->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return array{0: Carbon, 1: Carbon, 2: Collection, 3: Collection} */
    private function data(Request $request): array
    {
        $dari = $request->filled('dari') ? Carbon::parse($request->date('dari')) : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->date('sampai')) : now();

        $siswas = Siswa::with('kelas')
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->integer('kelas_id')))
            ->orderBy('kelas_id')->orderBy('no_absen')
            ->get();

        $rekap = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereHas('jurnal', fn ($q) => $q
                ->whereDate('tanggal', '>=', $dari->toDateString())
                ->whereDate('tanggal', '<=', $sampai->toDateString()))
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->countBy('status'));

        return [$dari, $sampai, $siswas, $rekap];
    }
}
