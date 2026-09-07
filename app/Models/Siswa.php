<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $guarded = [];

    // Relasi: Siswa milik satu Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelasid', 'id');
    }
}
