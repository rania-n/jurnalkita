<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kelas_id', 'mapel_id', 'guru_id', 'guru_pendamping_id',
        'ruang', 'hari', 'jam_ke_mulai', 'jam_ke_selesai',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function guruPendamping(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_pendamping_id');
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(Jurnal::class);
    }
}
