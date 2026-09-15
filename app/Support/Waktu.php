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

    /**
     * Jam ke- (JP) yang BENERAN sedang berjalan detik ini -- null kalau di luar
     * semua jam pelajaran (sebelum JP1 mulai, pas istirahat/setelah pulang, atau
     * tengah malam). Beda sama jpSekarang(): itu ada fallback (jam terakhir yang
     * sudah lewat, atau default) buat kebutuhan UI "tebakan awal", jadi SELALU
     * balikin angka walau lagi bukan jam sekolah sama sekali -- nggak cocok buat
     * ngambil keputusan "ini beneran jadwal yang lagi berlangsung", karena bisa
     * salah kunci ke jadwal yang sama sekali nggak relevan (mis. jam 1 pagi
     * kebaca seolah "JP1" gara-gara fallback-nya).
     */
    public static function jpAktifSekarang(): ?int
    {
        return JamPelajaran::where('kategori', self::kategori())
            ->where('mulai', '<=', now()->format('H:i:s'))
            ->where('selesai', '>=', now()->format('H:i:s'))
            ->value('jam_ke');
    }

    /** Jam mulai (hari ini, sebagai Carbon lengkap) buat JP tertentu. Null kalau JP-nya tidak ada. */
    public static function mulaiJpHariIni(int $jamKe): ?Carbon
    {
        $mulai = JamPelajaran::where('kategori', self::kategori())->where('jam_ke', $jamKe)->value('mulai');

        return $mulai ? now()->copy()->setTimeFromTimeString($mulai) : null;
    }
}
