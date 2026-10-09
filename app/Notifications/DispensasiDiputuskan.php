<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DispensasiDiputuskan extends Notification
{
    use Queueable;

    public function __construct(private Dispensasi $dispensasi) {}

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
        $disetujui = $this->dispensasi->status_akhir === 'approved';
        $anggota = $this->dispensasi->anggotaKelompok();
        $jenis = $this->dispensasi->jenis === 'lomba' ? 'Lomba' : 'Izin keluar';
        $siapa = $anggota->count() > 1 ? $anggota->count().' siswa' : ($this->dispensasi->siswa->nama ?? 'siswa');

        // Pembatalan otomatis (lewat batas waktu tanpa keputusan) BUKAN
        // keputusan Waka -- jangan ditulis "ditolak oleh Waka".
        $otomatis = ! $disetujui && str_starts_with((string) $this->dispensasi->catatan_waka, 'Otomatis dibatalkan');

        return [
            'icon' => $disetujui ? 'check_circle' : 'cancel',
            'title' => $disetujui ? "$jenis disetujui" : ($otomatis ? "$jenis dibatalkan otomatis" : "$jenis ditolak"),
            'body' => "$jenis $siapa ".match (true) {
                $disetujui => 'disetujui oleh Waka.',
                $otomatis => 'dibatalkan sistem karena melewati batas waktu tanpa keputusan Waka.',
                default => 'ditolak oleh Waka.',
            },
            'url' => route('dispensasi.index', ['lihat' => $this->dispensasi->id]),
        ];
    }
}
