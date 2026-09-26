<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Status PKL kelas (kolom "status" di tabel kelas) itu buat kelas yang
    // SEMUA siswanya PKL (mis. kelas XII). Kenyataannya ada kelas yang PKL-nya
    // cuma SEBAGIAN siswa (mis. kelas XI) -- makanya butuh penanda per siswa
    // sendiri, terpisah dari "status" siswa (aktif/lulus/pindah) yang artinya
    // beda (status pendaftaran, bukan lagi PKL atau enggak -- siswa aktif bisa
    // aja lagi PKL).
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->boolean('pkl')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('pkl');
        });
    }
};
