<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app untuk pengurus kelas & guru piket: ada siswa di kelas
 * mereka yang izin keluar / ikut lomba. Dikirim tiap kali pengajuan dicatat
 * (dan sekali lagi saat disetujui), biar pengurus & piket nggak ketinggalan
 * walau nggak buka halaman Izin Keluar.
 */
class DispensasiKelas extends Notification
{
    use Queueable;

    public function __construct(private Dispensasi $dispensasi, private bool $disetujui = false) {}

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

        // Halaman Izin Keluar cuma kebuka buat piket/waka/admin -- pengurus
        // kelas kalau diklik malah 403, jadi diarahkan ke beranda kelasnya.
        $bolehLihat = in_array($notifiable->role ?? null, ['waka', 'admin'], true)
            || (($notifiable->role ?? null) === 'guru' && $notifiable->isPiket());
        $url = $bolehLihat
            ? route('dispensasi.index', ['lihat' => $this->dispensasi->id])
            : route($notifiable->homeRoute());

        return [
            'icon' => 'fact_check',
            'title' => $this->disetujui ? "$jenis disetujui" : "Pengajuan $jenis baru",
            'body' => $siapa.' · '.($this->dispensasi->siswa->kelas?->nama ?? '—').' · '
                .$this->dispensasi->labelTanggal().' · '.$this->dispensasi->labelJam(),
            'url' => $url,
        ];
    }
}
