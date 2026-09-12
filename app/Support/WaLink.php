<?php

namespace App\Support;

/**
 * Link wa.me — versi gratis integrasi WhatsApp (spec.md §A "opsi hemat biaya").
 * Guru/Waka tinggal tekan tombol, WhatsApp kebuka dengan nomor & pesan sudah terisi,
 * tinggal pencet kirim. Tidak butuh API berbayar.
 */
class WaLink
{
    public static function url(?string $noHp, string $pesan): ?string
    {
        if (! $noHp) {
            return null;
        }

        return 'https://wa.me/'.self::normalisasi($noHp).'?text='.rawurlencode($pesan);
    }

    /** 08xx / +62xx / 62xx -> 62xx (format yang dipahami wa.me). */
    private static function normalisasi(string $noHp): string
    {
        $digit = preg_replace('/\D/', '', $noHp);

        if (str_starts_with($digit, '0')) {
            $digit = '62'.substr($digit, 1);
        }

        return $digit;
    }
}
