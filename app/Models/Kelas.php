<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id';
    public $incrementing = false; // Karena id pakai string (varchar)
    protected $keyType = 'string';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $guarded = [];

    // Relasi: Kelas memiliki banyak Siswa
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'kelasid', 'id');
    }
}