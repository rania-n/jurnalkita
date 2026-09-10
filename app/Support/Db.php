<?php

namespace App\Support;

/**
 * Ekspresi ORDER BY yang portabel (MySQL + SQLite) — pengganti FIELD() MySQL.
 */
class Db
{
    /** Urutan berdasarkan daftar nilai. Kolom lain yang tidak ada di daftar diletakkan terakhir. */
    public static function orderByList(string $column, array $values): string
    {
        $cases = '';
        foreach (array_values($values) as $i => $v) {
            $safe = str_replace("'", "''", $v);
            $cases .= " when '{$safe}' then {$i}";
        }

        return "case {$column}{$cases} else ".count($values).' end';
    }

    public static function hariOrder(string $column = 'hari'): string
    {
        return self::orderByList($column, ['senin', 'selasa', 'rabu', 'kamis', 'jumat']);
    }
}
