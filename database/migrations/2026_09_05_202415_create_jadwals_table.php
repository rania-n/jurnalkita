<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel kelas (string)
            $table->string('kelasid');
            $table->foreign('kelasid')->references('id')->on('kelas')->onDelete('cascade');

            // Relasi aman menggunakan foreignId ke tabel mapel (id tipenya unsignedBigInteger)
            $table->foreignId('mapelid')->constrained('mapel')->onDelete('cascade');

            $table->integer('guruutamaid');
            $table->integer('gurupendampingid')->nullable();
            $table->string('ruang');
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat']);
            $table->integer('jamkemulai');
            $table->integer('jamkeselesai');
            $table->timestamp('createdat')->useCurrent();
            $table->timestamp('updatedat')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deletedat')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};