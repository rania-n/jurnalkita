<?php

/*
 * Item navigasi bawah (mobile) / sidebar (desktop), per menu.
 * Item disembunyikan otomatis jika route-nya tidak ada / user tidak berhak
 * (komponen nav memakai Route::has() + cek middleware saat render).
 *
 * TODO(modul FE per role): pilih menu berdasarkan auth()->user()->role di layout.
 */

return [

    'default' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'edit_note', 'route' => 'jurnal.create', 'match' => 'jurnal.*'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Master', 'icon' => 'database', 'route' => 'master.index', 'match' => 'master.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'admin' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'admin.dashboard'],
        ['label' => 'Master Data', 'icon' => 'database', 'route' => 'master.index', 'match' => 'master.*'],
        ['label' => 'Piket', 'icon' => 'event_available', 'route' => 'piket.jadwal.index', 'match' => 'piket.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'guru' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'edit_note', 'route' => 'jurnal.create', 'match' => 'jurnal.*'],
        ['label' => 'Piket', 'icon' => 'event_available', 'route' => 'piket.jadwal.index', 'match' => 'piket.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'sekretaris' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'sekretaris.dashboard'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'waka' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'waka.dashboard'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

];
