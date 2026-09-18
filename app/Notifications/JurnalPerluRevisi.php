<?php

namespace App\Notifications;

use App\Models\Jurnal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JurnalPerluRevisi extends Notification
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
            'title' => 'Jurnal perlu direvisi',
            'body' => ($this->jurnal->jadwal->mapel->nama ?? 'Jurnal').' — '.($this->jurnal->jadwal->kelas->nama ?? '')
                .': '.($this->jurnal->catatan_verifikasi ?: 'Pengurus kelas minta perbaikan.'),
            // Langsung ke halaman Ubah (bukan sekadar lihat) -- itu jelas
            // maksudnya guru klik notif ini, jadi nggak perlu 1 klik ekstra
            // "Ubah" lagi dari halaman detail.
            'url' => route('jurnal.edit', $this->jurnal),
        ];
    }
}
