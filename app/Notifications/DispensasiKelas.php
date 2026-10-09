<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app untuk pengurus kelas & guru piket: ada siswa di kelas
 * mereka yang izin keluar / ikut lomba. Dikirim saat pengajuan dicatat,
 * saat disetujui, dan saat ditolak (termasuk dibatalkan otomatis sistem),
 * biar pengurus & piket nggak ketinggalan walau nggak buka halaman Izin Keluar.
 */
class DispensasiKelas extends Notification
{
    use Queueable;

    public const BARU = 'baru';

    public const DISETUJUI = 'disetujui';

    public const DITOLAK = 'ditolak';

    public function __construct(private Dispensasi $dispensasi, private string $status = self::BARU) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $anggota = $this->dispensasi->anggotaKelompok();
        $jenis = $this->dispensasi->jenis === 'lomba' ? 'Lomba' : 'Izin keluar';
        $siapa = $anggota->count() > 1
            ? $anggota->count().' siswa'
            : ($this->dispensasi->siswa->nama ?? 'Siswa');

        [$icon, $title] = match ($this->status) {
            self::DISETUJUI => ['check_circle', "$jenis disetujui"],
            self::DITOLAK => ['cancel', "$jenis ditolak"],
            default => ['fact_check', "Pengajuan $jenis baru"],
        };

        // Halaman Izin Keluar cuma kebuka buat piket/waka/admin -- pengurus
        // kelas kalau diklik malah 403, jadi diarahkan ke beranda kelasnya.
        $bolehLihat = in_array($notifiable->role ?? null, ['waka', 'admin'], true)
            || (($notifiable->role ?? null) === 'guru' && $notifiable->isPiket());
        $url = $bolehLihat
            ? route('dispensasi.index', ['lihat' => $this->dispensasi->id])
            : route($notifiable->homeRoute());

        $body = $siapa.' · '.($this->dispensasi->siswa->kelas?->nama ?? '—').' · '
            .$this->dispensasi->labelTanggal().' · '.$this->dispensasi->labelJam();
        if ($this->status === self::DITOLAK && $this->dispensasi->catatan_waka) {
            $body .= ' — '.str($this->dispensasi->catatan_waka)->limit(60);
        }

        return [
            'icon' => $icon,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ];
    }
}
