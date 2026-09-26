<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jeda/istirahat sebelumnya cuma "tebakan" dari teks keterangan (diawali
     * "Jeda ..."), rapuh -- salah ketik dikit langsung nggak kedetek pas
     * majuin JP (lihat JamPelajaranController::maju()). Sekarang jeda punya
     * kolom sendiri: durasi + label terpisah dari keterangan bebas.
     */
    public function up(): void
    {
        Schema::table('jam_pelajarans', function (Blueprint $table) {
            $table->unsignedSmallInteger('jeda_sebelum_menit')->nullable()->after('selesai');
            $table->string('jeda_label')->nullable()->after('jeda_sebelum_menit');
        });

        // Data lama: keterangan yang diawali "Jeda (X menit)" dipindah ke kolom
        // baru, keterangan-nya dikosongkan lagi (biar bener-bener bebas dipakai
        // buat catatan lain ke depannya, nggak dobel makna).
        $baris = DB::table('jam_pelajarans')->whereNotNull('keterangan')->where('keterangan', 'like', 'Jeda%')->get();
        foreach ($baris as $jp) {
            if (preg_match('/^(.*?)\s*\((\d+)\s*menit\)$/', $jp->keterangan, $cocok)) {
                DB::table('jam_pelajarans')->where('id', $jp->id)->update([
                    'jeda_sebelum_menit' => (int) $cocok[2],
                    'jeda_label' => trim($cocok[1]) ?: null,
                    'keterangan' => null,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('jam_pelajarans', function (Blueprint $table) {
            $table->dropColumn(['jeda_sebelum_menit', 'jeda_label']);
        });
    }
};
