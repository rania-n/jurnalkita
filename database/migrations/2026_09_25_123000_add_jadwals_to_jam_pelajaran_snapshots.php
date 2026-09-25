<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jam_pelajaran_snapshots', function (Blueprint $table) {
            $table->json('jadwals')->nullable()->after('baris');
        });
    }

    public function down(): void
    {
        Schema::table('jam_pelajaran_snapshots', function (Blueprint $table) {
            $table->dropColumn('jadwals');
        });
    }
};
