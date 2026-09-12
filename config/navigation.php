<?php

/*
 * Item navigasi (bottom-nav mobile / side-nav) untuk role NON-admin.
 * Admin punya sidebar sendiri di x-layouts.admin.
 * Item disembunyikan otomatis jika route-nya tidak ada.
 */

return [

    // Guru yang HARI INI bukan giliran piket — menu Piket/Dispensasi disembunyikan
    // biar nav-nya nggak rancu pas lagi murni ngajar. Guru yang piketHariIni()
    // pakai 'guru-piket' di bawah (lihat User::piketHariIni() vs isPiket() —
    // akses fitur dispensasi/piket tetap kebuka kapan saja, ini cuma soal nav).
    'guru' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'menu_book', 'route' => 'jurnal.index', 'match' => 'jurnal.*'],
        ['label' => 'Jadwal', 'icon' => 'calendar_month', 'route' => 'guru.jadwal.index'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    // Hari giliran piket: nav-nya cuma yang berhubungan sama piket. Jurnal/Jadwal
    // sengaja TIDAK ikut di sini -- hari itu dia ditugaskan piket, bukan ngajar
    // (guru piket memang nggak dijadwalkan mengajar saat shift piketnya).
    'guru-piket' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
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
        ['label' => 'Rekap', 'icon' => 'bar_chart', 'route' => 'rekap.siswa.index', 'match' => 'rekap.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    'satpam' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'satpam.dashboard'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    // Admin cuma lewat sini kalau ngintip halaman oversight (dispensasi/monitor/rekap)
    // yang layout-nya mobile, bukan layout admin. Sekadar jalan balik, bukan menu utama.
    'admin' => [
        ['label' => 'Beranda Admin', 'icon' => 'home', 'route' => 'admin.dashboard'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    // fallback
    'default' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'dashboard'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

];
