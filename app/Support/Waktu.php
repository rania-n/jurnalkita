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

    /**
     * Rentang jam beneran (mis. "07:00–08:30") buat satu JP atau rentang JP,
     * dipakai nemenin label "JP 1–2" di halaman jurnal biar keliatan jam
     * aslinya, bukan cuma nomor JP doang. Null kalau data jam pelajarannya
     * nggak ketemu (mis. kategori custom yang belum diatur).
     */
    public static function rentangJam(int $jamKeMulai, ?int $jamKeSelesai = null, ?Carbon $tanggal = null): ?string
    {
        return self::rentangJamDenganKategori(self::kategori($tanggal), $jamKeMulai, $jamKeSelesai);
    }

    /**
     * Sama kayak rentangJam(), tapi buat kasus yang cuma tau NAMA HARI
     * (mis. 'selasa') bukan tanggal konkret -- dipakai di form Isi Jurnal
     * pas milih jadwal dari dropdown, jadwalnya sendiri bisa dari hari
     * apa saja terlepas dari hari ini beneran hari apa.
     */
    public static function rentangJamUntukHari(string $hari, int $jamKeMulai, ?int $jamKeSelesai = null): ?string
    {
        return self::rentangJamDenganKategori($hari === 'jumat' ? 'jumat' : 'senin_kamis', $jamKeMulai, $jamKeSelesai);
    }

    private static function rentangJamDenganKategori(string $kategori, int $jamKeMulai, ?int $jamKeSelesai = null): ?string
    {
        $awal = JamPelajaran::where('kategori', $kategori)->where('jam_ke', $jamKeMulai)->first();
        $akhir = $jamKeSelesai && $jamKeSelesai !== $jamKeMulai
            ? JamPelajaran::where('kategori', $kategori)->where('jam_ke', $jamKeSelesai)->first()
            : $awal;

        if (! $awal || ! $akhir) {
            return null;
        }

        return $awal->mulai->format('H:i').'–'.$akhir->selesai->format('H:i');
    }
}
