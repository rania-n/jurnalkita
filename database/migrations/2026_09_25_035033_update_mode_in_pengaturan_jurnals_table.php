<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengaturan_jurnals', function (Blueprint $table) {
            $table->string('mode', 50)->default('disiplin')->change();
        });

        DB::table('pengaturan_jurnals')
            ->where('mode', 'bebas_kemarin')
            ->update(['mode' => 'bebas_selamanya']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('pengaturan_jurnals')
            ->where('mode', 'bebas_selamanya')
            ->update(['mode' => 'bebas_kemarin']);

        Schema::table('pengaturan_jurnals', function (Blueprint $table) {
            $table->enum('mode', ['disiplin', 'bebas_hari_ini', 'bebas_kemarin'])->default('disiplin')->change();
        });
    }
};
