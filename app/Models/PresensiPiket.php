<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiPiket extends Model
{
    protected $fillable = ['siswa_id', 'tanggal', 'status', 'catatan', 'surat_path', 'dicatat_oleh_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_id');
    }
}
