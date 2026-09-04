<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya jalankan jika koneksi menggunakan MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        DB::unprepared("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                password VARCHAR(255) NOT NULL,
                rolebase ENUM('guru', 'siswa', 'admin', 'piket') NOT NULL,
                statusapproval ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                usernameactive VARCHAR(50) GENERATED ALWAYS AS (IF(deletedat IS NULL, username, NULL)) VIRTUAL,
                emailactive VARCHAR(100) GENERATED ALWAYS AS (IF(deletedat IS NULL, email, NULL)) VIRTUAL,
                UNIQUE (usernameactive),
                UNIQUE (emailactive)
            );

            CREATE TABLE guru (
                id INT AUTO_INCREMENT PRIMARY KEY,
                userid INT NULL,
                nik VARCHAR(20) NOT NULL UNIQUE,
                nip VARCHAR(30) NULL,
                namaguru VARCHAR(100) NOT NULL,
                nomorhp VARCHAR(20) NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                nikactive VARCHAR(20) GENERATED ALWAYS AS (IF(deletedat IS NULL, nik, NULL)) VIRTUAL,
                UNIQUE (nikactive),
                FOREIGN KEY (userid) REFERENCES users(id) ON DELETE SET NULL
            );

            CREATE TABLE kelas (
                id VARCHAR(10) PRIMARY KEY,
                namakelas VARCHAR(50) NOT NULL,
                nikwalikelas VARCHAR(20) NULL,
                jumlahsiswa INT DEFAULT 0,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (nikwalikelas) REFERENCES guru(nik) ON DELETE SET NULL
            );

            CREATE TABLE siswa (
                id INT AUTO_INCREMENT PRIMARY KEY,
                userid INT NULL,
                nisn VARCHAR(20) NOT NULL,
                namasiswa VARCHAR(100) NOT NULL,
                kelasid VARCHAR(10) NOT NULL,
                jabatankelas ENUM('penguruskelas', 'anggota') NOT NULL DEFAULT 'penguruskelas',
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                nisnactive VARCHAR(20) GENERATED ALWAYS AS (IF(deletedat IS NULL, nisn, NULL)) VIRTUAL,
                UNIQUE (nisnactive),
                FOREIGN KEY (userid) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (kelasid) REFERENCES kelas(id) ON DELETE CASCADE
            );

            CREATE TABLE mapel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                kodemapel VARCHAR(20) NOT NULL,
                namamapel VARCHAR(100) NOT NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                kodemapelactive VARCHAR(20) GENERATED ALWAYS AS (IF(deletedat IS NULL, kodemapel, NULL)) VIRTUAL,
                UNIQUE (kodemapelactive)
            );

            CREATE TABLE gurumapel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                guruid INT NOT NULL,
                mapelid INT NOT NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (guruid) REFERENCES guru(id) ON DELETE CASCADE,
                FOREIGN KEY (mapelid) REFERENCES mapel(id) ON DELETE CASCADE
            );

            CREATE TABLE jammapel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jamke INT NOT NULL,
                jammulai TIME NOT NULL,
                jamselesai TIME NOT NULL,
                kategori ENUM('seninkamis', 'jumat', 'khusus') NOT NULL DEFAULT 'seninkamis',
                keterangan VARCHAR(100) NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL
            );

            CREATE TABLE jadwal (
                id INT AUTO_INCREMENT PRIMARY KEY,
                kelasid VARCHAR(10) NOT NULL,
                mapelid INT NOT NULL,
                guruutamaid INT NOT NULL,
                gurupendampingid INT NULL,
                ruang VARCHAR(50) NOT NULL,
                hari ENUM('senin', 'selasa', 'rabu', 'kamis', 'jumat') NOT NULL,
                jamkemulai INT NOT NULL,
                jamkeselesai INT NOT NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (kelasid) REFERENCES kelas(id) ON DELETE CASCADE,
                FOREIGN KEY (mapelid) REFERENCES mapel(id) ON DELETE CASCADE,
                FOREIGN KEY (guruutamaid) REFERENCES guru(id) ON DELETE CASCADE,
                FOREIGN KEY (gurupendampingid) REFERENCES guru(id) ON DELETE SET NULL
            );

            CREATE TABLE jadwalpiket (
                id INT AUTO_INCREMENT PRIMARY KEY,
                guruid INT NOT NULL,
                hari ENUM('senin', 'selasa', 'rabu', 'kamis', 'jumat') NOT NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (guruid) REFERENCES guru(id) ON DELETE CASCADE
            );

            CREATE TABLE jurnal (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jadwalid INT NOT NULL,
                gurupengisiid INT NOT NULL,
                verifikatorsiswaid INT NULL,
                tanggal DATE NOT NULL,
                jamkemulai INT NOT NULL,
                jamkeselesai INT NOT NULL,
                statushadarguru ENUM('hadir', 'tugas', 'tidakhadir') NOT NULL DEFAULT 'hadir',
                materi TEXT NOT NULL,
                statuskbm ENUM('tatapmuka', 'praktikum', 'tugasmandiri', 'ujian') NOT NULL DEFAULT 'tatapmuka',
                statusverifikasi ENUM('pending', 'terverifikasi', 'ajukankoreksi', 'autoverified') NOT NULL DEFAULT 'pending',
                catatankoreksi TEXT NULL,
                islate BOOLEAN DEFAULT FALSE,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (jadwalid) REFERENCES jadwal(id) ON DELETE CASCADE,
                FOREIGN KEY (gurupengisiid) REFERENCES guru(id) ON DELETE CASCADE,
                FOREIGN KEY (verifikatorsiswaid) REFERENCES siswa(id) ON DELETE SET NULL
            );

            CREATE TABLE absensi (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jurnalid INT NOT NULL,
                siswaid INT NOT NULL,
                statuskehadiran ENUM('hadir', 'sakit', 'izin', 'alpha', 'dispensasi', 'terlambat') NOT NULL DEFAULT 'hadir',
                catatankhusus VARCHAR(255) NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (jurnalid) REFERENCES jurnal(id) ON DELETE CASCADE,
                FOREIGN KEY (siswaid) REFERENCES siswa(id) ON DELETE CASCADE
            );

            CREATE TABLE gurupengganti (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jadwalid INT NOT NULL,
                guruasalid INT NOT NULL,
                gurupenggantiid INT NOT NULL,
                piketid INT NOT NULL,
                tanggal DATE NOT NULL,
                alasan VARCHAR(255) NOT NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (jadwalid) REFERENCES jadwal(id) ON DELETE CASCADE,
                FOREIGN KEY (guruasalid) REFERENCES guru(id) ON DELETE CASCADE,
                FOREIGN KEY (gurupenggantiid) REFERENCES guru(id) ON DELETE CASCADE,
                FOREIGN KEY (piketid) REFERENCES guru(id) ON DELETE CASCADE
            );

            CREATE TABLE tukarjam (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jadwalasalid INT NOT NULL,
                jadwaltujuanid INT NULL,
                gurupemohonid INT NULL,
                gurupenermaid INT NULL,
                jenistransaksi ENUM('tukar', 'penyerahan') NOT NULL DEFAULT 'tukar',
                tanggalpelaksanaan DATE NOT NULL,
                status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deletedat TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (jadwalasalid) REFERENCES jadwal(id) ON DELETE CASCADE,
                FOREIGN KEY (jadwaltujuanid) REFERENCES jadwal(id) ON DELETE CASCADE,
                FOREIGN KEY (gurupemohonid) REFERENCES guru(id) ON DELETE CASCADE,
                FOREIGN KEY (gurupenermaid) REFERENCES guru(id) ON DELETE CASCADE
            );

            CREATE TABLE auditlogs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                userid INT NULL,
                aktivitas TEXT NOT NULL,
                ipaddress VARCHAR(45) NULL,
                createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (userid) REFERENCES users(id) ON DELETE SET NULL
            );
        ");

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        DB::unprepared("
            DROP TABLE IF EXISTS auditlogs;
            DROP TABLE IF EXISTS tukarjam;
            DROP TABLE IF EXISTS gurupengganti;
            DROP TABLE IF EXISTS absensi;
            DROP TABLE IF EXISTS jurnal;
            DROP TABLE IF EXISTS jadwalpiket;
            DROP TABLE IF EXISTS jadwal;
            DROP TABLE IF EXISTS jammapel;
            DROP TABLE IF EXISTS gurumapel;
            DROP TABLE IF EXISTS mapel;
            DROP TABLE IF EXISTS siswa;
            DROP TABLE IF EXISTS kelas;
            DROP TABLE IF EXISTS guru;
            DROP TABLE IF EXISTS users;
        ");

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};