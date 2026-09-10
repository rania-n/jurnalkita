<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JamPelajaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['jam_ke', 'mulai', 'selesai', 'kategori', 'keterangan'];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime:H:i',
            'selesai' => 'datetime:H:i',
        ];
    }
}
