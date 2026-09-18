<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu baris aja (singleton, id=1) -- pengaturan skala sekolah, bukan per
 * guru. Admin atur seberapa longgar/ketat aturan JAM buat isi jurnal:
 *   - disiplin       : dikunci ke jam pelajaran yang beneran lagi jalan,
 *                       diblokir total pas istirahat (perilaku default/lama).
 *   - bebas_hari_ini : bebas pilih jadwal hari ini kapan aja (nggak dikunci
 *                       jam, nggak diblokir pas istirahat).
 *   - bebas_kemarin  : sama kayak bebas_hari_ini, DITAMBAH bisa isi susulan
 *                       buat jadwal KEMARIN juga (mis. buat kelas yang lagi
 *                       PKL/nggak rutin masuk tiap hari).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_jurnals', function (Blueprint $table) {
            $table->id();
            $table->enum('mode', ['disiplin', 'bebas_hari_ini', 'bebas_kemarin'])->default('disiplin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_jurnals');
    }
};
