<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('namakelas');
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->string('nikwalikelas')->nullable();
            $table->integer('jumlahsiswa')->default(0);
            $table->timestamp('createdat')->useCurrent();
            $table->timestamp('updatedat')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deletedat')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};