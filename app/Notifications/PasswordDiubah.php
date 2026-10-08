<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasswordDiubah extends Notification
{
    use Queueable;

    public function __construct(private User $userYangDiubah, private ?User $diubahOleh = null) {}

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
        $oleh = $this->diubahOleh && $this->diubahOleh->id !== $this->userYangDiubah->id
            ? " oleh Admin ({$this->diubahOleh->name})"
            : ' secara mandiri';

        return [
            'icon' => 'password',
            'title' => 'Password Akun Diubah',
            'body' => "Password untuk {$this->userYangDiubah->name} ({$this->userYangDiubah->role}) telah diubah{$oleh}.",
            'url' => route('master.akun.index', ['cari' => $this->userYangDiubah->email]),
        ];
    }
}
