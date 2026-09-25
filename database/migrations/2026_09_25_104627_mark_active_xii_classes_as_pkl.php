<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tahunAjaranAktifId = DB::table('tahun_ajarans')->where('aktif', true)->value('id');
        $kelasXii = DB::table('kelas')->where('tingkat', 'XII');

        if ($tahunAjaranAktifId) {
            $kelasXii->where('tahun_ajaran_id', $tahunAjaranAktifId);
        } else {
            $kelasXii->whereNull('tahun_ajaran_id');
        }

        $kelasXii->update(['status' => 'pkl']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Status dapat diubah admin setelah migrasi, jadi tidak dibalik otomatis.
    }
};
