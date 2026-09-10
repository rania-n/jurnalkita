<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — jurnalkita
|--------------------------------------------------------------------------
| Auth (login, registrasi 2 jalur, reset, verifikasi, logout) ada di routes/auth.php.
|
| FE MVP: view menampilkan data seeder. Aksi tulis (store/update/delete) sementara
| diarahkan ke $stub sampai controller modul terkait dibuat. Lihat docs/roadmap.md.
*/

$stub = fn () => back()->with('info', 'Fitur ini sedang dikerjakan (backend belum selesai).');

Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->homeRoute())
    : redirect()->route('login'));

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () use ($stub) {

    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->homeRoute()))->name('dashboard');
    Route::view('/profil', 'profil')->name('profil');

    /* =============================== ADMIN =============================== */
    Route::middleware('role:admin')->group(function () use ($stub) {
        Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

        Route::view('/admin/guru', 'admin.guru.index')->name('master.guru.index');
        Route::view('/admin/kelas', 'admin.kelas.index')->name('master.kelas.index');
        Route::view('/admin/siswa', 'admin.siswa.index')->name('master.siswa.index');
        Route::view('/admin/mapel', 'admin.mapel.index')->name('master.mapel.index');
        Route::view('/admin/jadwal-pelajaran', 'admin.jadwal-pelajaran.index')->name('master.jadwal-pelajaran.index');
        Route::view('/admin/jam-pelajaran', 'admin.jam-pelajaran.index')->name('master.jam-pelajaran.index');
        Route::view('/admin/jam-pelajaran/edit', 'admin.jam-pelajaran.edit')->name('master.jam-pelajaran.edit');
        Route::view('/admin/jadwal-piket', 'admin.jadwal-piket.index')->name('master.jadwal-piket.index');

        // Stub tulis admin (semua metode) sampai controller CRUD dibuat.
        Route::post('/admin/{resource}', $stub)->name('master.store');
        Route::any('/admin-aksi', $stub)->name('admin.stub');
    });

    /* =============================== GURU =============================== */
    Route::middleware('role:guru')->group(function () use ($stub) {
        Route::view('/guru', 'dashboards.guru')->name('guru.dashboard');
        Route::view('/guru/jurnal/tambah', 'jurnal.create')->name('jurnal.create');
        Route::view('/guru/jurnal/presensi', 'jurnal.presensi')->name('jurnal.presensi');
        Route::post('/guru/jurnal', $stub)->name('jurnal.store');
        Route::view('/guru/piket', 'guru.piket')->name('piket.index');
    });

    /* ============================ SEKRETARIS ============================ */
    Route::middleware('role:siswa')->group(function () {
        Route::view('/sekretaris', 'dashboards.sekretaris')->name('sekretaris.dashboard');
    });

    /* =============================== WAKA =============================== */
    Route::middleware('role:waka')->group(function () {
        Route::view('/waka', 'dashboards.waka')->name('waka.dashboard');
    });

    /* ===================== DISPENSASI (guru piket, sekretaris, waka) ===================== */
    Route::middleware('role:guru,siswa,waka')->group(function () use ($stub) {
        Route::view('/dispensasi', 'dispensasi.index')->name('dispensasi.index');
        Route::view('/dispensasi/ajukan', 'dispensasi.create')->name('dispensasi.create');
        Route::view('/dispensasi/detail', 'dispensasi.show')->name('dispensasi.show');
        Route::post('/dispensasi', $stub)->name('dispensasi.store');
    });
});
