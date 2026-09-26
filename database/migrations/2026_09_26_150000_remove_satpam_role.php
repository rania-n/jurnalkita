<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Peran satpam dihapus dari aplikasi -- scan QR dispensasi tetap jalan tanpa
 * login sama sekali (lihat route('satpam.scan')), jadi tidak butuh role apa
 * pun. Fitur "Catat Siswa Terlambat" (yang cuma bisa dilakukan satpam) juga
 * dihilangkan; catatan lama di catatan_terlambats TETAP disimpan (badge
 * "Terlambat" di Rekap/detail siswa masih jalan dari data lama).
 *
 * Sengaja BUKAN migration yang ngubah definisi enum users.role (ALTER TABLE
 * MODIFY COLUMN ... ENUM(...) itu ribet & rawan beda perilaku antar driver
 * DB -- MySQL di dev vs SQLite di test, lihat catatan yang sama di migration
 * remove_tugas_status_from_jurnals_table) -- cukup hapus akun yang sudah ada
 * & validasi di level aplikasi yang nolak 'satpam' lagi (lihat
 * AkunController::save()/update()). Kolomnya sendiri di DB masih SECARA
 * TEKNIS bisa nampung 'satpam', cuma udah nggak pernah dipakai lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'satpam')->delete();
    }

    public function down(): void
    {
        // Nggak dibalik -- akun satpam yang terhapus cuma data demo/seed,
        // bukan sesuatu yang perlu dipulihkan otomatis.
    }
};
