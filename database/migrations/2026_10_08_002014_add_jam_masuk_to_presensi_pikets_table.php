<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi_pikets', function (Blueprint $table) {
            // Khusus status terlambat: JP ke berapa siswa mulai masuk kelas.
            // JP sebelum angka ini → izin_terlambat, JP mulai angka ini → hadir.
            // Null untuk status sakit/izin biasa (tidak relevan).
            $table->unsignedTinyInteger('jam_masuk')->nullable()->after('status');

            // Untuk sakit dengan surat dokter, izin bisa berlaku beberapa hari.
            // Null = hanya berlaku 1 hari (tanggal saja, untuk izin biasa).
            $table->date('tanggal_selesai')->nullable()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('presensi_pikets', function (Blueprint $table) {
            $table->dropColumn(['jam_masuk', 'tanggal_selesai']);
        });
    }
};
