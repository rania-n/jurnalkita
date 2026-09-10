<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->string('jurusan')->nullable();          // kode: RPL, TKJ, ...
            $table->unsignedTinyInteger('nomor')->nullable();
            $table->foreignId('wali_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
