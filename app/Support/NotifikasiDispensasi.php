<?php

namespace App\Support;

use App\Models\Dispensasi;
use App\Notifications\DispensasiDiputuskan;
use App\Notifications\DispensasiKelas;
use App\Notifications\SiswaDispensasiDiKelasAnda;

/**
 * Satu tempat buat nyebarin notifikasi in-app saat ada pengajuan izin keluar
 * atau lomba. Sengaja dipusatkan biar semua jalur (form Izin Keluar, presensi
 * piket, keputusan Waka di web maupun lewat tautan WhatsApp, sampai batal
 * otomatis sistem) ngirim penerima & pesan yang SAMA.
 */
class NotifikasiDispensasi
{
    /** Pengajuan baru dicatat (masih menunggu keputusan Waka). */
    public static function saatDiajukan(Dispensasi $dispensasi): void
    {
        self::kePiketDanPengurus($dispensasi, DispensasiKelas::BARU);
    }

    /** Dispensasi disetujui (lomba auto-approve, atau diputuskan Waka). */
    public static function saatDisetujui(Dispensasi $dispensasi, ?int $kecualiUserId = null): void
    {
        // Guru yang sedang mengajar kelas siswa ini pada jam tsb.
        foreach ($dispensasi->guruMapelTerkait() as $guruUser) {
            $guruUser->notify(new SiswaDispensasiDiKelasAnda($dispensasi));
        }

        self::kePiketDanPengurus($dispensasi, DispensasiKelas::DISETUJUI, $kecualiUserId);
    }

    /** Dispensasi ditolak Waka, atau dibatalkan otomatis karena lewat batas. */
    public static function saatDitolak(Dispensasi $dispensasi, ?int $kecualiUserId = null): void
    {
        self::kePiketDanPengurus($dispensasi, DispensasiKelas::DITOLAK, $kecualiUserId);
    }

    /**
     * Keputusan final (setuju/tolak) -- pengaju dapat DispensasiDiputuskan,
     * lalu disebar ke pihak terkait sesuai hasilnya.
     */
    public static function saatDiputuskan(Dispensasi $dispensasi): void
    {
        $dispensasi->pengaju?->notify(new DispensasiDiputuskan($dispensasi));

        // Pengaju sudah dapat DispensasiDiputuskan -- jangan dobel lewat
        // notifikasi piket/pengurus kalau kebetulan dia piket hari itu.
        $dispensasi->status_akhir === 'approved'
            ? self::saatDisetujui($dispensasi, $dispensasi->diajukan_oleh_id)
            : self::saatDitolak($dispensasi, $dispensasi->diajukan_oleh_id);
    }

    /** Piket yang bertugas + pengurus kelas -- dedupe per orang/kelas. */
    private static function kePiketDanPengurus(Dispensasi $dispensasi, string $status, ?int $kecualiUserId = null): void
    {
        $anggota = $dispensasi->anggotaKelompok();

        $penerima = $anggota
            ->flatMap(fn (Dispensasi $item) => $item->piketBertugas())
            ->merge($anggota->map(fn (Dispensasi $item) => $item->siswa->kelas?->pengurusUser())->filter())
            ->unique('id')
            ->reject(fn ($user) => $kecualiUserId && $user->id === $kecualiUserId);

        foreach ($penerima as $user) {
            $user->notify(new DispensasiKelas($dispensasi, $status));
        }
    }
}
