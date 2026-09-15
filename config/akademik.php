<?php

return [

    'tingkat' => ['X', 'XI', 'XII'],

    // kode => nama lengkap
    'jurusan' => [
        'RPL' => 'Rekayasa Perangkat Lunak',
        'TKJ' => 'Teknik Komputer dan Jaringan',
        'AN' => 'Animasi',
        'BD' => 'Bisnis Digital',
        'MP' => 'Manajemen Perkantoran',
        'TKI' => 'Teknik Kimia Industri',
        'ULW' => 'Usaha Layanan Wisata',
        'AK' => 'Akuntansi',
        'DKV' => 'Desain Komunikasi Visual',
        'PSPT' => 'Produksi dan Siaran Program Televisi',
    ],

    // Daftar ruangan untuk jadwal pelajaran. R1-R20/Lab RPL 1 dst di baris pertama
    // adalah ruangan demo lama (dipertahankan, dipakai fixture/test lain). Baris-baris
    // setelahnya adalah ruangan ASLI dari PDF jadwal SMKN 1 Boyolangu (format "R 23"
    // pakai spasi -- memang beda penulisan dari yang demo, itu sesuai PDF aslinya).
    'ruangan' => [
        'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7', 'R8', 'R9', 'R10',
        'R11', 'R12', 'R13', 'R14', 'R15', 'R16', 'R17', 'R18', 'R19', 'R20',
        'Lab RPL 1', 'Lab RPL 2', 'Lab TKJ', 'Lab DKV', 'Lab Animasi',
        'Lab Kimia', 'Aula', 'Perpustakaan',

        // -- Ruangan asli (PDF jadwal semester ganjil 2026/2027) --
        'R 1', 'R 2', 'R 3', 'R 4', 'R 5', 'R 6', 'R 7', 'R 8',
        'R 9', 'R 10', 'R 11', 'R 12', 'R 13', 'R 14', 'R 15', 'R 16',
        'R 17', 'R 18', 'R 22', 'R 23', 'R 24', 'R 25', 'R 26', 'R 31',
        'R 32', 'R 33', 'R 34', 'R 40', 'R 41', 'R 55', 'R 56', 'R 57',
        'R 58', 'R 59', 'R 60', 'R 61', 'R 62', 'R 63', 'R 64', 'R 65',
        'R 66',
        'LAP', 'Lab. AK ATAS', 'Lab. AK BAWAH',
        'Lab. AN COE 1', 'Lab. AN COE 2', 'Lab. AP ATAS',
        'Lab. AP BAWAH', 'Lab. BD Depan', 'Lab. Broadcast',
        'Lab. DKV Komputer', 'Lab. DKV Produksi', 'Lab. KI 1',
        'Lab. PM Belakang', 'Lab. PSPT Editing', 'Lab. PSPT Studio',
        'Lab. RPL 1', 'Lab. RPL 2', 'Lab. TKJ 1',
        'Lab. TKJ 2 (FO)', 'Lab. TKJ 3', 'Lab. UPW 1 (Guiding)',
        'Lab. UPW 2 (Ticketing)', 'Lapangan',
    ],

    'hari' => ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'],

];
