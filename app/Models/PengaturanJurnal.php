<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton (1 baris, id=1) -- pengaturan skala sekolah buat aturan JAM isi
 * jurnal guru. Lihat migration create_pengaturan_jurnals_table buat
 * penjelasan tiap mode.
 */
class PengaturanJurnal extends Model
{
    protected $fillable = ['mode'];

    public const MODE_LABEL = [
        'disiplin' => 'Disiplin (kunci jam pelajaran)',
        'bebas_hari_ini' => 'Bebas isi hari ini',
        'bebas_selamanya' => 'Bebas selamanya (tanggal custom)',
    ];

    public function setModeAttribute(string $value): void
    {
        $this->attributes['mode'] = $value === 'bebas_kemarin' ? 'bebas_selamanya' : $value;
    }

    /** Ambil baris pengaturan (bikin dulu kalau belum ada -- default 'disiplin'). */
    public static function ambil(): self
    {
        return static::query()->firstOrCreate(['id' => 1], ['mode' => 'disiplin']);
    }

    /** Shortcut -- yang paling sering dipakai cuma nilai mode-nya. */
    public static function mode(): string
    {
        return static::ambil()->mode;
    }
}
