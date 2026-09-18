<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Status "Tugas Luar" dihapus dari aplikasi -- dianggap sama aja kayak
 * "Tidak Hadir" (nggak ada bedanya secara alur kerja, cuma nambah pilihan
 * yang nggak perlu). Jurnal LAMA yang statusnya masih 'tugas' dipindah ke
 * 'tidak_hadir' di sini.
 *
 * Sengaja BUKAN migration yang ngubah definisi enum kolomnya (ALTER TABLE
 * MODIFY COLUMN ... ENUM(...) itu ribet & rawan beda perilaku antar driver
 * DB -- MySQL di dev vs SQLite di test) -- cukup validasi di level aplikasi
 * yang nolak 'tugas' lagi (lihat JurnalController). Kolomnya sendiri di DB
 * masih SECARA TEKNIS bisa nampung 'tugas', cuma udah nggak pernah dipakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('jurnals')->where('status_guru', 'tugas')->update(['status_guru' => 'tidak_hadir']);
    }

    public function down(): void
    {
        // Nggak bisa dibalik -- data mana yang tadinya 'tugas' vs beneran
        // 'tidak_hadir' udah nggak bisa dibedain lagi setelah digabung.
    }
};
