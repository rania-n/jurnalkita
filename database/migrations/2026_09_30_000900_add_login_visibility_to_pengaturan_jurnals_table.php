<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengaturan_jurnals', function (Blueprint $table) {
            $table->boolean('tampilkan_di_login')->default(false)->after('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_jurnals', function (Blueprint $table) {
            $table->dropColumn('tampilkan_di_login');
        });
    }
};
