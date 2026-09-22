<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * "Tanda tangan" ringan buat satu query -- dipakai endpoint polling
 * (initAutoRefresh() di app.js) buat ketauan ADA PERUBAHAN atau nggak,
 * TANPA harus balikin/bandingin isi datanya sendiri. Gabungan jumlah baris +
 * updated_at paling baru cukup buat nangkep insert/update/delete/soft-delete
 * -- nggak perlu 100% presisi, cukup cukup peka biar user tau "ada yang
 * berubah, coba muat ulang" tanpa disuruh nebak sendiri.
 */
class Versi
{
    public static function dari(Builder|Relation $query): string
    {
        return $query->count().':'.($query->max('updated_at') ?? '-');
    }
}
