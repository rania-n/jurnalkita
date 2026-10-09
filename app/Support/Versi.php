<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

/**
 * "Tanda tangan" ringan buat satu query -- dipakai endpoint polling
 * (initAutoRefresh() di app.js) buat ketauan ADA PERUBAHAN atau nggak,
 * TANPA harus balikin/bandingin isi datanya sendiri.
 *
 * Fingerprint-nya gabungan jumlah baris + nilai terbesar dari beberapa kolom
 * waktu, biar perubahan di statuses apa pun (insert, update, delete, soft
 * delete) selalu bikin hasilnya beda -- nggak perlu 100% presisi, cukup peka
 * biar user tau "ada yang berubah, coba muat ulang" tanpa disuruh nebak sendiri.
 *
 * Kenapa kolom waktu-nya nggak hardcode 'updated_at'? Karena nggak semua tabel
 * punya kolom itu. audit_logs, misalnya, cuma punya 'created_at' (log itu
 * append-only, model-nya $timestamps = false) -- Max('updated_at') ke tabel
 * seperti itu bikin ERROR 500 Unknown column, yang efeknya endpoint-nya diam
 * diam gagal terus dan banner "ada data baru" nggak pernah muncul sama sekali.
 * Karena itu kolom dicek dulu ke skema tabel, dan yang tak ada di-lewati.
 */
class Versi
{
    /**
     * Kandidat kolom waktu, dari yang paling kuat menandakan perubahan.
     * deleted_at cuma kepakai kalau modelnya soft delete (pakai SoftDeletes).
     *
     * @var array<int, string>
     */
    private const KOLOM_WAKTU = ['updated_at', 'created_at', 'deleted_at'];

    /** @var array<string, array<int, string>> cache kolom waktu per tabel (cek skema itu mahal) */
    private static array $kolomTersedia = [];

    public static function dari(Builder|Relation $query): string
    {
        $bagian = [$query->count()];

        foreach (self::kolomWaktu($query) as $kolom) {
            $bagian[] = $kolom.'='.($query->max($kolom) ?? '-');
        }

        return implode(':', $bagian);
    }

    /**
     * Kolom waktu yang benar-benar ada di tabel query ini, sesuai urutan
     * prioritas KOLOM_WAKTU.
     *
     * @return array<int, string>
     */
    private static function kolomWaktu(Builder|Relation $query): array
    {
        $builder = $query instanceof Relation ? $query->getQuery() : $query;
        $model = $builder->getModel();
        $tabel = $model->getTable();

        return self::$kolomTersedia[$tabel] ??= array_values(array_filter(
            self::KOLOM_WAKTU,
            // deleted_at cuma relevan kalau tabelnya memang bisa di-soft-delete.
            fn (string $kolom) => Schema::hasColumn($tabel, $kolom)
                && ($kolom !== 'deleted_at' || in_array(SoftDeletes::class, class_uses_recursive($model), true))
        ));
    }
}
