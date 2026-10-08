<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi_pikets', function (Blueprint $table) {
            $table->string('status')->change();
        });
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('status')->change();
        });
    }

    public function down(): void
    {
        // ...
    }
};
