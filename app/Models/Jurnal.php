<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurnal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'jadwal_id', 'guru_id', 'tanggal', 'jam_ke_mulai', 'jam_ke_selesai',
        'status_guru', 'materi', 'metode', 'tugas_tambahan', 'foto_bukti',
        'diisi_oleh_pengurus', 'status_verifikasi', 'verifikator_id', 'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'diisi_oleh_pengurus' => 'boolean',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'verifikator_id');
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function isPending(): bool
    {
        return $this->status_verifikasi === 'pending';
    }

    /** Guru masih boleh mengubah jurnal selama belum terverifikasi (pending) atau saat diminta revisi. */
    public function bisaDiubah(): bool
    {
        return in_array($this->status_verifikasi, ['pending', 'revisi'], true);
    }
}
