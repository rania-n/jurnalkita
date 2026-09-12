<?php

/*
 * Item navigasi (bottom-nav mobile / side-nav) untuk role NON-admin.
 * Admin punya sidebar sendiri di x-layouts.admin.
 * Item disembunyikan otomatis jika route-nya tidak ada.
 */

return [

    // Guru yang TIDAK pernah kebagian piket — menu Piket/Dispensasi disembunyikan
    // total, bukan cuma kosong, biar nggak nyasar ke halaman yang buat mereka
    // permanen kosong. Guru yang isPiket() pakai 'guru-piket' di bawah.
    'guru' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'menu_book', 'route' => 'jurnal.index', 'match' => 'jurnal.*'],
        ['label' => 'Jadwal', 'icon' => 'calendar_month', 'route' => 'guru.jadwal.index'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'guru-piket' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'menu_book', 'route' => 'jurnal.index', 'match' => 'jurnal.*'],
        ['label' => 'Jadwal', 'icon' => 'calendar_month', 'route' => 'guru.jadwal.index'],
        ['label' => 'Piket', 'icon' => 'event_available', 'route' => 'piket.index'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'sekretaris' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'sekretaris.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'menu_book', 'route' => 'sekretaris.jurnal.index', 'match' => 'sekretaris.jurnal.*'],
        ['label' => 'Kelas', 'icon' => 'school', 'route' => 'sekretaris.kelas.siswa'],
        ['label' => 'Jadwal', 'icon' => 'calendar_month', 'route' => 'sekretaris.kelas.jadwal'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'waka' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'waka.dashboard'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Monitor', 'icon' => 'monitoring', 'route' => 'piket.monitor.index', 'match' => 'piket.monitor.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    // fallback
    'default' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'dashboard'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

];
