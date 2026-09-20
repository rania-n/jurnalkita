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

    /**
     * withTrashed() di 4 relasi bawah -- jadwal itu bisa dirujuk balik dari
     * Jurnal (riwayat) lama sesudah kelas/mapel/gurunya sendiri belakangan
     * dihapus admin. Tanpa ini, buka detail jurnal lama bisa error null.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class)->withTrashed();
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class)->withTrashed();
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class)->withTrashed();
    }

    public function guruPendamping(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_pendamping_id')->withTrashed();
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(Jurnal::class);
    }

    /**
     * Hari (senin-jumat) yang buat kelas ini JP-nya udah kepakai SEMUA --
     * nggak ada celah JP kosong sama sekali di hari itu. Dipakai admin
     * Tambah/Ubah Jadwal buat ngunci opsi hari yang beneran udah penuh, biar
     * nggak coba nambahin jadwal yang jelas-jelas bakal bentrok.
     *
     * @return array<int, string>
     */
    public static function hariPenuhUntukKelas(int $kelasId, ?int $kecualiJadwalId = null): array
    {
        $penuh = [];

        foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $hari) {
            $kategori = $hari === 'jumat' ? 'jumat' : 'senin_kamis';

            $jpTersedia = JamPelajaran::where('kategori', $kategori)->pluck('jam_ke');
            if ($jpTersedia->isEmpty()) {
                continue; // jam pelajaran kategori ini belum diatur admin -> jangan asal kunci
            }

            $jpTerpakai = collect();
            static::where('kelas_id', $kelasId)->where('hari', $hari)
                ->when($kecualiJadwalId, fn ($q) => $q->whereKeyNot($kecualiJadwalId))
                ->get(['jam_ke_mulai', 'jam_ke_selesai'])
                ->each(function ($j) use (&$jpTerpakai) {
                    for ($i = $j->jam_ke_mulai; $i <= $j->jam_ke_selesai; $i++) {
                        $jpTerpakai->push($i);
                    }
                });

            if ($jpTersedia->diff($jpTerpakai)->isEmpty()) {
                $penuh[] = $hari;
            }
        }

        return $penuh;
    }
}
