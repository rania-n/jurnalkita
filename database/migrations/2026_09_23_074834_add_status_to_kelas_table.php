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
        Schema::table('kelas', function (Blueprint $table) {
            // 'pkl' -- kelas XII biasanya PKL (praktik kerja lapangan) sebagian
            // semester, nggak perlu diminta isi jurnal kayak kelas biasa.
            // Sengaja string (bukan enum DB) biar gampang nambah status baru
            // nanti, sama pola kayak siswas.status.
            $table->string('status')->default('aktif')->after('nomor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
