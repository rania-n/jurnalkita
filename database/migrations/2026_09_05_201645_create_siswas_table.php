<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->integer('userid')->nullable();
            $table->string('nis');
            $table->string('nisn')->nullable();
            $table->string('namasiswa');
            
            // Relasi ke string id tabel kelas
            $table->string('kelasid');
            $table->foreign('kelasid')->references('id')->on('kelas')->onDelete('cascade');

            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->enum('jabatankelas', ['penguruskelas', 'anggota'])->default('anggota');
            $table->timestamp('createdat')->useCurrent();
            $table->timestamp('updatedat')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deletedat')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};