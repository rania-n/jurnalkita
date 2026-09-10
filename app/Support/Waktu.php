<?php

namespace App\Support;

use App\Models\JamPelajaran;
use Illuminate\Support\Carbon;

class Waktu
{
    /** Kategori jam pelajaran untuk sebuah tanggal. */
    public static function kategori(?Carbon $tanggal = null): string
    {
        return match (($tanggal ?? now())->dayOfWeek) {
            Carbon::FRIDAY => 'jumat',
            default => 'senin_kamis',
        };
    }

    /**
     * Jam ke- (JP) yang sedang berjalan sekarang berdasarkan konfigurasi jam pelajaran.
     * Kalau di luar semua rentang, pakai JP terakhir yang sudah dimulai, atau $default.
     */
    public static function jpSekarang(int $default = 1): int
    {
        $kategori = self::kategori();
        $sekarang = now()->format('H:i:s');

        return JamPelajaran::where('kategori', $kategori)
            ->where('mulai', '<=', $sekarang)->where('selesai', '>=', $sekarang)
            ->value('jam_ke')
            ?? JamPelajaran::where('kategori', $kategori)
                ->where('mulai', '<=', $sekarang)->orderByDesc('mulai')
                ->value('jam_ke')
            ?? $default;
    }
}
