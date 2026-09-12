<?php

namespace App\Support;

/**
 * QR dispensasi yang berganti tiap 10 detik (anti-screenshot) — dari spec.md §A.
 * Token dihitung, bukan disimpan: HMAC(kunci aplikasi, dispensasi_id:jendela_waktu).
 * Satpam scan -> dicek ulang dengan rumus yang sama (toleransi 1 jendela ke belakang).
 */
class QrDispensasi
{
    private const JENDELA_DETIK = 10;

    public static function token(int $dispensasiId, ?int $jendelaKe = null): string
    {
        $jendelaKe ??= intdiv(time(), self::JENDELA_DETIK);

        return substr(hash_hmac('sha256', "{$dispensasiId}:{$jendelaKe}", self::kunci()), 0, 12);
    }

    /** Cocok untuk jendela sekarang ATAU satu jendela sebelumnya (toleransi jeda kamera/koneksi). */
    public static function valid(int $dispensasiId, string $token): bool
    {
        $sekarang = intdiv(time(), self::JENDELA_DETIK);

        foreach ([$sekarang, $sekarang - 1] as $jendela) {
            if (hash_equals(self::token($dispensasiId, $jendela), $token)) {
                return true;
            }
        }

        return false;
    }

    /** Detik tersisa sebelum token saat ini kedaluwarsa — buat interval refresh halaman. */
    public static function detikSisa(): int
    {
        return self::JENDELA_DETIK - (time() % self::JENDELA_DETIK);
    }

    private static function kunci(): string
    {
        return config('app.key').'|qr-dispensasi';
    }
}
