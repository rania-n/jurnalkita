<?php

namespace App\Support;

use App\Models\Dispensasi;
use App\Models\Jurnal;
use Illuminate\Support\Collection;

/**
 * Presensi default siswa satu kelas, dipakai bareng oleh form Isi Jurnal
 * (Guru) & Isi Jurnal Pengganti (Pengurus Kelas) -- biar aturannya SATU
 * tempat & sama persis di kedua sisi:
 *
 *   1. Dispensasi disetujui buat hari itu (opsional dipersempit ke jam
 *      tertentu) -> menang, paling spesifik/akurat.
 *   2. Kalau nggak ada, ikut presensi dari jurnal LAIN yang sudah diisi
 *      hari ini di kelas yang sama (jam berapa pun) -- biar guru/pengurus
 *      kelas berikutnya nggak perlu nandain ulang dari nol siswa yang
 *      tadi sudah ketahuan sakit/izin/alpha, tinggal ubah kalau memang
 *      ada yang berubah.
 *   3. Kalau nggak ada dua-duanya sama sekali -> default "Hadir".
 *
 * Reset otomatis tiap hari beda karena semuanya di-query per tanggal.
 */
class PresensiDefault
{
    /**
     * @return array<int, array{status: string, catatan: ?string}> keyed by siswa_id
     */
    public static function untukKelas(Collection $siswas, int $kelasId, string $tanggal, ?int $jamMulai = null, ?int $jamSelesai = null): array
    {
        $siswaDispensasi = self::siswaDispensasi($kelasId, $tanggal, $jamMulai, $jamSelesai);
        $presensiSebelumnya = self::presensiTerakhirHariIni($kelasId, $tanggal);

        return $siswas->mapWithKeys(function ($s) use ($siswaDispensasi, $presensiSebelumnya) {
            if ($siswaDispensasi->contains($s->id)) {
                return [$s->id => ['status' => 'dispensasi', 'catatan' => 'Dispensasi (otomatis dari sistem)']];
            }

            return [$s->id => $presensiSebelumnya[$s->id] ?? ['status' => 'hadir', 'catatan' => null]];
        })->all();
    }

    /**
     * ID siswa yang punya dispensasi disetujui pada tanggal tsb. Kalau
     * $jamMulai/$jamSelesai diisi, dipersempit ke jam itu (dispensasi tanpa
     * jam_ke = "sepanjang hari" tetap ikut). Kalau dikosongkan (mis. form
     * Pengganti yang presensinya dirender sebelum jadwal dipilih), ambil
     * SEMUA dispensasi approved hari itu buat kelasnya, apapun jam_ke-nya.
     */
    private static function siswaDispensasi(int $kelasId, string $tanggal, ?int $jamMulai, ?int $jamSelesai): Collection
    {
        return Dispensasi::whereDate('tanggal', $tanggal)
            ->where('status_akhir', 'approved')
            ->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId))
            ->when($jamMulai !== null && $jamSelesai !== null, fn ($q) => $q->where(
                fn ($q2) => $q2->whereNull('jam_ke_mulai')
                    ->orWhere(fn ($q3) => $q3->where('jam_ke_mulai', '<=', $jamSelesai)->where('jam_ke_selesai', '>=', $jamMulai))
            ))
            ->pluck('siswa_id');
    }

    /** Presensi siswa dari jurnal TERAKHIR yang sudah diisi hari ini di kelas yang sama. */
    private static function presensiTerakhirHariIni(int $kelasId, string $tanggal): array
    {
        $jurnalTerakhir = Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelasId))
            ->whereDate('tanggal', $tanggal)
            ->latest('created_at')
            ->with('absensis')
            ->first();

        return $jurnalTerakhir
            ? $jurnalTerakhir->absensis->mapWithKeys(fn ($a) => [$a->siswa_id => ['status' => $a->status, 'catatan' => $a->catatan]])->all()
            : [];
    }
}
