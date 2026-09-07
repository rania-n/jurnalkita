<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    protected $primaryKey = 'id';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $guarded = [];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelasid', 'id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapelid', 'id');
    }
}