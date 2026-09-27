<?php

return [

    'tingkat' => ['X', 'XI', 'XII'],

    // kode => nama lengkap
    'jurusan' => [
        'TKI' => 'Teknik Kimia Industri',
        'RPL' => 'Rekayasa Perangkat Lunak',
        'TKJ' => 'Teknik Komputer dan Jaringan',
        'BD' => 'Bisnis Digital',
        'MP' => 'Manajemen Perkantoran',
        'AK' => 'Akuntansi',
        'ULW' => 'Usaha Layanan Wisata',
        'DKV' => 'Desain Komunikasi Visual',
        'PSPT' => 'Produksi dan Siaran Program Televisi',
        'AN' => 'Animasi',
    ],

    // Daftar ruangan untuk jadwal pelajaran. R1-R20/Lab RPL 1 dst di baris pertama
    // adalah ruangan demo lama (dipertahankan, dipakai fixture/test lain). Baris-baris
    // setelahnya adalah ruangan ASLI dari PDF jadwal SMKN 1 Boyolangu (format "R 23"
    // pakai spasi -- memang beda penulisan dari yang demo, itu sesuai PDF aslinya).
    'ruangan' => [
        'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7', 'R8', 'R9', 'R10',
        'R11', 'R12', 'R13', 'R14', 'R15', 'R16', 'R17', 'R18', 'R19', 'R20',
        'R22', 'R23', 'R24', 'R25', 'R26', 'R31', 'R32', 'R33', 'R34',
        'R40', 'R41', 'R55', 'R56', 'R57', 'R58', 'R59', 'R60', 'R61',
        'R62', 'R63', 'R64', 'R65', 'R66',
        'Lab RPL 1', 'Lab RPL 2', 'Lab TKJ', 'Lab DKV', 'Lab Animasi',
        'Lab Kimia', 'Aula', 'Perpustakaan', 'Lapangan', 'LAP',
        'Lab. AK ATAS', 'Lab. AK BAWAH', 'Lab. AN COE 1', 'Lab. AN COE 2',
        'Lab. AP ATAS', 'Lab. AP BAWAH', 'Lab. BD Depan', 'Lab. Broadcast',
        'Lab. DKV Komputer', 'Lab. DKV Produksi', 'Lab. KI 1',
        'Lab. PM Belakang', 'Lab. PSPT Editing', 'Lab. PSPT Studio',
        'Lab. RPL 1', 'Lab. RPL 2', 'Lab. TKJ 1', 'Lab. TKJ 2 (FO)',
        'Lab. TKJ 3', 'Lab. UPW 1 (Guiding)', 'Lab. UPW 2 (Ticketing)',
    ],

    'hari' => ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'],

];
