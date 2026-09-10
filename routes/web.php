<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — jurnalkita (FE MVP)
|--------------------------------------------------------------------------
| Tahap ini masih frontend: route menampilkan view dengan data contoh.
| Controller + auth + middleware role ditambahkan saat backend (lihat docs/scope.md).
|
| Form yang butuh POST diarahkan ke $stub: mengembalikan pesan "belum aktif"
| supaya tombol tidak menghasilkan error 405 selama FE dikembangkan.
*/

$stub = fn () => redirect()->back()->with('info', 'Fitur ini akan aktif setelah backend siap.');

Route::redirect('/', '/login');

/* ---------------------------------------------------------------- Auth / umum */
Route::view('/login', 'auth.login')->name('login');
Route::view('/lupa-sandi', 'auth.lupa_sandi')->name('lupa_sandi');
Route::view('/pilih-peran', 'auth.pilih_peran')->name('pilih_peran');
Route::view('/daftar/guru', 'auth.register_guru')->name('register_guru');
Route::view('/daftar/pengurus-kelas', 'auth.register_pengurus_kelas')->name('register_pengurus_kelas');

Route::post('/login', $stub);
Route::post('/lupa-sandi', $stub);
Route::post('/daftar/guru', $stub);
Route::post('/daftar/pengurus-kelas', $stub);

/* ------------------------------------------------------------------- Beranda */
Route::view('/beranda', 'beranda')->name('beranda');
Route::view('/profil', 'profil')->name('profil');

/* -------------------------------------------------------------------- Jurnal */
Route::prefix('jurnal')->name('jurnal.')->group(function () {
    Route::view('/tambah', 'jurnal.create')->name('create');
    Route::view('/presensi', 'jurnal.presensi')->name('presensi');
});
Route::post('jurnal/presensi', $stub)->name('jurnal.store');

/* ---------------------------------------------------------------- Dispensasi */
Route::prefix('dispensasi')->name('dispensasi.')->group(function () {
    Route::view('/', 'dispensasi.index')->name('index');
    Route::view('/ajukan', 'dispensasi.create')->name('create');
    Route::view('/detail', 'dispensasi.show')->name('show');
});
Route::post('dispensasi', $stub)->name('dispensasi.store');

/* ------------------------------------------------------------------- Piket */
Route::prefix('piket')->name('piket.')->group(function () {
    Route::view('/jadwal', 'piket.jadwal.index')->name('jadwal.index');
    Route::view('/jadwal/tambah', 'piket.jadwal.create')->name('jadwal.create');
    Route::view('/guru', 'piket.guru.index')->name('guru.index');
    Route::view('/guru/tambah', 'piket.guru.create')->name('guru.create');
});
Route::post('piket/jadwal', $stub)->name('piket.jadwal.store');
Route::post('piket/guru', $stub)->name('piket.guru.store');

/* --------------------------------------------------------- Master data (admin) */
Route::prefix('master')->name('master.')->group(function () {
    Route::view('/', 'master.index')->name('index');

    Route::view('/kelas', 'master.kelas.index')->name('kelas.index');
    Route::view('/kelas/tambah', 'master.kelas.create')->name('kelas.create');

    Route::view('/siswa', 'master.siswa.index')->name('siswa.index');
    Route::view('/siswa/tambah', 'master.siswa.create')->name('siswa.create');

    Route::view('/mapel', 'master.mapel.index')->name('mapel.index');
    Route::view('/mapel/tambah', 'master.mapel.create')->name('mapel.create');

    Route::view('/jadwal-pelajaran', 'master.jadwal-pelajaran.index')->name('jadwal-pelajaran.index');
    Route::view('/jadwal-pelajaran/tambah', 'master.jadwal-pelajaran.create')->name('jadwal-pelajaran.create');

    Route::view('/jam-pelajaran', 'master.jam-pelajaran.index')->name('jam-pelajaran.index');
    Route::view('/jam-pelajaran/edit', 'master.jam-pelajaran.edit')->name('jam-pelajaran.edit');
});

Route::post('master/{resource}', $stub)
    ->whereIn('resource', ['kelas', 'siswa', 'mapel', 'jadwal-pelajaran', 'jam-pelajaran'])
    ->name('master.store');
