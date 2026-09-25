<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->index(['guru_id', 'tanggal', 'deleted_at'], 'jurnals_guru_tanggal_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->dropIndex('jurnals_guru_tanggal_deleted_idx');
        });
    }
};
