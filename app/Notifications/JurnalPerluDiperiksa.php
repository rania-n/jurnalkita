<?php

namespace App\Notifications;

use App\Models\Jurnal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JurnalPerluDiperiksa extends Notification
{
    use Queueable;

    public function __construct(private Jurnal $jurnal, private bool $hasilRevisi = false) {}

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
            'title' => $this->hasilRevisi ? 'Jurnal hasil revisi perlu diperiksa' : 'Jurnal baru perlu diperiksa',
            'body' => ($this->jurnal->jadwal->mapel->nama ?? 'Jurnal').' — '.($this->jurnal->guru->nama ?? ''),
            'url' => route('sekretaris.jurnal.index', ['lihat' => $this->jurnal->id]),
        ];
    }
}
