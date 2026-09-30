<?php

namespace App\Support;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class RekapKehadiran
{
    /**
     * Hitung rekap kehadiran siswa satu kelas, mengembalikan rekapHari dan rekapMapel.
     *
     * @param  EloquentCollection<int, Siswa>|Collection<int, Siswa>  $siswas
     * @return array{rekapHari: Collection<int, Collection<string, int>>, rekapMapel: Collection<int, Collection<string, int>>}
     */
    public static function untukKelas(Kelas $kelas, Collection $siswas, ?Carbon $dari = null, ?Carbon $sampai = null): array
    {
        $absensis = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereHas('jurnal', fn ($q) => $q
                ->whereHas('jadwal', fn ($q2) => $q2->where('kelas_id', $kelas->id))
                ->when($dari, fn ($q2) => $q2->whereDate('tanggal', '>=', $dari))
                ->when($sampai, fn ($q2) => $q2->whereDate('tanggal', '<=', $sampai)))
            ->with(['jurnal:id,tanggal,jadwal_id,jam_ke_mulai'])
            ->get();

        // 1. Rekap per Mapel (setiap baris absensi = 1 jam pelajaran / pertemuan mapel)
        $rekapMapel = $absensis->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->countBy('status'));

        // 2. Rekap per Hari (agregasi 1 status per tanggal per siswa)
        // Prioritas penentuan status harian: alpha > sakit > izin > dispensasi > hadir
        $rekapHari = $absensis->groupBy('siswa_id')
            ->map(function ($rows) {
                return $rows->groupBy(fn ($a) => $a->jurnal?->tanggal?->toDateString())
                    ->map(fn ($dateRows) => self::tentukanStatusHarian($dateRows->pluck('status')))
                    ->countBy();
            });

        return [
            'rekapHari' => $rekapHari,
            'rekapMapel' => $rekapMapel,
        ];
    }

    /**
     * Tentukan status harian siswa jika ada beberapa status jurnal dalam satu tanggal.
     * Prioritas kedisiplinan: alpha > sakit > izin > dispensasi > hadir.
     *
     * @param  Collection<int, string>  $statuses
     */
    public static function tentukanStatusHarian(Collection $statuses): string
    {
        if ($statuses->contains('alpha')) {
            return 'alpha';
        }
        if ($statuses->contains('sakit')) {
            return 'sakit';
        }
        if ($statuses->contains('izin')) {
            return 'izin';
        }
        if ($statuses->contains('dispensasi')) {
            return 'dispensasi';
        }

        return 'hadir';
    }

    /**
     * Rincian lengkap rekap kehadiran satu siswa untuk modal popup.
     * Mengembalikan data per mapel dan riwayat kronologis per pertemuan.
     *
     * @return array{
     *     siswa: Siswa,
     *     dari: ?Carbon,
     *     sampai: ?Carbon,
     *     rekapPerMapel: Collection<int, array{mapel: string, kode: string, guru: string, total: int, hadir: int, sakit: int, izin: int, alpha: int, dispensasi: int, persentase: int}>,
     *     riwayat: Collection<int, Absensi>,
     *     totalMapel: array{total: int, hadir: int, sakit: int, izin: int, alpha: int, dispensasi: int},
     *     totalHari: array{total: int, hadir: int, sakit: int, izin: int, alpha: int, dispensasi: int}
     * }
     */
    public static function detailSiswa(Siswa $siswa, ?Carbon $dari = null, ?Carbon $sampai = null): array
    {
        $absensis = Absensi::where('siswa_id', $siswa->id)
            ->whereHas('jurnal', fn ($q) => $q
                ->when($dari, fn ($q2) => $q2->whereDate('tanggal', '>=', $dari))
                ->when($sampai, fn ($q2) => $q2->whereDate('tanggal', '<=', $sampai)))
            ->with([
                'jurnal.jadwal.mapel',
                'jurnal.guru',
            ])
            ->get();

        // Rekap ringkas per mapel
        $rekapPerMapel = $absensis->groupBy(fn ($a) => $a->jurnal?->jadwal?->mapel_id ?? 0)
            ->map(function ($rows) {
                $first = $rows->first();
                $mapel = $first->jurnal?->jadwal?->mapel;
                $guru = $first->jurnal?->guru;
                $counts = $rows->countBy('status');
                $total = $rows->count();
                $hadir = $counts['hadir'] ?? 0;
                $persentase = $total > 0 ? (int) round(($hadir / $total) * 100) : 0;

                return [
                    'mapel' => $mapel?->nama ?? 'Mata Pelajaran',
                    'kode' => $mapel?->kode ?? '—',
                    'guru' => $guru?->nama ?? '—',
                    'total' => $total,
                    'hadir' => $hadir,
                    'sakit' => $counts['sakit'] ?? 0,
                    'izin' => $counts['izin'] ?? 0,
                    'alpha' => $counts['alpha'] ?? 0,
                    'dispensasi' => $counts['dispensasi'] ?? 0,
                    'persentase' => $persentase,
                ];
            })
            ->sortBy('mapel')
            ->values();

        // Riwayat kronologis detail (terbaru di atas)
        $riwayat = $absensis->sortByDesc(fn ($a) => ($a->jurnal?->tanggal?->format('Y-m-d') ?? '').sprintf('%02d', $a->jurnal?->jam_ke_mulai ?? 0))->values();

        // Total ringkasan siswa
        $countsMapel = $absensis->countBy('status');
        $countsHari = $absensis->groupBy(fn ($a) => $a->jurnal?->tanggal?->toDateString())
            ->map(fn ($dateRows) => self::tentukanStatusHarian($dateRows->pluck('status')))
            ->countBy();

        return [
            'siswa' => $siswa,
            'dari' => $dari,
            'sampai' => $sampai,
            'rekapPerMapel' => $rekapPerMapel,
            'riwayat' => $riwayat,
            'totalMapel' => [
                'total' => $absensis->count(),
                'hadir' => $countsMapel['hadir'] ?? 0,
                'sakit' => $countsMapel['sakit'] ?? 0,
                'izin' => $countsMapel['izin'] ?? 0,
                'alpha' => $countsMapel['alpha'] ?? 0,
                'dispensasi' => $countsMapel['dispensasi'] ?? 0,
            ],
            'totalHari' => [
                'total' => $countsHari->sum(),
                'hadir' => $countsHari['hadir'] ?? 0,
                'sakit' => $countsHari['sakit'] ?? 0,
                'izin' => $countsHari['izin'] ?? 0,
                'alpha' => $countsHari['alpha'] ?? 0,
                'dispensasi' => $countsHari['dispensasi'] ?? 0,
            ],
        ];
    }
}
