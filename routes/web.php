<?php

use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\JadwalPiketController;
use App\Http\Controllers\Admin\JamPelajaranController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\DispensasiController;
use App\Http\Controllers\Guru\JurnalController;
use App\Http\Controllers\Sekretaris\JurnalController as VerifikasiJurnalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — jurnalkita
|--------------------------------------------------------------------------
| Auth (login, registrasi 2 jalur, reset, verifikasi, logout) ada di routes/auth.php.
| Grup route dibagi per role: admin, guru, sekretaris (pengurus kelas), waka.
| Lihat docs/roadmap.md untuk status tiap modul.
*/

Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->homeRoute())
    : redirect()->route('login'));

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

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
    Route::middleware('role:guru')->group(function () {
        Route::view('/guru', 'dashboards.guru')->name('guru.dashboard');
        Route::view('/guru/piket', 'guru.piket')->name('piket.index');

        Route::get('/guru/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
        Route::get('/guru/jurnal/tambah', [JurnalController::class, 'create'])->name('jurnal.create');
        Route::post('/guru/jurnal', [JurnalController::class, 'store'])->name('jurnal.store');
        Route::get('/guru/jurnal/{jurnal}', [JurnalController::class, 'show'])->name('jurnal.show');
        Route::post('/guru/jurnal/{jurnal}', [JurnalController::class, 'update'])->name('jurnal.update');
        Route::get('/guru/jurnal/{jurnal}/presensi', [JurnalController::class, 'presensi'])->name('jurnal.presensi');
        Route::post('/guru/jurnal/{jurnal}/presensi', [JurnalController::class, 'presensiSave'])->name('jurnal.presensi.save');
    });

    /* ==================== SEKRETARIS (pengurus kelas) ==================== */
    Route::middleware('role:siswa')->prefix('sekretaris')->name('sekretaris.')->group(function () {
        Route::view('/', 'dashboards.sekretaris')->name('dashboard');

        Route::get('/jurnal', [VerifikasiJurnalController::class, 'index'])->name('jurnal.index');
        Route::get('/jurnal/pengganti', [VerifikasiJurnalController::class, 'createPengganti'])->name('jurnal.pengganti');
        Route::post('/jurnal/pengganti', [VerifikasiJurnalController::class, 'storePengganti'])->name('jurnal.pengganti.store');
        Route::get('/jurnal/{jurnal}', [VerifikasiJurnalController::class, 'show'])->name('jurnal.show');
        Route::post('/jurnal/{jurnal}/verifikasi', [VerifikasiJurnalController::class, 'verifikasi'])->name('jurnal.verifikasi');
    });

    /* =============================== WAKA =============================== */
    Route::middleware('role:waka')->group(function () {
        Route::view('/waka', 'dashboards.waka')->name('waka.dashboard');
    });

    /* ===================== DISPENSASI (guru piket + waka) ===================== */
    Route::middleware('role:guru,waka')->group(function () {
        Route::get('/dispensasi', [DispensasiController::class, 'index'])->name('dispensasi.index');
        Route::get('/dispensasi/{dispensasi}', [DispensasiController::class, 'show'])->name('dispensasi.show');
    });
    Route::middleware('role:guru')->group(function () {
        Route::get('/dispensasi-ajukan/baru', [DispensasiController::class, 'create'])->name('dispensasi.create');
        Route::post('/dispensasi', [DispensasiController::class, 'store'])->name('dispensasi.store');
    });
    Route::middleware('role:waka')->group(function () {
        Route::post('/dispensasi/{dispensasi}/waka', [DispensasiController::class, 'approveWaka'])->name('dispensasi.waka');
    });
});
