<?php

use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\JadwalController;

Route::resource('kelas', KelasController::class);
Route::resource('siswa', SiswaController::class);
Route::resource('mapel', MapelController::class);
Route::resource('jadwal', JadwalController::class);