<?php

/*
 * Item navigasi bawah (mobile) / sidebar (desktop).
 *
 * MVP: satu set menu "default" karena role belum dipisah (lihat docs/scope.md).
 * Nanti saat ada auth, tinggal pilih set per role berdasarkan user yang login.
 *
 * - icon  : nama ikon Material Symbols (https://fonts.google.com/icons)
 * - route : nama route Laravel. Kalau route belum ada, item disembunyikan.
 * - match : pola routeIs() untuk menandai item aktif (opsional, default = route).
 */

return [

    'default' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'beranda'],
        ['label' => 'Jurnal', 'icon' => 'edit_note', 'route' => 'jurnal.create', 'match' => 'jurnal.*'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Data', 'icon' => 'database', 'route' => 'master.index', 'match' => 'master.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

];
