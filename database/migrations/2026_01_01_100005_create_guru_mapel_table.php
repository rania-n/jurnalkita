<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mapel utama guru (single). guru_mapel = mapel tambahan yang diajar.
        Schema::table('gurus', function (Blueprint $table) {
            $table->foreignId('mapel_utama_id')->nullable()->after('nama')->constrained('mapels')->nullOnDelete();
        });

        Schema::create('guru_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['guru_id', 'mapel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_mapel');
        Schema::table('gurus', fn (Blueprint $table) => $table->dropConstrainedForeignId('mapel_utama_id'));
    }
};
