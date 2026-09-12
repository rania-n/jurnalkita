<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan lokasi sekolah + toleransi telat -- dipakai buat geolokasi &
     * deteksi telat di jurnal. Cuma 1 baris (singleton), diisi admin.
     * Kalau lat/lng masih kosong, fitur geolokasi jalan tanpa validasi jarak
     * (dicatat doang, belum bisa dinilai "di sekolah atau tidak").
     */
    public function up(): void
    {
        Schema::create('pengaturan_kehadirans', function (Blueprint $table) {
            $table->id();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->unsignedInteger('radius_meter')->default(200);
            $table->unsignedInteger('toleransi_telat_menit')->default(15);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_kehadirans');
    }
};
