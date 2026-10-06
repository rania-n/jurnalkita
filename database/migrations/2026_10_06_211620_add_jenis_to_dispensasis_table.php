<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasis', function (Blueprint $table) {
            $table->string('jenis')->default('lainnya')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('dispensasis', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
