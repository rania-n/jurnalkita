<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalPiket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['guru_id', 'hari', 'mulai', 'selesai', 'keterangan'];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime:H:i',
            'selesai' => 'datetime:H:i',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
