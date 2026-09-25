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

    protected $fillable = ['nama', 'tingkat', 'jurusan', 'nomor', 'wali_id', 'tahun_ajaran_id', 'status'];

    public function jurusanNama(): string
    {
        return config("akademik.jurusan.{$this->jurusan}", $this->jurusan ?? '—');
    }

    /** Kelas PKL (biasanya XII, praktik kerja lapangan) -- nggak perlu diminta isi jurnal. */
    public function pkl(): bool
    {
        return $this->status === 'pkl';
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

    /** Urutan kelas yang konsisten: tingkat, jurusan, lalu nomor secara numerik. */
    public function scopeOrderedByHierarchy(Builder $query): Builder
    {
        $urutanJurusan = array_keys(config('akademik.jurusan', []));
        $caseJurusan = 'CASE jurusan '.collect($urutanJurusan)
            ->map(fn ($jurusan, $urutan) => "WHEN ? THEN {$urutan}")
            ->implode(' ')
            .' ELSE '.count($urutanJurusan).' END';

        return $query
            ->orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderByRaw($caseJurusan, $urutanJurusan)
            ->orderBy('nomor')
            ->orderBy('nama');
    }
}
