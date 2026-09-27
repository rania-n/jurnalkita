<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom khusus akun TESTING -- biar bisa dicek "Piket Hari Ini" kapan
     * saja (termasuk Sabtu/Minggu) TANPA mengubah aturan hari sekolah asli
     * (HariSekolah::hariIni() tetap Senin-Jumat, itu benar buat sekolah
     * sungguhan). Default false buat semua guru asli -- nggak ada efek sama
     * sekali kecuali kolom ini sengaja diaktifkan.
     */
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->boolean('piket_selalu_aktif')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn('piket_selalu_aktif');
        });
    }
};
