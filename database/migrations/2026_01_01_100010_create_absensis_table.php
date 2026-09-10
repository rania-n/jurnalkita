<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnals')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'])->default('hadir');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['jurnal_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
