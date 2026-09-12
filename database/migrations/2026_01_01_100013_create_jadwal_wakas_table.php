<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadwal shift Waka Kesiswaan -- bukan cuma guru piket yang gantian, Waka juga
     * gantian per hari. Dipakai buat nentuin Waka mana yang dikirimi link WA
     * persetujuan dispensasi (yang bertugas hari itu, bukan asal Waka pertama).
     */
    public function up(): void
    {
        Schema::create('jadwal_wakas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_wakas');
    }
};
