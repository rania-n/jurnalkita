<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('jam_ke');
            $table->time('mulai');
            $table->time('selesai');
            // String bebas (bukan enum lagi) -- admin boleh bikin kategori baru sendiri
            // (mis. "Ramadhan", "Ujian"), bukan cuma 3 bawaan (senin_kamis/jumat/khusus).
            $table->string('kategori')->default('senin_kamis');
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_pelajarans');
    }
};
