<?php

namespace App\Support;

use App\Models\Dispensasi;
use App\Models\Jurnal;
use App\Models\PresensiPiket;
use Illuminate\Support\Collection;

/**
 * Presensi default siswa satu kelas, dipakai bareng oleh form Isi Jurnal
 * (Guru) & Isi Jurnal Pengganti (Pengurus Kelas) -- biar aturannya SATU
 * tempat & sama persis di kedua sisi:
 *
 *   1. Dispensasi disetujui buat hari itu (opsional dipersempit ke jam
 *      tertentu) -> menang, paling spesifik/akurat.
 *   2. Kalau nggak ada, ikuti catatan sakit/izin yang dimasukkan piket.
 *   3. Kalau nggak ada juga, ikut presensi dari jurnal lain yang sudah diisi
 *      hari ini di kelas yang sama (jam berapa pun).
 *   4. Kalau belum ada sumber status -> default "Hadir".
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
        $presensiPiket = self::presensiPiket($kelasId, $tanggal);
        $presensiSebelumnya = self::presensiTerakhirHariIni($kelasId, $tanggal);

        return $siswas->mapWithKeys(function ($s) use ($siswaDispensasi, $presensiPiket, $presensiSebelumnya, $jamMulai, $jamSelesai) {
            if ($siswaDispensasi->has($s->id)) {
                $d = $siswaDispensasi[$s->id];
                $catatan = $d->alasan ?: 'Dispensasi (otomatis dari sistem)';

                // Dispensasinya kepilih karena ADA tumpang tindih jam sama jurnal
                // ini (nggak harus nutup penuh) -- kalau ternyata cuma nutup
                // SEBAGIAN (mulai belakangan atau selesai duluan dari jurnalnya),
                // kasih tau sampai/dari jam berapa di catatan, biar guru inget
                // buat ngecek manual sisa jamnya (siswa mungkin udah balik/belum
                // dateng di luar rentang itu).
                $labelJamDispen = match (true) {
                    $d->jam_ke_mulai === null => null, // sepanjang hari, gak ada batas buat dicatetin
                    $d->jam_ke_selesai === null => "JP {$d->jam_ke_mulai} sampai selesai",
                    default => "JP {$d->jam_ke_mulai}–{$d->jam_ke_selesai}",
                };
                $cumaSebagian = $labelJamDispen !== null && (
                    ($jamMulai !== null && $d->jam_ke_mulai > $jamMulai)
                    || ($jamSelesai !== null && $d->jam_ke_selesai !== null && $d->jam_ke_selesai < $jamSelesai)
                );
                if ($cumaSebagian) {
                    $catatan .= " (dispensasi {$labelJamDispen} -- cek manual buat jam di luar itu)";
                }

                return [$s->id => ['status' => 'dispensasi', 'catatan' => $catatan]];
            }

            if ($presensiPiket->has($s->id)) {
                $piket = $presensiPiket[$s->id];

                return [$s->id => ['status' => $piket->status, 'catatan' => $piket->catatan]];
            }

            $sebelumnya = $presensiSebelumnya[$s->id] ?? null;

            // Dispensasi itu ADA BATAS WAKTUNYA (nggak kayak sakit/izin/alpha yang
            // wajar sepanjang hari) -- kalau jurnal SEBELUMNYA nyatetin siswa ini
            // dispensasi (mis. JP6-8), tapi jurnal yang lagi diisi sekarang
            // jamnya udah lewat dari situ (mis. JP9), jangan ikut-ikutan
            // nurunin status "dispensasi" itu -- $siswaDispensasi di atas
            // udah ngecek ulang dari awal apa dispensasinya beneran masih
            // nyakup jam jurnal ini; kalau nggak ada di situ, berarti udah
            // kelar, harusnya balik "Hadir" lagi.
            if ($sebelumnya && $sebelumnya['status'] === 'dispensasi') {
                $sebelumnya = null;
            }

            return [$s->id => $sebelumnya ?? ['status' => 'hadir', 'catatan' => null]];
        })->all();
    }

    /** Catatan sakit/izin dari piket untuk kelas dan tanggal ini, keyed by siswa_id. */
    public static function presensiPiket(int $kelasId, string $tanggal): Collection
    {
        return PresensiPiket::whereDate('tanggal', $tanggal)
            ->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId))
            ->get(['siswa_id', 'status', 'catatan'])
            ->keyBy('siswa_id');
    }

    /**
     * Dispensasi approved buat siswa pada tanggal tsb, keyed by siswa_id (siswa_id,
     * alasan, jam_ke_mulai, jam_ke_selesai -- jam-nya dibawa buat dibandingin lagi
     * di untukKelas(), nentuin apa perlu catatan "cuma sebagian" atau nggak). Kalau
     * $jamMulai/$jamSelesai diisi, dipersempit ke jam itu (dispensasi tanpa jam_ke =
     * "sepanjang hari" tetap ikut). Kalau dikosongkan (mis. form Pengganti yang
     * presensinya dirender sebelum jadwal dipilih), ambil SEMUA dispensasi approved
     * hari itu buat kelasnya, apapun jam_ke-nya.
     */
    private static function siswaDispensasi(int $kelasId, string $tanggal, ?int $jamMulai, ?int $jamSelesai): Collection
    {
        return Dispensasi::whereDate('tanggal', $tanggal)
            ->where('status_akhir', 'approved')
            ->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelasId))
            // Bug lama: dispensasi jam_ke_selesai kosong (artinya "sampai selesai
            // hari itu", BUKAN sekosong-tanpa-batas kayak jam_ke_mulai null)
            // kebanding langsung "jam_ke_selesai >= jamMulai" di SQL -- NULL >=
            // angka apa pun hasilnya NULL (dianggap false), jadi dispensasi
            // kayak gitu nggak pernah ke-match walau jamnya beneran overlap.
            // Sekarang jam_ke_selesai NULL dianggap "sampai akhir hari" (nggak
            // ada batas atas) via whereNull tambahan.
            ->when($jamMulai !== null && $jamSelesai !== null, fn ($q) => $q->where(
                fn ($q2) => $q2->whereNull('jam_ke_mulai')
                    ->orWhere(fn ($q3) => $q3->where('jam_ke_mulai', '<=', $jamSelesai)
                        ->where(fn ($q4) => $q4->whereNull('jam_ke_selesai')->orWhere('jam_ke_selesai', '>=', $jamMulai)))
            ))
            ->get(['siswa_id', 'alasan', 'jam_ke_mulai', 'jam_ke_selesai'])
            ->keyBy('siswa_id');
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
