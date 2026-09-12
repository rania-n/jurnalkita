<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanKehadiran extends Model
{
    protected $table = 'pengaturan_kehadirans';

    protected $fillable = ['lat', 'lng', 'radius_meter', 'toleransi_telat_menit'];

    /** Singleton -- cuma 1 baris pengaturan buat seluruh sekolah. */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'radius_meter' => 200,
            'toleransi_telat_menit' => 15,
        ]);
    }

    public function lokasiSudahDiatur(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }
}
