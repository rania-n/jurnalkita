<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispensasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'siswa_id', 'diajukan_oleh_id', 'tanggal', 'jam_ke_mulai', 'jam_ke_selesai',
        'alasan', 'surat_path', 'no_hp',
        'status_piket', 'piket_id', 'catatan_piket',
        'status_waka', 'waka_id', 'catatan_waka',
        'status_akhir',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh_id');
    }

    public function piket(): BelongsTo
    {
        return $this->belongsTo(User::class, 'piket_id');
    }

    public function waka(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waka_id');
    }

    /** Hitung ulang status akhir dari kedua tahap persetujuan. */
    public function segarkanStatusAkhir(): void
    {
        $this->status_akhir = match (true) {
            $this->status_piket === 'rejected' || $this->status_waka === 'rejected' => 'rejected',
            $this->status_piket === 'approved' && $this->status_waka === 'approved' => 'approved',
            default => 'pending',
        };
        $this->save();
    }
}
