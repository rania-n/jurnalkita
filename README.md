<div align="center">

<img src="docs/assets/banner.svg" alt="jurnalkita - Sistem Jurnal Mengajar dan Presensi Siswa" width="100%">

<br>

![Laravel](https://img.shields.io/badge/Laravel-13-1B2A4A?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3+-1B2A4A?style=for-the-badge&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-v4-1B2A4A?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-8-1B2A4A?style=for-the-badge&logo=vite&logoColor=white)

[![Drive Penggunaan](https://img.shields.io/badge/Drive-Penggunaan-0A5C36?style=for-the-badge&logo=googledrive&logoColor=white)](https://drive.google.com/drive/folders/1pFlESE6AGejzRO-shkvtrIPqYPVRQ1Oo)
[![Panduan](https://img.shields.io/badge/Panduan-Penggunaan-0369A1?style=for-the-badge&logo=readthedocs&logoColor=white)](docs/panduan-penggunaan.md)
[![Mulai](https://img.shields.io/badge/Mulai-Cepat-6D28D9?style=for-the-badge&logo=rocket&logoColor=white)](#-memulai)

**[Tentang](#-tentang)** · **[Tampilan](#-tampilan-aplikasi)** · **[Fitur](#-fitur-utama)** · **[Peran](#-peran-pengguna)** · **[Memulai](#-memulai)** · **[Dokumentasi](#-dokumentasi)** · **[Tim](#-tim-pengembang)**

</div>

---

## 📖 Tentang

**jurnalkita** membantu sekolah mencatat kegiatan belajar mengajar secara rapi dan dapat dipantau. Guru mengisi jurnal setiap jam pelajaran, pengurus kelas memeriksanya, guru piket memantau kelas yang belum terisi, dan Waka memberi persetujuan izin siswa. Seluruh data dikelola Admin.

Tampilannya dirancang mobile first, karena sebagian besar pengguna membukanya lewat HP.

## 📸 Tampilan Aplikasi

> Semua tangkapan layar memakai data contoh dengan nama samaran.

<table>
  <tr>
    <td align="center" width="25%"><img src="docs/assets/screens/masuk.png" alt="Halaman masuk"><br><sub><b>Masuk</b></sub></td>
    <td align="center" width="25%"><img src="docs/assets/screens/beranda-piket.png" alt="Beranda guru piket"><br><sub><b>Beranda Guru Piket</b></sub></td>
    <td align="center" width="25%"><img src="docs/assets/screens/monitor-piket.png" alt="Monitor piket"><br><sub><b>Monitor Piket</b></sub></td>
    <td align="center" width="25%"><img src="docs/assets/screens/presensi-siswa.png" alt="Presensi siswa oleh piket"><br><sub><b>Presensi Siswa</b></sub></td>
  </tr>
  <tr>
    <td align="center"><img src="docs/assets/screens/lomba-izin.png" alt="Daftar lomba dan izin"><br><sub><b>Lomba / Izin</b></sub></td>
    <td align="center"><img src="docs/assets/screens/lomba-izin-form.png" alt="Form pengajuan lomba atau izin"><br><sub><b>Form Pengajuan</b></sub></td>
    <td align="center"><img src="docs/assets/screens/lomba-izin-detail.png" alt="Detail pengajuan"><br><sub><b>Detail Pengajuan</b></sub></td>
    <td align="center"><img src="docs/assets/screens/waka.png" alt="Beranda Waka"><br><sub><b>Beranda Waka</b></sub></td>
  </tr>
</table>

<details>
<summary><b>Lihat tampilan peran lain</b></summary>
<br>

<table>
  <tr>
    <td align="center" width="30%"><img src="docs/assets/screens/pengurus-kelas.png" alt="Beranda pengurus kelas"><br><sub><b>Beranda Pengurus Kelas</b></sub></td>
    <td align="center" width="70%"><img src="docs/assets/screens/admin.png" alt="Beranda Admin"><br><sub><b>Beranda Admin (desktop)</b></sub></td>
  </tr>
</table>

</details>

## ✨ Fitur Utama

| | Fitur | Keterangan |
|---|---|---|
| 📝 | **Jurnal & Presensi** | Materi, metode, presensi siswa, dan foto suasana kelas per jam pelajaran |
| ✅ | **Verifikasi Pengurus Kelas** | Pengurus kelas memeriksa jurnal yang diisi guru |
| 👁️ | **Monitor Piket** | Pantauan kehadiran guru per hari beserta status jurnal tiap jadwal, bisa diekspor ke PDF |
| 🩺 | **Presensi Siswa oleh Piket** | Mencatat siswa sakit, izin, dan terlambat, lalu disamakan ke semua jurnal kelas |
| 🏆 | **Lomba / Izin** | Pengajuan izin keluar dan lomba / dinas dengan persetujuan Waka lewat WhatsApp |
| 🔳 | **Surat + QR Berputar** | Surat izin dengan kode QR yang berganti tiap 10 detik, dipindai lewat kamera HP |
| 🔔 | **Notifikasi** | Pemberitahuan di dalam aplikasi untuk guru, piket, pengurus kelas, dan Waka |
| 📊 | **Rekap Kehadiran** | Rekap per siswa, kelas, dan mata pelajaran |
| 🎓 | **Tahun Ajaran & Kenaikan Kelas** | Kenaikan tingkat X → XI → XII, kelas XII lulus, data lama diarsipkan |
| 🧾 | **Audit Log** | Jejak aktivitas penting yang dapat ditelusuri Admin |

## 👥 Peran Pengguna

| Peran | Tugas utama |
|---|---|
| **Admin** | Mengelola akun, data master, jadwal, dan pengaturan |
| **Guru** | Mengisi jurnal mengajar dan presensi siswa |
| **Guru Piket** | Memantau kelas, mencatat presensi siswa, dan mengajukan izin keluar / lomba |
| **Wali Kelas** | Melihat rekap kehadiran dan jurnal kelas perwalian |
| **Waka** | Menyetujui atau menolak izin keluar dan memantau ketidakhadiran guru |
| **Pengurus Kelas** | Memeriksa jurnal kelasnya |

### Alur singkat izin keluar

```mermaid
flowchart LR
    A([Guru piket mengajukan]) --> B{Jenis pengajuan}
    B -->|Lomba / Dinas| C[Langsung disetujui]
    B -->|Izin Keluar| D[Tautan persetujuan dikirim ke Waka lewat WhatsApp]
    D --> E{Keputusan Waka}
    E -->|Setuju| C
    E -->|Tolak| F([Ditolak])
    C --> G[Surat + QR berputar untuk siswa]
    G --> H([Satpam memindai QR di gerbang])
```

## 🚀 Memulai

Prasyarat: PHP 8.3 atau lebih baru, Composer, dan Node.js.

```bash
git clone https://github.com/rania-n/jurnalkita.git
cd jurnalkita
composer install
composer setup        # membuat .env, kunci aplikasi, database SQLite, migrasi + data contoh, dan build aset
php artisan serve
```

<details>
<summary><b>Jika <code>composer setup</code> gagal, jalankan secara manual</b></summary>

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
npm install && npm run build
```

</details>

Saat pengembangan, jalankan `npm run dev` di terminal terpisah.

<details>
<summary><b>Menggunakan MySQL (bukan SQLite)</b></summary>

Ubah `.env`: `DB_CONNECTION=mysql`, isi `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`, buat databasenya, lalu jalankan `php artisan migrate:fresh --seed`.

</details>

<details>
<summary><b>Login gagal atau halaman galat?</b></summary>

Hampir selalu karena `.env` belum ada (`APP_KEY` kosong) atau database belum dimigrasi. Ulangi `composer setup`.

</details>

### 🔑 Akun Contoh

Kata sandi semua akun: `password`

| Email | Peran |
|---|---|
| `admin@jurnalkita.test` | Admin |
| `waka@jurnalkita.test` | Waka |
| `winartin@jurnalkita.test` | Guru (juga mendapat jadwal piket, hari Senin) |
| `guru.biasa@jurnalkita.test` | Guru tanpa jadwal piket |
| `guru.piket@jurnalkita.test` | Guru piket (jadwal dipasang pada hari seeder dijalankan) |
| `kelas1@jurnalkita.test` | Pengurus kelas |

### 🧪 Pengujian & Format Kode

```bash
php artisan test
vendor/bin/pint
```

## 📚 Dokumentasi

| Dokumen | Isi |
|---|---|
| [Drive Penggunaan](https://drive.google.com/drive/folders/1pFlESE6AGejzRO-shkvtrIPqYPVRQ1Oo) | Materi penggunaan aplikasi |
| [`docs/panduan-penggunaan.md`](docs/panduan-penggunaan.md) | Panduan per peran |
| [`docs/spec.md`](docs/spec.md) | Aturan alur detail |
| [`docs/scope.md`](docs/scope.md) | Cakupan fitur dan rencana berikutnya |
| [`docs/roadmap.md`](docs/roadmap.md) | Rencana dan pembagian modul |
| [`docs/halaman.md`](docs/halaman.md) | Daftar halaman |

## 🤝 Tim Pengembang

Proyek **Collaboration SMK**, **Kelas XI RPL 2, Semester 1**
SMK Negeri 1 Boyolangu, Tulungagung.

- **Rania**
- **Fitra**
- **Zahwa**
- **Vara**
- **Dude**

---

<div align="center">

Dibuat oleh siswa **XI RPL 2** · SMK Negeri 1 Boyolangu, Tulungagung

</div>
