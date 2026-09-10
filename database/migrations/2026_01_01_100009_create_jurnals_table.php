<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwals')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedTinyInteger('jam_ke_mulai');
            $table->unsignedTinyInteger('jam_ke_selesai');
            $table->enum('status_guru', ['hadir', 'tugas', 'tidak_hadir'])->default('hadir');
            $table->text('materi');
            $table->string('metode')->nullable();
            $table->text('tugas_tambahan')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->boolean('diisi_oleh_pengurus')->default(false);
            $table->enum('status_verifikasi', ['pending', 'terverifikasi', 'revisi'])->default('pending');
            $table->foreignId('verifikator_id')->nullable()->constrained('siswas')->nullOnDelete();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
