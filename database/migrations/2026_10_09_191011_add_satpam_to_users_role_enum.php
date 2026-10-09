<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Peran satpam dihidupkan lagi, tetapi enum users.role di database MySQL
 * sudah tidak memuat 'satpam' sehingga pembuatan akun satpam gagal
 * ("Data truncated for column 'role'"). Hanya dijalankan di MySQL; SQLite
 * (test) sudah memakai definisi awal yang memuat 'satpam'.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','guru','siswa','waka','satpam') NOT NULL DEFAULT 'guru'");
        }
    }

    public function down(): void
    {
        // Tidak dibalik agar akun satpam yang sudah ada tidak rusak.
    }
};
