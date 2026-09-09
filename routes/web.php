<?php

use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\JadwalController;

Route::resource('kelas', KelasController::class);
Route::resource('siswa', SiswaController::class);
Route::resource('mapel', MapelController::class);
Route::resource('jadwal', JadwalController::class);

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/pilih_peran', function () {
    return view('auth.pilih_peran');
})->name('pilih_peran');

Route::get('/register_guru', function () {
    return view('auth.register_guru');
})->name('register_guru');

Route::get('/register_pengurus_kelas', function () {
    return view('auth.register_pengurus_kelas');
})->name('register_pengurus_kelas');

Route::get('/lupa_sandi', function () {
    return view('auth.lupa_sandi');
})->name('lupa_sandi');

Route::get('/tambah_data_kelas', function () {
    return view('auth.tambah_data_kelas');
})->name('tambah_data_kelas');

Route::get('/data_kelas', function () {
    return view('auth.data_kelas');
})->name('data_kelas');

Route::get('/tambah_data_siswa', function () {
    return view('auth.tambah_data_siswa');
})->name('tambah_data_siswa');

Route::get('/data_siswa', function () {
    return view('auth.data_siswa');
})->name('data_siswa');

Route::get('/tambah_data_mata_pelajaran', function () {
    return view('auth.tambah_data_mata_pelajaran');
})->name('tambah_data_mata_pelajaran');

Route::get('/data_mata_pelajaran', function () {
    return view('auth.data_mata_pelajaran');
})->name('data_mata_pelajaran');

Route::get('/tambah_data_jadwal_pelajaran', function () {
    return view('auth.tambah_data_jadwal_pelajaran');
})->name('tambah_data_jadwal_pelajaran');

Route::get('/data_jadwal_pelajaran', function () {
    return view('auth.data_jadwal_pelajaran');
})->name('data_jadwal_pelajaran');

Route::get('/tambah_data_dispen', function () {
    return view('auth.tambah_data_dispen');
})->name('tambah_data_dispen');

Route::get('/data_dispen', function () {
    return view('auth.data_dispen');
})->name('data_dispen');

Route::get('/detail_dispen', function () {
    return view('auth.detail_dispen');
})->name('detail_dispen');

Route::get('/tambah_isi_jurnal', function () {
    return view('auth.tambah_isi_jurnal');
})->name('tambah_isi_jurnal');

Route::get('/data_jam_pelajaran', function () {
    return view('auth.data_jam_pelajaran');
})->name('data_jam_pelajaran');

Route::get('/edit_jam_pelajaran', function () {
    return view('auth.edit_jam_pelajaran');
})->name('edit_jam_pelajaran');

Route::get('/tambah_jadwal_piket', function () {
    return view('auth.tambah_jadwal_piket');
})->name('tambah_jadwal_piket');

Route::get('/data_jadwal_piket', function () {
    return view('auth.data_jadwal_piket');
})->name('data_jadwal_piket');

Route::get('/input_absensi_siswa', function () {
    return view('auth.input_absensi_siswa');
})->name('input_absensi_siswa');

Route::get('/master_data', function () {
    return view('auth.master_data');
})->name('master_data');

Route::get('/tambah_guru', function () {
    return view('auth.tambah_guru');
})->name('tambah_guru');

Route::get('/data_guru', function () {
    return view('auth.data_guru');
})->name('data_guru');