<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\CatatanTerlambat;
use App\Models\Kelas;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        [$dari, $sampai, $siswas, $rekap, $terlambat] = $this->data($request);

        return view('rekap.siswa', [
            'siswas' => $siswas,
            'rekap' => $rekap,
            'terlambat' => $terlambat,
            'dari' => $dari,
            'sampai' => $sampai,
            'kelasList' => Kelas::orderedByHierarchy()->get(),
            'ambangAlpha' => self::AMBANG_ALPHA,
        ]);
    }

    // Ambang batas jumlah siswa buat 1 PDF -- di atas ini dompdf-nya beneran
    // nge-hang/500 (dites langsung: 2598 siswa tanpa filter bikin PHP abis
    // memori bahkan udah dinaikin ke 512M, sementara 1 kelas ~35 siswa lancar
    // dalam hitungan detik). Solusinya BUKAN naikin memory terus -- itu cuma
    // nunda, sekolah makin gede tetap bisa nabrak lagi -- tapi cegah kasusnya
    // dari awal + kasih tau kenapa, biar admin ngerti perlu nyaring dulu.
    private const BATAS_EKSPOR_SISWA = 500;

    public function eksporSiswa(Request $request)
    {
        [$dari, $sampai, $siswas, $rekap, $terlambat] = $this->data($request);

        if ($siswas->count() > self::BATAS_EKSPOR_SISWA) {
            return back()->with('error', "Terlalu banyak siswa untuk 1 laporan PDF ({$siswas->count()} siswa). Pilih kelas tertentu dulu lewat filter \"Kelas\", atau persempit rentang tanggalnya, baru ekspor lagi.");
        }

        // Margin aman tambahan buat sisi atas ambang (mis. 400-500 siswa) --
        // batas bawaan (128M) sempit banget buat dompdf, biar nggak mepet.
        ini_set('memory_limit', '256M');

        AuditLog::catat('Ekspor Rekap Siswa', "Ekspor rekap kehadiran siswa {$dari->toDateString()} s/d {$sampai->toDateString()}");

        $daftar = [];
        foreach ($siswas as $s) {
            $r = $rekap[$s->id] ?? collect();
            $daftar[] = [
                'kelas' => $s->kelas?->nama ?? '-',
                'no_absen' => $s->no_absen ?? '-',
                'nama' => $s->nama,
                'nis' => $s->nis,
                'hadir' => $r['hadir'] ?? 0,
                'sakit' => $r['sakit'] ?? 0,
                'izin' => $r['izin'] ?? 0,
                'alpha' => $r['alpha'] ?? 0,
                'dispensasi' => $r['dispensasi'] ?? 0,
                'terlambat' => $terlambat[$s->id] ?? 0,
            ];
        }

        $kelasNama = 'Semua Kelas';
        if ($request->filled('kelas_id')) {
            $k = Kelas::find($request->kelas_id);
            if ($k) {
                $kelasNama = $k->nama;
            }
        }

        $pdf = Pdf::loadView('pdf.rekap-kehadiran-siswa', [
            'judul' => 'Rekap Kehadiran Siswa',
            'dari' => $dari,
            'sampai' => $sampai,
            'kelasNama' => $kelasNama,
            'daftar' => $daftar,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('rekap-kehadiran-siswa-'.$dari->toDateString().'-sd-'.$sampai->toDateString().'.pdf');
    }

    /** @return array{0: Carbon, 1: Carbon, 2: Collection, 3: Collection, 4: Collection} */
    private function data(Request $request): array
    {
        $dari = $request->filled('dari') ? Carbon::parse($request->date('dari')) : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->date('sampai')) : now();

        $siswas = Siswa::with('kelas')
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', '%'.$request->string('cari').'%')
                ->orWhere('nis', 'like', '%'.$request->string('cari').'%')
            ))
            ->orderBy('kelas_id')->orderBy('no_absen')
            ->get();

        $rekap = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereHas('jurnal', fn ($q) => $q
                ->whereDate('tanggal', '>=', $dari->toDateString())
                ->whereDate('tanggal', '<=', $sampai->toDateString()))
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->countBy('status'));

        $terlambat = CatatanTerlambat::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereDate('tanggal', '>=', $dari->toDateString())
            ->whereDate('tanggal', '<=', $sampai->toDateString())
            ->get()
            ->countBy('siswa_id');

        return [$dari, $sampai, $siswas, $rekap, $terlambat];
    }
}
