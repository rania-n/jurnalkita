<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi_pikets', function (Blueprint $table) {
            $table->unsignedTinyInteger('jam_ke_mulai')->nullable()->after('jam_masuk');
            $table->unsignedTinyInteger('jam_ke_selesai')->nullable()->after('jam_ke_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('presensi_pikets', function (Blueprint $table) {
            $table->dropColumn(['jam_ke_mulai', 'jam_ke_selesai']);
        });
    }
};
