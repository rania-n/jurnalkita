<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DispensasiBaru extends Notification
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
        return [
            'title' => 'Dispensasi baru menunggu persetujuan',
            'body' => $this->dispensasi->siswa->nama.' ('.($this->dispensasi->siswa->kelas?->nama ?? '—').') — '
                .str($this->dispensasi->alasan)->limit(60),
            'url' => route('dispensasi.show', $this->dispensasi),
        ];
    }
}
