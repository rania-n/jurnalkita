<?php

use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\JadwalPiketController;
use App\Http\Controllers\Admin\JamPelajaranController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\SiswaController;
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
    Route::middleware('role:admin')->group(function () {
        Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

        Route::view('/admin/guru', 'admin.guru.index')->name('master.guru.index');
        Route::view('/admin/kelas', 'admin.kelas.index')->name('master.kelas.index');
        Route::view('/admin/siswa', 'admin.siswa.index')->name('master.siswa.index');
        Route::view('/admin/mapel', 'admin.mapel.index')->name('master.mapel.index');
        Route::view('/admin/jadwal-pelajaran', 'admin.jadwal-pelajaran.index')->name('master.jadwal-pelajaran.index');
        Route::view('/admin/jam-pelajaran', 'admin.jam-pelajaran.index')->name('master.jam-pelajaran.index');
        Route::view('/admin/jadwal-piket', 'admin.jadwal-piket.index')->name('master.jadwal-piket.index');
        Route::view('/admin/akun', 'admin.akun.index')->name('master.akun.index');

        /* Manajemen akun */
        Route::post('/admin/akun', [AkunController::class, 'save'])->name('master.akun.save');
        Route::post('/admin/akun/{user}/setujui', [AkunController::class, 'approve'])->name('master.akun.approve');
        Route::post('/admin/akun/{user}/tolak', [AkunController::class, 'reject'])->name('master.akun.reject');
        Route::post('/admin/akun/{user}/kirim-reset', [AkunController::class, 'sendResetLink'])->name('master.akun.reset');

        /* Tulis master data (tambah/ubah = save, hapus = destroy) */
        Route::post('/admin/guru', [GuruController::class, 'save'])->name('master.guru.save');
        Route::delete('/admin/guru/{guru}', [GuruController::class, 'destroy'])->name('master.guru.destroy');
        Route::post('/admin/kelas', [KelasController::class, 'save'])->name('master.kelas.save');
        Route::delete('/admin/kelas/{kelas}', [KelasController::class, 'destroy'])->name('master.kelas.destroy');
        Route::post('/admin/siswa', [SiswaController::class, 'save'])->name('master.siswa.save');
        Route::delete('/admin/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('master.siswa.destroy');
        Route::post('/admin/mapel', [MapelController::class, 'save'])->name('master.mapel.save');
        Route::delete('/admin/mapel/{mapel}', [MapelController::class, 'destroy'])->name('master.mapel.destroy');
        Route::post('/admin/jadwal-pelajaran', [JadwalController::class, 'save'])->name('master.jadwal-pelajaran.save');
        Route::delete('/admin/jadwal-pelajaran/{jadwal}', [JadwalController::class, 'destroy'])->name('master.jadwal-pelajaran.destroy');
        Route::post('/admin/jadwal-piket', [JadwalPiketController::class, 'save'])->name('master.jadwal-piket.save');
        Route::delete('/admin/jadwal-piket/{jadwalPiket}', [JadwalPiketController::class, 'destroy'])->name('master.jadwal-piket.destroy');
        Route::post('/admin/jam-pelajaran', [JamPelajaranController::class, 'save'])->name('master.jam-pelajaran.save');
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
