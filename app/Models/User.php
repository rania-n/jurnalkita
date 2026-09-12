<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role', 'status', 'no_hp'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /** Guru yang menjabat sebagai staff piket (punya jadwal piket) — permanen, dipakai buat akses fitur. */
    public function isPiket(): bool
    {
        return $this->role === 'guru'
            && $this->guru?->jadwalPikets()->exists();
    }

    /**
     * Guru piket yang KEBAGIAN JADWAL HARI INI (bukan cuma "pernah dapat piket") —
     * dipakai buat nav, biar menu Piket/Dispensasi cuma nongol di hari dia
     * beneran bertugas. Beda dari isPiket() yang tetap dipakai buat akses fitur
     * (boleh ajukan/lihat dispensasi kapan saja, bukan cuma pas hari piketnya).
     */
    public function piketHariIni(): bool
    {
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? null;

        return $hari && $this->isPiket() && $this->guru->jadwalPikets()->where('hari', $hari)->exists();
    }

    /** Siswa pengurus kelas (akun kelas / "sekretaris"). */
    public function isSekretaris(): bool
    {
        return $this->role === 'siswa' && $this->siswa?->jabatan === 'pengurus';
    }

    /** Kelas yang diampu pengurus kelas ini. */
    public function kelasSekretaris(): ?Kelas
    {
        return $this->siswa?->kelas;
    }

    /** Nama folder/route dashboard sesuai peran efektif. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'waka' => 'waka.dashboard',
            'siswa' => 'sekretaris.dashboard',
            'satpam' => 'satpam.dashboard',
            default => 'guru.dashboard',
        };
    }
}
