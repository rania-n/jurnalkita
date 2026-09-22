<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class JadwalPiket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['guru_id', 'hari', 'tanggal', 'mulai', 'selesai', 'keterangan'];

    /** @var array<int, string> urutan hari Senin-Jumat, dipakai buat konversi tanggal <-> hari. */
    public const HARI_URUT = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'mulai' => 'datetime:H:i',
            'selesai' => 'datetime:H:i',
        ];
    }

    /** withTrashed() -- jadwal piket lama bisa tetap nyantol ke guru yang belakangan dihapus admin. */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class)->withTrashed();
    }

    /**
     * true kalau baris piket ini berlaku pas tanggal tsb -- baris dengan
     * tanggal spesifik cuma cocok di HARI ITU JUGA (nggak berulang), baris
     * lama (tanggal kosong) tetap dianggap berulang tiap minggu di hari yang
     * sama (mode lama, sebelum ada generator per-tanggal).
     */
    public function berlakuPada(Carbon $tanggal): bool
    {
        if ($this->tanggal) {
            return $this->tanggal->isSameDay($tanggal);
        }

        $hariTanggal = self::HARI_URUT[$tanggal->dayOfWeek - 1] ?? null;

        return $hariTanggal === $this->hari;
    }

    /** Scope: baris yang berlaku pas tanggal tsb (lihat berlakuPada()) -- versi query, buat filter di DB. */
    public function scopeBerlakuPada(Builder $query, Carbon $tanggal): Builder
    {
        $hariTanggal = self::HARI_URUT[$tanggal->dayOfWeek - 1] ?? null;

        return $query->where(
            fn ($q) => $q->whereDate('tanggal', $tanggal)
                ->orWhere(fn ($q2) => $q2->whereNull('tanggal')->when($hariTanggal, fn ($q3) => $q3->where('hari', $hariTanggal), fn ($q3) => $q3->whereRaw('1 = 0')))
        );
    }
}
