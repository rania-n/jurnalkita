<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatatanTerlambat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['siswa_id', 'tanggal', 'jam_datang', 'catatan', 'dicatat_oleh_id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_datang' => 'datetime:H:i',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_id');
    }
}
