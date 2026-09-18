<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = ['jurnal_id', 'siswa_id', 'status', 'catatan'];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    /**
     * withTrashed() -- presensi itu CATATAN SEJARAH, harus tetap kebaca utuh
     * walau siswanya belakangan di-soft-delete (pindah/keluar/data diganti
     * data asli, dsb). Tanpa ini, jurnal lama yang presensinya nyantol ke
     * siswa yang udah dihapus bakal error null pas ditampilin.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }
}
