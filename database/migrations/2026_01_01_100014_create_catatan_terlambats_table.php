<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Buku piket ketertiban" -- siswa telat masuk gerbang pagi. Sengaja tabel
     * TERPISAH dari absensis: ini soal telat masuk SEKOLAH (dicatat satpam di
     * gerbang), bukan telat/tidak hadir di jam pelajaran tertentu (yang sudah
     * dicover absensis lewat jurnal).
     */
    public function up(): void
    {
        Schema::create('catatan_terlambats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_datang');
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_terlambats');
    }
};
