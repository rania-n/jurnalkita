<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_pelajaran_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->index();
            $table->json('baris');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('jam_pelajaran_hari', function (Blueprint $table) {
            $table->string('hari', 10)->primary();
            $table->string('kategori', 50)->index();
            $table->timestamps();
        });

        $now = now();
        foreach (['senin', 'selasa', 'rabu', 'kamis'] as $hari) {
            DB::table('jam_pelajaran_hari')->insert([
                'hari' => $hari, 'kategori' => 'senin_kamis', 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        DB::table('jam_pelajaran_hari')->insert([
            'hari' => 'jumat', 'kategori' => 'jumat', 'created_at' => $now, 'updated_at' => $now,
        ]);

        // Simpan konfigurasi terakhir yang pernah diganti agar Reset pertama
        // tetap bisa memulihkan jadwal lama yang sudah soft-delete.
        $kategoriAktif = DB::table('jam_pelajarans')->whereNull('deleted_at')->distinct()->pluck('kategori');
        foreach ($kategoriAktif as $kategori) {
            $waktuHapusTerakhir = DB::table('jam_pelajarans')
                ->where('kategori', $kategori)
                ->whereNotNull('deleted_at')
                ->max('deleted_at');
            if (! $waktuHapusTerakhir) {
                continue;
            }

            $baris = DB::table('jam_pelajarans')
                ->where('kategori', $kategori)
                ->where('deleted_at', $waktuHapusTerakhir)
                ->orderBy('jam_ke')
                ->get(['jam_ke', 'mulai', 'selesai', 'keterangan'])
                ->map(fn ($jp) => (array) $jp)
                ->values();

            if ($baris->isNotEmpty()) {
                DB::table('jam_pelajaran_snapshots')->insert([
                    'kategori' => $kategori,
                    'baris' => json_encode($baris, JSON_THROW_ON_ERROR),
                    'created_at' => $waktuHapusTerakhir,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_pelajaran_hari');
        Schema::dropIfExists('jam_pelajaran_snapshots');
    }
};
