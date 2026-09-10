<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('diajukan_oleh_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedTinyInteger('jam_ke_mulai')->nullable();
            $table->unsignedTinyInteger('jam_ke_selesai')->nullable();
            $table->text('alasan');
            $table->string('surat_path')->nullable();
            $table->string('no_hp')->nullable();

            $table->enum('status_piket', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('piket_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_piket')->nullable();

            $table->enum('status_waka', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('waka_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_waka')->nullable();

            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensasis');
    }
};
