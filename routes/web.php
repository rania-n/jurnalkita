<?php

use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\JadwalPiketController;
use App\Http\Controllers\Admin\JadwalWakaController;
use App\Http\Controllers\Admin\JamPelajaranController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\PengaturanJurnalController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\DispensasiController;
use App\Http\Controllers\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\Guru\JurnalController;
use App\Http\Controllers\Guru\SiswaController as GuruSiswaController;
use App\Http\Controllers\Guru\WaliKelasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PiketController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SatpamController;
use App\Http\Controllers\Sekretaris\JurnalController as VerifikasiJurnalController;
use App\Http\Controllers\Sekretaris\KelasController as SekretarisKelasController;
use App\Http\Controllers\SuratDispensasiController;
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

/* Surat & persetujuan dispensasi lewat link WA — bertanda-tangan, TANPA login.
   Sengaja di luar grup auth. Surat juga bisa dibuka piket/waka/admin yang sudah login. */
Route::get('/surat/dispensasi/{dispensasi}', [SuratDispensasiController::class, 'show'])
    ->name('dispensasi.surat');
Route::get('/dispensasi/{dispensasi}/persetujuan', [SuratDispensasiController::class, 'persetujuan'])
    ->name('dispensasi.persetujuan');
Route::post('/dispensasi/{dispensasi}/persetujuan', [SuratDispensasiController::class, 'prosesPersetujuan']);

/* Scan QR dispensasi -- TANPA login juga. Satpam buka dari kamera HP-nya
   langsung (nggak sempat/perlu login dulu di gerbang), dan keamanannya udah
   dijamin sama token QR yang ganti tiap 10 detik sendiri (lihat
   QrDispensasi::valid()), bukan dari middleware role. Dulu ke-taruh di
   dalam grup role:satpam -- jadi kepentok login duluan, padahal view-nya
   (satpam.hasil-scan) udah dari awal pakai layout guest (tanpa sidebar). */
Route::get('/satpam/scan', [SatpamController::class, 'scan'])->name('satpam.scan');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->homeRoute()))->name('dashboard');
    Route::view('/profil', 'profil')->name('profil');
    Route::post('/profil/no-hp', [ProfilController::class, 'updateNoHp'])->name('profil.no-hp');

    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/jumlah', [NotifikasiController::class, 'jumlah'])->name('notifikasi.jumlah');
    Route::get('/notifikasi/fragment', [NotifikasiController::class, 'fragment'])->name('notifikasi.fragment');
    Route::get('/notifikasi/{id}/buka', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');
    Route::post('/notifikasi/tandai-semua-dibaca', [NotifikasiController::class, 'tandaiSemuaDibaca'])->name('notifikasi.tandai-semua-dibaca');

    /* =============================== ADMIN =============================== */
    Route::middleware('role:admin')->group(function () {
        Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

        Route::view('/admin/guru', 'admin.guru.index')->name('master.guru.index');
        Route::get('/admin/guru/{guru}', [GuruController::class, 'show'])->name('master.guru.show');
        Route::view('/admin/kelas', 'admin.kelas.index')->name('master.kelas.index');
        Route::get('/admin/kelas/{kelas}', [KelasController::class, 'show'])->name('master.kelas.show');
        Route::view('/admin/siswa', 'admin.siswa.index')->name('master.siswa.index');
        Route::get('/admin/siswa/no-absen-otomatis', [SiswaController::class, 'noAbsenOtomatis'])->name('master.siswa.no-absen-otomatis');
        Route::get('/admin/siswa/{siswa}', [SiswaController::class, 'show'])->name('master.siswa.show');
        Route::view('/admin/mapel', 'admin.mapel.index')->name('master.mapel.index');
        Route::view('/admin/jadwal-pelajaran', 'admin.jadwal-pelajaran.index')->name('master.jadwal-pelajaran.index');
        Route::view('/admin/jam-pelajaran', 'admin.jam-pelajaran.index')->name('master.jam-pelajaran.index');
        Route::view('/admin/jadwal-piket', 'admin.jadwal-piket.index')->name('master.jadwal-piket.index');
        Route::view('/admin/jadwal-waka', 'admin.jadwal-waka.index')->name('master.jadwal-waka.index');
        Route::view('/admin/akun', 'admin.akun.index')->name('master.akun.index');
        Route::view('/admin/akun-persetujuan', 'admin.akun.persetujuan')->name('master.akun.persetujuan');
        Route::view('/admin/audit-log', 'admin.audit-log.index')->name('master.audit-log.index');
        Route::view('/admin/tahun-ajaran', 'admin.tahun-ajaran.index')->name('master.tahun-ajaran.index');
        Route::get('/admin/backup', [BackupController::class, 'index'])->name('master.backup.index');
        Route::get('/admin/backup/unduh', [BackupController::class, 'download'])->name('master.backup.download');
        Route::get('/admin/pengaturan-jurnal', [PengaturanJurnalController::class, 'index'])->name('master.pengaturan-jurnal.index');
        Route::post('/admin/pengaturan-jurnal', [PengaturanJurnalController::class, 'save'])->name('master.pengaturan-jurnal.save');

        /* Manajemen akun */
        Route::post('/admin/akun', [AkunController::class, 'save'])->name('master.akun.save');
        Route::post('/admin/akun-ubah', [AkunController::class, 'update'])->name('master.akun.update');
        Route::post('/admin/akun/{user}/setujui', [AkunController::class, 'approve'])->name('master.akun.approve');
        Route::post('/admin/akun/{user}/tolak', [AkunController::class, 'reject'])->name('master.akun.reject');
        Route::post('/admin/akun/{user}/kirim-reset', [AkunController::class, 'sendResetLink'])->name('master.akun.reset');
        Route::delete('/admin/akun/{user}', [AkunController::class, 'destroy'])->name('master.akun.destroy');

        /* Tulis master data (tambah/ubah = save, hapus = destroy) */
        Route::post('/admin/guru', [GuruController::class, 'save'])->name('master.guru.save');
        Route::delete('/admin/guru/{guru}', [GuruController::class, 'destroy'])->name('master.guru.destroy');
        Route::post('/admin/kelas', [KelasController::class, 'save'])->name('master.kelas.save');
        Route::patch('/admin/kelas/status-massal', [KelasController::class, 'updateStatusBulk'])->name('master.kelas.status-massal');
        Route::delete('/admin/kelas/{kelas}', [KelasController::class, 'destroy'])->name('master.kelas.destroy');
        Route::post('/admin/siswa', [SiswaController::class, 'save'])->name('master.siswa.save');
        Route::patch('/admin/siswa/pkl-massal', [SiswaController::class, 'updatePklBulk'])->name('master.siswa.pkl-massal');
        Route::delete('/admin/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('master.siswa.destroy');
        Route::post('/admin/mapel', [MapelController::class, 'save'])->name('master.mapel.save');
        Route::delete('/admin/mapel/{mapel}', [MapelController::class, 'destroy'])->name('master.mapel.destroy');
        Route::post('/admin/jadwal-pelajaran', [JadwalController::class, 'save'])->name('master.jadwal-pelajaran.save');
        Route::delete('/admin/jadwal-pelajaran/{jadwal}', [JadwalController::class, 'destroy'])->name('master.jadwal-pelajaran.destroy');
        Route::post('/admin/jadwal-piket', [JadwalPiketController::class, 'save'])->name('master.jadwal-piket.save');
        Route::delete('/admin/jadwal-piket/{jadwalPiket}', [JadwalPiketController::class, 'destroy'])->name('master.jadwal-piket.destroy');
        Route::post('/admin/jadwal-waka', [JadwalWakaController::class, 'save'])->name('master.jadwal-waka.save');
        Route::delete('/admin/jadwal-waka/{jadwalWaka}', [JadwalWakaController::class, 'destroy'])->name('master.jadwal-waka.destroy');
        Route::post('/admin/jam-pelajaran', [JamPelajaranController::class, 'save'])->name('master.jam-pelajaran.save');
        Route::post('/admin/jam-pelajaran/generate', [JamPelajaranController::class, 'generate'])->name('master.jam-pelajaran.generate');
        Route::post('/admin/jam-pelajaran/maju', [JamPelajaranController::class, 'maju'])->name('master.jam-pelajaran.maju');
        Route::post('/admin/jam-pelajaran/reset', [JamPelajaranController::class, 'resetSebelumnya'])->name('master.jam-pelajaran.reset');
        Route::post('/admin/jam-pelajaran/kategori-hari', [JamPelajaranController::class, 'simpanKategoriHari'])->name('master.jam-pelajaran.kategori-hari');
        Route::delete('/admin/jam-pelajaran/{kategori}', [JamPelajaranController::class, 'destroyKategori'])->name('master.jam-pelajaran.destroy-kategori');
        Route::post('/admin/tahun-ajaran/naik-kelas', [TahunAjaranController::class, 'naikKelas'])->name('master.tahun-ajaran.naik-kelas');
        Route::put('/admin/tahun-ajaran/semester', [TahunAjaranController::class, 'updateSemester'])->name('master.tahun-ajaran.semester');
    });

    /* =============================== GURU =============================== */
    Route::middleware('role:guru')->group(function () {
        Route::view('/guru', 'dashboards.guru')->name('guru.dashboard');

        Route::get('/guru/wali-kelas', [WaliKelasController::class, 'index'])->name('guru.wali-kelas.index');
        Route::get('/guru/wali-kelas/{kelas}', [WaliKelasController::class, 'rekap'])->name('guru.wali-kelas.rekap');
    });

    // Isi Jurnal + Jadwal Mengajar -- Waka ikut dikasih akses karena di dunia
    // nyata Waka juga megang jadwal ngajar sendiri (bukan cuma approve
    // dispensasi). Kalau akun waka itu kebetulan nggak ada data Guru terkait,
    // JurnalController::guru() sendiri yang nolak dengan pesan jelas.
    Route::middleware('role:guru,waka')->group(function () {
        Route::get('/guru/jadwal', [GuruJadwalController::class, 'index'])->name('guru.jadwal.index');

        Route::get('/guru/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
        Route::get('/guru/jurnal/versi', [JurnalController::class, 'versi'])->name('jurnal.versi');
        Route::get('/guru/jurnal/tambah', [JurnalController::class, 'create'])->name('jurnal.create');
        Route::post('/guru/jurnal', [JurnalController::class, 'store'])->name('jurnal.store');
        // Statis, HARUS didaftarkan sebelum route {jurnal} di bawah (POST
        // /guru/jurnal/{jurnal} bisa "nangkep" /guru/jurnal/tidak-hadir-massal
        // kalau didaftarkan belakangan -- {jurnal} nganggep itu ID/slug).
        // Halaman GET-nya udah nggak ada -- "Tidak Hadir Semua Kelas" sekarang
        // nyatu di jurnal.create (blok-massal-kelas), endpoint POST ini yang
        // dipanggil form yang sama.
        Route::post('/guru/jurnal/tidak-hadir-massal', [JurnalController::class, 'storeMassal'])->name('jurnal.massal.store');
        Route::get('/guru/jurnal/{jurnal}/fragment', [JurnalController::class, 'showFragment'])->name('jurnal.show.fragment');
        // Halaman GET penuh buat "Ubah Jurnal" udah nggak ada -- popup "Lihat"
        // yang nyatu jadi form ubah lewat fragment ini (tombol "Ubah Jurnal"
        // di _detail-fragment nge-swap isi popup, bukan pindah halaman).
        Route::get('/guru/jurnal/{jurnal}/ubah/fragment', [JurnalController::class, 'editFragment'])->name('jurnal.edit.fragment');
        Route::post('/guru/jurnal/{jurnal}', [JurnalController::class, 'update'])->name('jurnal.update');
        Route::delete('/guru/jurnal/{jurnal}', [JurnalController::class, 'destroy'])->name('jurnal.destroy');

        Route::get('/guru/siswa/{siswa}', [GuruSiswaController::class, 'show'])->name('guru.siswa.show');
    });

    /* ==================== SEKRETARIS (pengurus kelas) ==================== */
    Route::middleware('role:siswa')->prefix('sekretaris')->name('sekretaris.')->group(function () {
        Route::view('/', 'dashboards.sekretaris')->name('dashboard');

        Route::get('/kelas', [SekretarisKelasController::class, 'siswa'])->name('kelas.siswa');
        Route::get('/jadwal', [SekretarisKelasController::class, 'jadwal'])->name('kelas.jadwal');
        Route::get('/rekap', [SekretarisKelasController::class, 'rekap'])->name('kelas.rekap');

        Route::get('/jurnal', [VerifikasiJurnalController::class, 'index'])->name('jurnal.index');
        Route::get('/jurnal/versi', [VerifikasiJurnalController::class, 'versi'])->name('jurnal.versi');
        Route::get('/jurnal/pengganti', [VerifikasiJurnalController::class, 'createPengganti'])->name('jurnal.pengganti');
        Route::post('/jurnal/pengganti', [VerifikasiJurnalController::class, 'storePengganti'])->name('jurnal.pengganti.store');
        Route::get('/jurnal/{jurnal}/fragment', [VerifikasiJurnalController::class, 'showFragment'])->name('jurnal.show.fragment');
        Route::post('/jurnal/{jurnal}/verifikasi', [VerifikasiJurnalController::class, 'verifikasi'])->name('jurnal.verifikasi');
    });

    /* =============================== WAKA =============================== */
    Route::middleware('role:waka')->group(function () {
        Route::view('/waka', 'dashboards.waka')->name('waka.dashboard');
    });

    /* ============ DISPENSASI (guru piket + waka; admin cuma lihat/oversight) ============ */
    Route::middleware('role:guru,waka,admin')->group(function () {
        Route::get('/dispensasi', [DispensasiController::class, 'index'])->name('dispensasi.index');
        Route::get('/dispensasi/versi', [DispensasiController::class, 'versi'])->name('dispensasi.versi');
        Route::get('/dispensasi/ekspor', [DispensasiController::class, 'ekspor'])->name('dispensasi.ekspor');
        Route::get('/dispensasi/{dispensasi}/fragment', [DispensasiController::class, 'showFragment'])->name('dispensasi.show.fragment');
        Route::get('/dispensasi/{dispensasi}/surat/fragment', [SuratDispensasiController::class, 'showFragment'])->name('dispensasi.surat.fragment');
    });
    Route::middleware('role:guru')->group(function () {
        Route::get('/dispensasi-ajukan/baru', [DispensasiController::class, 'create'])->name('dispensasi.create');
        Route::post('/dispensasi', [DispensasiController::class, 'store'])->name('dispensasi.store');
        Route::delete('/dispensasi/{dispensasi}', [DispensasiController::class, 'destroy'])->name('dispensasi.destroy');
    });
    Route::middleware('role:waka')->group(function () {
        Route::post('/dispensasi/{dispensasi}/waka', [DispensasiController::class, 'approveWaka'])->name('dispensasi.waka');
    });

    /* ===== MONITOR PIKET (pantauan kehadiran guru — piket, waka, admin oversight) ===== */
    Route::middleware('role:guru,waka,admin')->prefix('piket/monitor')->name('piket.monitor.')->group(function () {
        Route::get('/', [PiketController::class, 'index'])->name('index');
        Route::get('/versi', [PiketController::class, 'versi'])->name('versi');
        Route::get('/ekspor', [PiketController::class, 'ekspor'])->name('ekspor');
        Route::get('/ekspor/{tipe}/{id}', [PiketController::class, 'eksporDetail'])->name('ekspor.detail');
        Route::get('/jurnal/{jurnal}', [PiketController::class, 'jurnalDetail'])->name('jurnal');
    });

    Route::middleware('role:guru')->prefix('piket/presensi-siswa')->name('piket.presensi-siswa.')->group(function () {
        Route::get('/', [PiketController::class, 'presensiSiswa'])->name('index');
        Route::post('/', [PiketController::class, 'simpanPresensiSiswa'])->name('store');
    });

    /* ===== REKAP KEHADIRAN SISWA (lintas kelas — waka + admin) ===== */
    Route::middleware('role:waka,admin')->prefix('rekap')->name('rekap.')->group(function () {
        Route::get('/siswa', [RekapController::class, 'siswa'])->name('siswa.index');
        Route::get('/siswa/ekspor', [RekapController::class, 'eksporSiswa'])->name('siswa.ekspor');
    });

});
