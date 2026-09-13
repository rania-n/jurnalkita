<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DispensasiDiputuskan extends Notification
{
    use Queueable;

    public function __construct(private Dispensasi $dispensasi)
    {
    }

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

        return [
            'title' => $disetujui ? 'Dispensasi disetujui' : 'Dispensasi ditolak',
            'body' => 'Dispensasi '.($this->dispensasi->siswa->nama ?? 'siswa')
                .' '.($disetujui ? 'disetujui' : 'ditolak').' oleh Waka Kesiswaan.',
            'url' => route('dispensasi.show', $this->dispensasi),
        ];
    }
}
