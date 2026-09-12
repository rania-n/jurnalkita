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

    // Guru yang JUGA wali kelas (kelas.wali_id) -- tambahan menu rekap kelasnya.
    // Cuma dipakai kalau bukan hari piket (piket tetap prioritas, lihat 'guru-piket').
    'guru-wali' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'guru.dashboard'],
        ['label' => 'Jurnal', 'icon' => 'menu_book', 'route' => 'jurnal.index', 'match' => 'jurnal.*'],
        ['label' => 'Jadwal', 'icon' => 'calendar_month', 'route' => 'guru.jadwal.index'],
        ['label' => 'Wali Kelas', 'icon' => 'groups', 'route' => 'guru.wali-kelas.index', 'match' => 'guru.wali-kelas.*'],
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

    // Sumber tunggal buat sidebar admin (dipakai x-layouts.admin) SEKALIGUS buat
    // halaman oversight (dispensasi/monitor-piket/rekap-siswa, yang templatenya
    // masih x-layouts.app) -- biar admin lihat sidebar yang SAMA PERSIS di mana
    // pun dia berada, nggak berasa pindah ke "app lain".
    'admin' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'admin.dashboard'],
        ['label' => 'Manajemen Akun', 'icon' => 'manage_accounts', 'route' => 'master.akun.index'],
        ['label' => 'Data Guru', 'icon' => 'groups', 'route' => 'master.guru.index'],
        ['label' => 'Data Kelas', 'icon' => 'meeting_room', 'route' => 'master.kelas.index'],
        ['label' => 'Data Siswa', 'icon' => 'school', 'route' => 'master.siswa.index'],
        ['label' => 'Mata Pelajaran', 'icon' => 'menu_book', 'route' => 'master.mapel.index'],
        ['label' => 'Jadwal Pelajaran', 'icon' => 'calendar_month', 'route' => 'master.jadwal-pelajaran.index'],
        ['label' => 'Jam Pelajaran', 'icon' => 'schedule', 'route' => 'master.jam-pelajaran.index'],
        ['label' => 'Jadwal Piket', 'icon' => 'event_available', 'route' => 'master.jadwal-piket.index'],
        ['label' => 'Jadwal Waka', 'icon' => 'assignment_ind', 'route' => 'master.jadwal-waka.index'],
        ['label' => 'Tahun Ajaran', 'icon' => 'event_repeat', 'route' => 'master.tahun-ajaran.index'],
        ['label' => 'Audit Log', 'icon' => 'history', 'route' => 'master.audit-log.index'],
        // Oversight kesiswaan — lihat saja, aksi (approve/tolak) tetap milik piket/waka.
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Monitor Piket', 'icon' => 'monitoring', 'route' => 'piket.monitor.index', 'match' => 'piket.monitor.*'],
        ['label' => 'Rekap Siswa', 'icon' => 'bar_chart', 'route' => 'rekap.siswa.index', 'match' => 'rekap.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    // Bottom-nav mobile admin -- ringkas (admin desktop-first, sidebar lengkap cuma
    // muncul lg:block). 4 pintasan paling sering dipakai; sisanya lewat hamburger
    // sidebar (juga jalan di mobile lewat tombol menu di topbar admin).
    'admin-mobile' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'admin.dashboard'],
        ['label' => 'Akun', 'icon' => 'manage_accounts', 'route' => 'master.akun.index'],
        ['label' => 'Dispensasi', 'icon' => 'fact_check', 'route' => 'dispensasi.index', 'match' => 'dispensasi.*'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    // fallback
    'default' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'dashboard'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

];
