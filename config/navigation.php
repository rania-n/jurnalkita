<?php

/*
 * Item navigasi (bottom-nav mobile / side-nav) untuk role NON-admin.
 * Admin punya sidebar sendiri di x-layouts.admin.
 * Item disembunyikan otomatis jika route-nya tidak ada.
 */

return [

    'guru' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'menu_book', 'route' => 'jurnal.index', 'match' => 'jurnal.*'],
        ['label' => 'Piket', 'icon' => 'event_available', 'route' => 'piket.index'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
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

    // fallback
    'default' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'dashboard'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

];
