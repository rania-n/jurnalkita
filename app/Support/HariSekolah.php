<?php

namespace App\Support;

use App\Models\HariKhusus;

/** Hari sekolah (Senin-Jumat) -- dipakai buat jadwal piket, jadwal waka, dsb. */
class HariSekolah
{
    private const URUTAN = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

    /** Nama hari sekolah hari ini, atau null kalau Sabtu/Minggu. */
    public static function hariIni(): ?string
    {
        if (HariKhusus::untukTanggal(today())?->jenis === 'tanpa_kbm') {
            return null;
        }

        return self::URUTAN[now()->dayOfWeek - 1] ?? null;
    }
}
