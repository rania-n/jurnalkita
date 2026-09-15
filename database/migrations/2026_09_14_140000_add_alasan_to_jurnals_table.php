<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->text('alasan')->nullable()->after('tugas_tambahan');
            // Materi cuma wajib kalau guru Hadir -- Tugas Luar/Tidak Hadir nggak
            // ngisi materi, jadi kolomnya harus boleh kosong.
            $table->text('materi')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->dropColumn('alasan');
            $table->text('materi')->nullable(false)->change();
        });
    }
};
