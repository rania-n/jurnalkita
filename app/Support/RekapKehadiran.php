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
            ->map(fn ($rows) => $rows->countBy(fn ($a) => self::normalisasi($a->status)));

        // 2. Rekap per Hari (agregasi 1 status per tanggal per siswa)
        // Prioritas penentuan status harian: alpha > sakit > izin > izin_keluar > hadir
        $rekapHari = $absensis->groupBy('siswa_id')
            ->map(function ($rows) {
                return $rows->groupBy(fn ($a) => $a->jurnal?->tanggal?->toDateString())
                    ->map(fn ($dateRows) => self::normalisasi(self::tentukanStatusHarian($dateRows->pluck('status'))))
                    ->countBy();
            });

        return [
            'rekapHari' => $rekapHari,
            'rekapMapel' => $rekapMapel,
        ];
    }

    /**
     * Tentukan status harian siswa jika ada beberapa status jurnal dalam satu tanggal.
     * Prioritas kedisiplinan: alpha > sakit > izin > izin_keluar > dispensasi > hadir.
     *
     * 'izin_keluar' (siswa keluar atas persetujuan piket/waka) berdiri sendiri dari
     * 'izin' -- keduanya sama-sama "siswa nggak ada di kelas", tapi laporan ke
     * sekolah perlu membedakan izin seharian vs keluar hanya sebagian jam.
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
        if ($statuses->contains('izin_keluar') || $statuses->contains('dispensasi')) {
            return 'izin_keluar';
        }

        return 'hadir';
    }

    /**
     * Status lama 'dispensasi' digabung ke 'izin_keluar' -- keduanya makna yang
     * sama, tapi data jurnal yang terlanjur tersimpan sebelum status ini
     * diganti masih penuh pakai 'dispensasi'. Digabung di satu tempat begini
     * biar semua halaman rekap (tabel desktop, kartu HP, popup detail siswa,
     * rekap wali kelas & pengurus kelas) otomatis ikut, nggak ada yang
     * ketinggalan nunjuk angka 0 terus.
     *
     * 'izin_terlambat' sengaja TIDAK digabung ke 'izin' -- datang terlambat itu
     * tetap dihitung hadir (setengah), jadi nggak boleh ngurangin angka hadir.
     */
    public static function normalisasi(string $status): string
    {
        return $status === 'dispensasi' ? 'izin_keluar' : $status;
    }

    /**
     * Urutan & label status buat ringkasan kehadiran satu jurnal/kelas.
     * Dipakai bareng di popup detail jurnal (Guru & Pengurus) & halaman detail
     * siswa, biar nggak ada 4 versi daftar yang beda-beda diulang di tiap file.
     *
     * @return array<string, array{0: string, 1: string}> status => [label, nada warna]
     */
    public static function ringkasan(): array
    {
        return [
            'hadir' => ['Hadir', 'hadir'],
            'sakit' => ['Sakit', 'sakit'],
            'izin' => ['Izin', 'izin'],
            'alpha' => ['Alpha', 'alpha'],
            'izin_keluar' => ['Izin Keluar', 'dispen'],
        ];
    }

    /**
     * Rincian lengkap rekap kehadiran satu siswa untuk modal popup.
     * Mengembalikan data per mapel dan riwayat kronologis per pertemuan.
     *
     * @return array{
     *     siswa: Siswa,
     *     dari: ?Carbon,
     *     sampai: ?Carbon,
     *     rekapPerMapel: Collection<int, array{mapel: string, kode: string, guru: string, total: int, hadir: int, sakit: int, izin: int, alpha: int, izin_keluar: int, persentase: int}>,
     *     riwayat: Collection<int, Absensi>,
     *     totalMapel: array{total: int, hadir: int, sakit: int, izin: int, alpha: int, izin_keluar: int},
     *     totalHari: array{total: int, hadir: int, sakit: int, izin: int, alpha: int, izin_keluar: int}
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
                $counts = self::hitungStatus($rows->pluck('status'));
                $total = $rows->count();
                $hadir = $counts['hadir'];
                $persentase = $total > 0 ? (int) round(($hadir / $total) * 100) : 0;

                return [
                    'mapel' => $mapel?->nama ?? 'Mata Pelajaran',
                    'kode' => $mapel?->kode ?? '—',
                    'guru' => $guru?->nama ?? '—',
                    'total' => $total,
                    'hadir' => $hadir,
                    'sakit' => $counts['sakit'],
                    'izin' => $counts['izin'],
                    'alpha' => $counts['alpha'],
                    'izin_keluar' => $counts['izin_keluar'],
                    'persentase' => $persentase,
                ];
            })
            ->sortBy('mapel')
            ->values();

        // Riwayat kronologis detail (terbaru di atas)
        $riwayat = $absensis->sortByDesc(fn ($a) => ($a->jurnal?->tanggal?->format('Y-m-d') ?? '').sprintf('%02d', $a->jurnal?->jam_ke_mulai ?? 0))->values();

        // Total ringkasan siswa
        $countsMapel = self::hitungStatus($absensis->pluck('status'));
        $countsHari = self::hitungStatus($absensis
            ->groupBy(fn ($a) => $a->jurnal?->tanggal?->toDateString())
            ->map(fn ($dateRows) => self::tentukanStatusHarian($dateRows->pluck('status'))));

        return [
            'siswa' => $siswa,
            'dari' => $dari,
            'sampai' => $sampai,
            'rekapPerMapel' => $rekapPerMapel,
            'riwayat' => $riwayat,
            'totalMapel' => [
                'total' => $absensis->count(),
                'hadir' => $countsMapel['hadir'],
                'sakit' => $countsMapel['sakit'],
                'izin' => $countsMapel['izin'],
                'alpha' => $countsMapel['alpha'],
                'izin_keluar' => $countsMapel['izin_keluar'],
            ],
            'totalHari' => [
                'total' => $countsHari->sum(),
                'hadir' => $countsHari['hadir'],
                'sakit' => $countsHari['sakit'],
                'izin' => $countsHari['izin'],
                'alpha' => $countsHari['alpha'],
                'izin_keluar' => $countsHari['izin_keluar'],
            ],
        ];
    }

    /**
     * Hitung jumlah per status, dengan status lama 'dispensasi' sudah digabung
     * ke 'izin_keluar' (lihat normalisasi()).
     *
     * Semua status di ringkasan() DIJAMIN ada di hasilnya (0 kalau belum ada),
     * biar pemanggil (tabel, kartu HP, popup detail) nggak perlu nulis
     * `?? 0` di mana-mana dan nggak pernah "kolom hilang" cuma karena belum
     * ada data. Status di luar ringkasan (mis. 'izin_terlambat', 'tugas')
     * tetap ikut kehitung, nggak diam-diam dibuang.
     *
     * @param  Collection<int, string>  $statuses
     * @return Collection<string, int>
     */
    public static function hitungStatus(Collection $statuses): Collection
    {
        $hitung = $statuses
            ->map(fn (string $status) => self::normalisasi($status))
            ->countBy();

        $lengkap = collect(array_fill_keys(array_keys(self::ringkasan()), 0))
            ->merge($hitung);

        return $lengkap;
    }
}
