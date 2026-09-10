<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — jurnalkita
|--------------------------------------------------------------------------
| Auth (login, registrasi 2 jalur, reset, verifikasi, logout) ada di routes/auth.php.
|
| TRANSISI: route fitur masih "flat" (belum dipecah per folder role). Modul FE
| berikutnya memindahkan view ke folder per role + mengetatkan middleware `role:*`.
| Untuk sekarang cukup 'auth' + 'verified'; dashboard per role sudah dipisah.
| Aksi store/update/destroy sementara -> $stub. Lihat docs/roadmap.md.
*/

$stub = fn () => back()->with('info', 'Fitur ini sedang dikerjakan.');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRoute())
        : redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () use ($stub) {

    /* Redirect pintar ke dashboard sesuai peran (dipakai Breeze & tautan umum) */
    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->homeRoute()))->name('dashboard');

    /* Dashboard per role (target redirect setelah login) */
    Route::middleware('role:admin')->get('/admin', fn () => view('dashboards.admin'))->name('admin.dashboard');
    Route::middleware('role:guru')->get('/guru', fn () => view('dashboards.guru'))->name('guru.dashboard');
    Route::middleware('role:siswa')->get('/sekretaris', fn () => view('dashboards.sekretaris'))->name('sekretaris.dashboard');
    Route::middleware('role:waka')->get('/waka', fn () => view('dashboards.waka'))->name('waka.dashboard');

    Route::view('/profil', 'profil')->name('profil');

    /* -------- Master data (admin) -------- */
    Route::middleware('role:admin')->group(function () use ($stub) {
        Route::view('/master', 'master.index')->name('master.index');
        Route::view('/master/kelas', 'master.kelas.index')->name('master.kelas.index');
        Route::view('/master/kelas/tambah', 'master.kelas.create')->name('master.kelas.create');
        Route::view('/master/siswa', 'master.siswa.index')->name('master.siswa.index');
        Route::view('/master/siswa/tambah', 'master.siswa.create')->name('master.siswa.create');
        Route::view('/master/mapel', 'master.mapel.index')->name('master.mapel.index');
        Route::view('/master/mapel/tambah', 'master.mapel.create')->name('master.mapel.create');
        Route::view('/master/jadwal-pelajaran', 'master.jadwal-pelajaran.index')->name('master.jadwal-pelajaran.index');
        Route::view('/master/jadwal-pelajaran/tambah', 'master.jadwal-pelajaran.create')->name('master.jadwal-pelajaran.create');
        Route::view('/master/jam-pelajaran', 'master.jam-pelajaran.index')->name('master.jam-pelajaran.index');
        Route::view('/master/jam-pelajaran/edit', 'master.jam-pelajaran.edit')->name('master.jam-pelajaran.edit');
        Route::post('/master/{resource}', $stub)
            ->whereIn('resource', ['kelas', 'siswa', 'mapel', 'jadwal-pelajaran', 'jam-pelajaran'])
            ->name('master.store');
    });

    /* -------- Jurnal (guru) -------- */
    Route::middleware('role:guru')->group(function () use ($stub) {
        Route::view('/jurnal/tambah', 'jurnal.create')->name('jurnal.create');
        Route::view('/jurnal/presensi', 'jurnal.presensi')->name('jurnal.presensi');
        Route::post('/jurnal', $stub)->name('jurnal.store');
    });

    /* -------- Piket (guru piket & admin) -------- */
    Route::middleware('role:guru,admin')->group(function () use ($stub) {
        Route::view('/piket/jadwal', 'piket.jadwal.index')->name('piket.jadwal.index');
        Route::view('/piket/jadwal/tambah', 'piket.jadwal.create')->name('piket.jadwal.create');
        Route::view('/piket/guru', 'piket.guru.index')->name('piket.guru.index');
        Route::view('/piket/guru/tambah', 'piket.guru.create')->name('piket.guru.create');
        Route::post('/piket/jadwal', $stub)->name('piket.jadwal.store');
        Route::post('/piket/guru', $stub)->name('piket.guru.store');
    });

    /* -------- Dispensasi (sekretaris ajukan, piket & waka approve) -------- */
    Route::middleware('role:guru,siswa,waka')->group(function () use ($stub) {
        Route::view('/dispensasi', 'dispensasi.index')->name('dispensasi.index');
        Route::view('/dispensasi/ajukan', 'dispensasi.create')->name('dispensasi.create');
        Route::view('/dispensasi/detail', 'dispensasi.show')->name('dispensasi.show');
        Route::post('/dispensasi', $stub)->name('dispensasi.store');
    });
});
