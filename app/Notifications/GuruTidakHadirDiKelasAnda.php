<?php

namespace App\Notifications;

use App\Models\Jurnal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app buat pengurus kelas & wali kelas: guru mapel tidak masuk
 * di kelas mereka. Dikirim bareng notifikasi Waka (GuruTidakHadir) tiap kali
 * guru menandai dirinya tidak hadir pada satu jurnal.
 */
class GuruTidakHadirDiKelasAnda extends Notification
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
        $kelas = $this->jurnal->jadwal->kelas;
        $mapel = $this->jurnal->jadwal->mapel->nama ?? 'pelajaran';

        // Pengurus kelas -> daftar jurnal kelasnya; wali kelas -> halaman jurnal
        // kelas yang diampu; selain itu beranda masing-masing.
        if (($notifiable->role ?? null) === 'siswa') {
            $url = route('sekretaris.jurnal.index', ['lihat' => $this->jurnal->id]);
        } elseif (($notifiable->role ?? null) === 'guru' && $notifiable->isWali() && $kelas) {
            $url = route('guru.wali-kelas.jurnal', $kelas->id);
        } else {
            $url = route($notifiable->homeRoute());
        }

        return [
            'icon' => 'person_off',
            'title' => 'Guru tidak hadir di kelas Anda',
            'body' => ($this->jurnal->guru->nama ?? 'Guru').' tidak masuk '.$mapel
                .' — '.($kelas->nama ?? '—').' ('.($this->jurnal->alasan ?? 'tanpa alasan').')',
            'url' => $url,
        ];
    }
}
