<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal_pikets', function (Blueprint $table) {
            // Piket asli ternyata bukan "tiap Senin selamanya" -- gilirannya
            // per TANGGAL SPESIFIK, ulang tiap 2 minggu. Nullable -- data lama
            // (hari doang, tanpa tanggal) tetap jalan sebagai "berulang tiap
            // minggu" (mode lama), baris BARU ke depannya diisi tanggal
            // spesifik lewat generator (lihat JadwalPiketController::save()).
            $table->date('tanggal')->nullable()->after('guru_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pikets', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }
};
