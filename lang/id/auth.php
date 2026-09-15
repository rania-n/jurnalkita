<?php

/*
 * Terjemahan pesan autentikasi Laravel -- lihat catatan yang sama di
 * lang/id/validation.php (APP_LOCALE=id, tapi Laravel nggak nyediain
 * lang/id/... bawaan, jadi tanpa ini pesan balik ke Inggris).
 */

return [

    // Sengaja nggak nyebut spesifik "email salah" atau "password salah" --
    // biar orang lain nggak bisa nebak akun mana yang beneran terdaftar
    // cuma dari pesan errornya (praktik keamanan standar Laravel).
    'failed' => 'Email atau kata sandi salah.',
    'password' => 'Kata sandi yang Anda masukkan salah.',
    'throttle' => 'Terlalu banyak percobaan masuk. Coba lagi dalam :seconds detik.',

];
