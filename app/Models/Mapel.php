<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $table = 'mapel';
    protected $primaryKey = 'id';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $guarded = [];
}
