<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SiswaDispensasiDiKelasAnda extends Notification
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
        return [
            'title' => 'Siswa dispensasi di jam Anda mengajar',
            'body' => $this->dispensasi->siswa->nama.' ('.($this->dispensasi->siswa->kelas?->nama ?? '—').') dispensasi '
                .$this->dispensasi->labelTanggal().' · '.$this->dispensasi->labelJam(),
            'url' => route('dispensasi.index', ['lihat' => $this->dispensasi->id]),
        ];
    }
}
