<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class HariKhusus extends Model
{
    public const JENIS = [
        'pulang_cepat' => 'Pulang Cepat',
        'tanpa_kbm' => 'Tanpa KBM & Piket',
    ];

    protected $fillable = ['tanggal', 'nama', 'jenis', 'jam_selesai'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_selesai' => 'datetime:H:i',
        ];
    }

    public static function untukTanggal(Carbon|string|null $tanggal = null): ?self
    {
        return static::query()->whereDate('tanggal', $tanggal ?? today())->first();
    }

    public function scopeTanggal(Builder $query, Carbon|string $tanggal): Builder
    {
        return $query->whereDate('tanggal', $tanggal);
    }
}
