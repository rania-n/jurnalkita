<?php

namespace App\Support;

use App\Models\Dispensasi;
use App\Notifications\DispensasiKelas;
use App\Notifications\SiswaDispensasiDiKelasAnda;

/**
 * Satu tempat buat nyebarin notifikasi in-app saat ada pengajuan izin keluar
 * atau lomba. Sengaja dipusatkan biar semua jalur (form Izin Keluar, presensi
 * piket, sampai keputusan Waka) ngirim penerima & pesan yang SAMA -- dulu
 * guru mapel cuma dapat notifikasi kalau lewat Waka, padahal lomba
 * auto-approve juga harus nginfokan piket, pengurus kelas, & guru pengajar.
 */
class NotifikasiDispensasi
{
    /** Pengajuan baru dicatat (masih menunggu keputusan Waka). */
    public static function saatDiajukan(Dispensasi $dispensasi): void
    {
        self::kePiketDanPengurus($dispensasi, disetujui: false);
    }

    /** Dispensasi disetujui (lomba auto-approve, atau diputuskan Waka). */
    public static function saatDisetujui(Dispensasi $dispensasi): void
    {
        // Guru yang sedang mengajar kelas siswa ini pada jam tsb.
        foreach ($dispensasi->guruMapelTerkait() as $guruUser) {
            $guruUser->notify(new SiswaDispensasiDiKelasAnda($dispensasi));
        }

        self::kePiketDanPengurus($dispensasi, disetujui: true);
    }

    /** Piket yang bertugas + pengurus kelas -- dedupe per orang/kelas. */
    private static function kePiketDanPengurus(Dispensasi $dispensasi, bool $disetujui): void
    {
        $anggota = $dispensasi->anggotaKelompok();

        $pikets = $anggota
            ->flatMap(fn (Dispensasi $item) => $item->piketBertugas())
            ->unique('id');

        foreach ($pikets as $piketUser) {
            $piketUser->notify(new DispensasiKelas($dispensasi, $disetujui));
        }

        $pengurus = $anggota
            ->map(fn (Dispensasi $item) => $item->siswa->kelas?->pengurusUser())
            ->filter()
            ->unique('id');

        foreach ($pengurus as $pengurusUser) {
            $pengurusUser->notify(new DispensasiKelas($dispensasi, $disetujui));
        }
    }
}
