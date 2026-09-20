<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kelas';

    protected $fillable = ['nama', 'tingkat', 'jurusan', 'nomor', 'wali_id', 'tahun_ajaran_id'];

    public function jurusanNama(): string
    {
        return config("akademik.jurusan.{$this->jurusan}", $this->jurusan ?? '—');
    }

    /** withTrashed() -- wali_id bisa nyantol ke guru yang belakangan dihapus admin. */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_id')->withTrashed();
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    /** Akun pengurus kelas ("sekretaris") kelas ini, kalau ada. 1 kelas = 1 pengurus. */
    public function pengurusUser(): ?User
    {
        return $this->siswas()->where('jabatan', 'pengurus')->first()?->user;
    }

    /** Kelas tahun ajaran yang sedang aktif (atau tanpa tahun ajaran sama sekali — data lama). */
    public function scopeAktif(Builder $query): Builder
    {
        $aktif = TahunAjaran::aktif();

        return $aktif
            ? $query->where('tahun_ajaran_id', $aktif->id)
            : $query->whereNull('tahun_ajaran_id');
    }
}
