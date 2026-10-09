<?php

namespace App\Notifications;

use App\Models\Jurnal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GuruTidakHadir extends Notification
{
    use Queueable;

    public function __construct(private Jurnal $jurnal) {}

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
            'icon' => 'person_off',
            'title' => 'Guru Tidak Hadir',
            'body' => ($this->jurnal->guru->nama ?? 'Guru').' ('.($this->jurnal->alasan ?? 'Tidak ada alasan').') pada '.$this->jurnal->jadwal->mapel->nama.' — '.$this->jurnal->jadwal->kelas->nama,
            'url' => route('piket.monitor.index', ['lihat' => $this->jurnal->id, 'dari' => $this->jurnal->tanggal->toDateString(), 'sampai' => $this->jurnal->tanggal->toDateString()]),
        ];
    }
}
