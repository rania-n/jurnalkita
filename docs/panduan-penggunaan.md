# Panduan Penggunaan Aplikasi jurnalkita

**jurnalkita** adalah aplikasi jurnal mengajar dan presensi siswa berbasis web untuk sekolah.
Guru mengisi jurnal setiap jam pelajaran (materi, metode, presensi, foto suasana kelas),
pengurus kelas memeriksanya, guru piket memantau kelas yang belum terisi dan mencatat siswa
yang sakit/izin/terlambat/izin keluar, Waka memberi persetujuan izin keluar, dan admin
mengelola seluruh data sekolah.

Panduan ini disusun per peran. Silakan langsung menuju bagian peran Anda.

---

## Daftar Isi

1. [Memulai](#1-memulai)
2. [Admin](#2-admin)
3. [Guru](#3-guru)
4. [Guru Piket](#4-guru-piket)
5. [Wali Kelas](#5-wali-kelas)
6. [Waka](#6-waka)
7. [Pengurus Kelas](#7-pengurus-kelas)
8. [Satpam](#8-satpam)
9. [Notifikasi](#9-notifikasi)
10. [Istilah & Arti Status](#10-istilah--arti-status)
11. [Pertanyaan yang Sering Diajukan](#11-pertanyaan-yang-sering-diajukan)
12. [Untuk Pengembang: Menjalankan Aplikasi](#12-untuk-pengembang-menjalankan-aplikasi)

---

## 1. Memulai

### 1.1 Peran di dalam aplikasi

| Peran | Siapa | Tugas utama |
|---|---|---|
| **Admin** | Operator/TU sekolah | Mengelola akun, data master, jadwal, dan pengaturan |
| **Guru** | Semua guru mata pelajaran | Mengisi jurnal mengajar dan presensi siswa |
| **Guru Piket** | Guru yang mendapat giliran piket | Memantau kelas, mencatat presensi siswa, mengajukan izin keluar/lomba |
| **Wali Kelas** | Guru yang menjadi wali sebuah kelas | Melihat rekap kehadiran dan jurnal kelas perwaliannya |
| **Waka** | Wakil kepala sekolah | Menyetujui/menolak izin keluar, memantau ketidakhadiran guru |
| **Pengurus Kelas** | Satu siswa per kelas (mis. sekretaris) | Memeriksa jurnal guru, mengisi jurnal pengganti, melihat rekap kelas |
| **Satpam** | Petugas gerbang | Memindai QR surat izin keluar dan mengonfirmasi siswa kembali |

> Guru Piket dan Wali Kelas **bukan akun terpisah**. Keduanya tetap akun Guru biasa; menu
> tambahannya muncul otomatis sesuai jadwal piket atau penugasan wali kelas.

### 1.2 Masuk (Login)

1. Buka alamat aplikasi di peramban (Chrome/Safari/Edge). Aplikasi dapat dipakai di HP maupun laptop.
2. Isi **Email** dan **Password**, lalu tekan **Masuk**.
3. Anda otomatis diarahkan ke Beranda sesuai peran.

### 1.3 Mendaftar akun baru

Guru dan pengurus kelas dapat mendaftar sendiri:

1. Pada halaman login, tekan **Daftar Akun Baru**.
2. Pilih peran: **Guru** atau **Pengurus Kelas**.
3. Lengkapi formulir:
   - Guru: nama, email, No. WhatsApp, password.
   - Pengurus kelas: kelas, nama lengkap, NIS, email, No. WhatsApp, password.
4. Tekan **Daftar**. Akun berstatus **menunggu persetujuan** sampai disetujui admin.

Akun Waka, Satpam, dan akun lain yang perlu langsung aktif dibuatkan oleh admin
(lihat [2.2 Manajemen Akun](#22-manajemen-akun)).

### 1.4 Lupa password

1. Pada halaman login, tekan **Lupa Sandi**.
2. Masukkan email akun Anda, lalu tekan kirim.
3. Buka tautan atur ulang sandi yang dikirim ke email, lalu buat password baru.

### 1.5 Profil

Menu **Profil** (ada di semua peran) untuk mengubah nama, No. WhatsApp, dan password.
Setiap perubahan password akan memunculkan notifikasi pemberitahuan demi keamanan.

### 1.6 Tampilan di HP dan laptop

- **Di HP:** menu ada di bar bawah layar. Tombol bulat besar di tengah adalah aksi yang
  paling sering dipakai (mis. **Isi Jurnal** untuk guru).
- **Di laptop:** menu ada di sisi kiri layar.
- Ikon **lonceng** di kanan atas berisi notifikasi. Titik merah berarti ada notifikasi baru.
- Bila muncul pita biru **"Ada data baru dari perangkat lain"**, tekan **Muat Ulang** untuk
  melihat data terbaru (misalnya ada guru lain yang baru mengisi jurnal).

---

## 2. Admin

Menu admin dikelompokkan menjadi: **Akun & Pendaftaran**, **Data Master**, **Jadwal**,
**Pengaturan**, dan **Kesiswaan**.

> **Urutan pengisian awal yang disarankan** (saat pertama kali memakai aplikasi):
> Tahun Ajaran → Data Guru → Data Kelas → Data Siswa → Mata Pelajaran → Jam Pelajaran →
> Jadwal Pelajaran → Jadwal Piket → Jadwal Waka → Akun.

### 2.1 Beranda

Menampilkan ringkasan sekolah: jumlah guru, kelas, siswa, mata pelajaran, serta aktivitas
jurnal hari ini.

### 2.2 Manajemen Akun

**Menu: Akun & Pendaftaran › Manajemen Akun**

**Membuat akun baru:**
1. Tekan **Buat Akun**.
2. Pilih **Jenis Akun**: Guru, Pengurus Kelas, Waka, atau Satpam.
3. **Ambil dari data** (khusus Guru/Pengurus Kelas): ketik nama untuk menautkan akun ke data
   guru/siswa yang sudah ada. Kosongkan bila ingin membuat data baru sekaligus.
4. Isi nama, email, No. WhatsApp (wajib untuk Guru), dan password.
5. Untuk pengurus kelas baru: pilih kelas, isi NIS, dan jenis kelamin.
   *Satu kelas hanya boleh punya satu pengurus.*
6. Tekan **Buat Akun**. Berikan password tersebut kepada pemilik akun; ia dapat
   menggantinya sendiri lewat **Lupa Sandi** atau menu **Profil**.

**Mengubah atau menghapus akun:** tekan tombol **Ubah** / **Hapus** pada baris akun.
Akun yang dihapus tidak hilang permanen, dan email-nya dapat dipakai lagi.

### 2.3 Persetujuan Akun

**Menu: Akun & Pendaftaran › Persetujuan Akun**

Daftar akun yang mendaftar sendiri dan masih menunggu. Periksa datanya, lalu tekan
**Setujui** atau **Tolak**.

### 2.4 Audit Log

**Menu: Akun & Pendaftaran › Audit Log**

Riwayat aktivitas penting (siapa melakukan apa dan kapan): membuat/mengubah jurnal,
mengajukan izin keluar, keputusan Waka, ekspor laporan, dan sebagainya.

### 2.5 Backup Data

**Menu: Akun & Pendaftaran › Backup Data**

Tekan **Unduh** untuk mengunduh cadangan seluruh data (file `.sql`). Lakukan secara rutin,
misalnya setiap akhir pekan, dan simpan di tempat yang aman karena berisi data pribadi siswa.

### 2.6 Data Master

**Menu: Data Master**

| Menu | Isi |
|---|---|
| **Data Guru** | Nama, NIP, dan rincian beban mengajar tiap guru |
| **Data Kelas** | Nama kelas, tingkat, jurusan, wali kelas, status aktif |
| **Data Siswa** | NIS, nama, kelas, nomor absen, jabatan (pengurus/anggota), status PKL |
| **Mata Pelajaran** | Kode dan nama mapel |

Semua menu memakai pola yang sama: **Tambah** membuka formulir, **Ubah** mengisi formulir
dengan data lama, **Hapus** meminta konfirmasi terlebih dahulu. Gunakan kolom **Cari**
untuk menemukan data dengan cepat.

Tips:
- Di formulir **Data Siswa**, tombol **Otomatis** di samping *Nomor Presensi* menyarankan
  nomor absen sesuai urutan abjad nama di kelas tersebut.
- **Data Siswa** dapat menandai banyak siswa sekaligus sebagai **PKL** (praktik kerja
  lapangan). Siswa PKL tidak ikut di presensi kelas.
- **Data Kelas** dapat mengubah status aktif/nonaktif banyak kelas sekaligus.

### 2.7 Jam Pelajaran

**Menu: Jadwal › Jam Pelajaran**

Mengatur jam mulai dan selesai setiap jam pelajaran (JP). Jam dikelompokkan per
**kategori hari**, misalnya `senin_kamis` dan `jumat`.

- **Buat otomatis:** isi jam mulai, jumlah JP, durasi per JP, dan jeda istirahat (setelah
  JP ke berapa dan berapa menit), lalu tekan **Buat**.
- **Kategori hari:** tentukan hari mana memakai kategori jam yang mana. Kategori tambahan
  (mis. "Ramadhan") dapat dibuat bila jadwal jam berubah sementara.
- **Majukan jam:** menggeser semua jam pada kategori tertentu, misalnya bila masuk lebih
  pagi. **Kembalikan** membatalkan pergeseran terakhir.

> Jam pelajaran sangat penting: aplikasi memakainya untuk menentukan jam yang sedang
> berlangsung, kapan jurnal terlambat, dan kapan izin keluar kedaluwarsa.

### 2.8 Jadwal Pelajaran

**Menu: Jadwal › Jadwal Pelajaran**

Mengatur siapa mengajar apa, di kelas mana, hari apa, JP berapa, dan di ruang mana.

- **Tambah satu per satu:** tekan **Tambah**, isi kelas, mapel, guru, (guru pendamping
  bila ada), hari, JP mulai–selesai, dan ruang.
- **Impor CSV:** untuk banyak jadwal sekaligus. Tekan **Import**, lalu **Download Template
  CSV**. Isi file dengan kolom `Hari, Jam Mulai, Jam Selesai, Kelas, Mapel, Guru, Ruang`
  (nama kelas, mapel, dan guru harus sama dengan data di aplikasi), lalu pilih file dan
  tekan **Mulai Import**.

Aplikasi otomatis menolak jadwal yang bentrok: kelas yang sama di jam yang sama, guru yang
sama di dua kelas pada jam yang sama, atau ruang yang dipakai dua kelas sekaligus.

> Penulisan ruang mengikuti format resmi, misalnya **R 1**, **R 23** (dengan spasi).
> Bila diketik `R1`, aplikasi otomatis mengubahnya menjadi `R 1`.

### 2.9 Jadwal Piket

**Menu: Jadwal › Jadwal Piket**

Menentukan guru piket per hari (atau per tanggal tertentu) beserta jam shift-nya.
Guru yang sedang bertugas piket otomatis mendapat menu piket di HP-nya.

### 2.10 Jadwal Waka

**Menu: Jadwal › Jadwal Waka**

Menentukan Waka yang bertugas setiap hari. Tautan persetujuan izin keluar melalui WhatsApp
dikirim ke Waka yang bertugas pada hari itu.

### 2.11 Tahun Ajaran

**Menu: Jadwal › Tahun Ajaran**

- **Ganti semester** (ganjil/genap).
- **Naik kelas:** memindahkan siswa ke tingkat berikutnya pada pergantian tahun ajaran.
  Lakukan dengan hati-hati dan **buat backup terlebih dahulu**.

### 2.12 Pengaturan Isi Jurnal Guru

**Menu: Pengaturan › Isi Jurnal Guru**

| Mode | Arti |
|---|---|
| **Disiplin** | Guru hanya bisa mengisi jurnal saat jam pelajarannya sedang berlangsung |
| **Bebas isi hari ini** | Guru bisa mengisi jurnal kapan saja selama masih hari yang sama |
| **Bebas selamanya** | Guru bisa memilih tanggal sendiri (untuk mengisi jurnal susulan) |

Di halaman ini juga dapat diatur apakah daftar **jurnal hari ini** ditampilkan secara publik
di halaman login.

### 2.13 Hari Khusus

**Menu: Pengaturan › Hari Khusus**

Untuk tanggal yang tidak normal:
- **Pulang Cepat:** isi jam selesai. Jadwal setelah jam itu otomatis ditiadakan.
- **Tanpa KBM & Piket:** seluruh jadwal pada tanggal itu ditiadakan (mis. ujian, acara sekolah).

Jadwal yang ditiadakan tidak dihitung sebagai "tidak diisi" di Monitor Piket.

### 2.14 Kesiswaan (pantauan)

**Menu: Kesiswaan**

- **Izin Keluar:** melihat seluruh pengajuan izin keluar & lomba (hanya melihat, tidak menyetujui).
- **Monitor Piket:** sama seperti milik guru piket (lihat [4.1](#41-monitor-piket)).
- **Rekap Siswa:** rekap kehadiran seluruh siswa lintas kelas, dapat difilter kelas & tanggal,
  dan dapat diekspor ke PDF. Siswa dengan alpha 3 kali atau lebih ditandai merah muda.

---

## 3. Guru

Menu guru: **Beranda**, **Riwayat**, **Isi Jurnal**, **Jadwal**, **Monitor**, **Profil**.

### 3.1 Beranda

Menampilkan jadwal mengajar hari ini lengkap dengan statusnya:
- **Berlangsung** — jam pelajaran sedang berjalan, jurnal bisa diisi.
- **Belum Mulai**, **Jeda Istirahat**, **Sudah Lewat**, atau **Ditiadakan** (hari khusus).

Kartu sorotan di atas menunjukkan jadwal berikutnya.

### 3.2 Mengisi Jurnal

1. Tekan **Isi Jurnal** (tombol bulat di bar bawah) atau tombol isi pada jadwal di Beranda.
2. Pilih **jadwal** (jam pelajaran, mapel, kelas). Pada mode Disiplin, jadwal yang sedang
   berlangsung otomatis terpilih.
3. Pilih **Status Kehadiran Anda**:

   **Jika Hadir:**
   - Isi **Materi** yang diajarkan (materi pertemuan sebelumnya ditampilkan sebagai pengingat).
   - Pilih **Metode** pembelajaran (Ceramah, Diskusi, Praktik, Ulangan/Tes, Presentasi, atau Lainnya).
   - Ambil **Foto Suasana Kelas** langsung dengan kamera (wajib untuk jurnal hari ini).
   - Periksa **Presensi Siswa** (lihat [3.3](#33-presensi-siswa)).

   **Jika Tidak Hadir:**
   - Pilih **Alasan**: Sakit atau Izin.
   - Isi **Tugas untuk Siswa**.
   - Lampirkan surat/bukti bila ada (opsional).
   - Waka, pengurus kelas, dan wali kelas otomatis menerima notifikasi.

4. Tekan **Periksa Jurnal**. Ringkasan isian akan ditampilkan; tekan **Kirim** untuk menyimpan,
   atau **Cek Lagi** untuk kembali memperbaiki.

> **Tidak hadir di beberapa kelas sekaligus?** Pilih **Ya, Semua** pada pertanyaan kelas
> lain hari itu, lalu isi tugas umum atau tugas khusus per kelas. Jurnal untuk semua kelas
> tersebut dibuat sekaligus.

Jurnal yang diisi **setelah jam pelajarannya selesai** otomatis ditandai **Terlambat**.

### 3.3 Presensi Siswa

Semua siswa otomatis berstatus **Hadir**. Anda hanya perlu mengubah siswa yang tidak hadir:

1. Ketik nama siswa di kotak **Cari nama siswa**, lalu pilih namanya.
2. Pilih statusnya: **Hadir**, **Sakit**, **Izin**, **Terlambat**, atau **Alpha**.
3. Untuk **Terlambat**, pilih mulai JP ke berapa siswa masuk kelas.
4. Tambahkan catatan bila perlu.

Centang **Hanya tidak hadir** untuk menampilkan siswa yang tidak hadir saja.

**Status yang terisi otomatis** (ditandai keterangan kecil di bawah nama siswa dan
**terkunci**, tidak dapat diubah guru):
- **Izin keluar disetujui untuk jam ini** — siswa mendapat izin keluar/lomba yang sudah disetujui.
- **Dicatat guru piket hari ini** — piket sudah mencatat siswa sakit/izin/terlambat.

Status siswa juga mengikuti jurnal kelas lain yang sudah diisi hari itu, sehingga guru
berikutnya tidak perlu mengulang dari awal.

### 3.4 Riwayat Jurnal

Daftar semua jurnal Anda, dapat difilter tanggal, status, kelas, dan mapel.

| Status | Arti |
|---|---|
| **Menunggu** | Belum diperiksa pengurus kelas, masih bisa diubah/dihapus |
| **Terverifikasi** | Sudah diterima pengurus kelas, tidak bisa diubah lagi |
| **Revisi** | Pengurus kelas meminta perbaikan; baca catatannya lalu ubah jurnal |

Tekan sebuah jurnal untuk melihat detailnya. Tombol **Ubah Jurnal** dan **Hapus Jurnal**
tersedia selama jurnal belum terverifikasi.

### 3.5 Jadwal

Jadwal mengajar Anda selama seminggu. Gunakan tab hari (Senin–Jumat) untuk memfilter.
Jadwal piket Anda juga tampil di halaman ini.

### 3.6 Detail Siswa

Pada daftar presensi, tekan nama siswa untuk melihat riwayat kehadirannya di semua mata
pelajaran yang Anda ajar.

### 3.7 Monitor

Semua guru dapat **melihat** Monitor Piket (lihat [4.1](#41-monitor-piket)). Fitur ekspor
hanya untuk guru piket, Waka, dan admin.

---

## 4. Guru Piket

Pada hari piket, menu berubah menjadi: **Beranda**, **Jadwal**, **Monitor**,
**Presensi Siswa**, **Izin Keluar**, **Profil**. Menu jurnal disembunyikan karena guru piket
tidak dijadwalkan mengajar pada shift piketnya.

### 4.1 Monitor Piket

Memantau apakah setiap jam pelajaran sudah diisi jurnalnya oleh guru.

1. Atur rentang tanggal (**Dari**–**Sampai**). Defaultnya hari ini.
2. Pilih tampilan **Per Kelas** atau **Per Guru**.
3. Gunakan tab status: **Semua**, **Sudah Diisi** (Hadir / Tidak Hadir / Terlambat),
   **Belum Diisi** (jam belum selesai), **Tidak Diisi** (jam sudah lewat tanpa jurnal).
4. Tekan nama kelas/guru untuk **membuka daftarnya**. Isi tabel dimuat saat dibuka sehingga
   halaman tetap cepat meskipun rentang tanggalnya panjang.
5. Tekan baris yang sudah terisi untuk melihat detail jurnal dan presensinya.

**Ekspor:** **Ekspor Ringkasan** (PDF seluruh kelas) atau **Ekspor Lengkap** pada setiap
kelas/guru (PDF lengkap dengan presensi).

### 4.2 Presensi Siswa

Mencatat siswa yang membawa surat atau datang terlambat.

1. Pilih **Kelas** dan **Tanggal**.
2. Centang satu atau beberapa **Siswa**.
3. Pilih **Status Kehadiran**:

| Status | Isian tambahan |
|---|---|
| **Sakit** | Dapat diisi **berlaku sampai tanggal** (surat dokter beberapa hari) |
| **Izin** | Berlaku satu hari |
| **Terlambat** | Pilih **mulai masuk kelas di JP ke-**. JP sebelumnya tercatat terlambat, JP setelahnya hadir |
| **Dispen** | Pilih rentang JP dan **Jenis Izin**: *Izin Keluar* (perlu persetujuan Waka) atau *Lomba / Dinas* (langsung disetujui) |

4. Isi catatan dan lampirkan surat/foto bukti (dari file atau kamera).
5. Tekan **Simpan Presensi**.

Presensi otomatis **disamakan ke semua jurnal kelas** pada tanggal tersebut, termasuk jurnal
yang sudah diisi sebelumnya. Untuk Dispen, hanya jurnal pada jam yang sesuai yang berubah;
jam lainnya tidak disentuh.

Daftar **Catatan tanggal ini** di bawah formulir menampilkan semua yang sudah dicatat.
Tekan **Ubah** untuk memperbarui atau **Lihat Surat** untuk membuka bukti.

### 4.3 Izin Keluar & Lomba

**Menu: Izin Keluar**

**Mengajukan:**
1. Tekan **Ajukan Izin Keluar**.
2. Pilih **Jenis Pengajuan**:
   - **Izin Keluar** — siswa meninggalkan sekolah; **perlu persetujuan Waka**.
   - **Lomba / Dinas** — siswa mewakili sekolah; **langsung disetujui** tanpa Waka.
3. Pilih siswa (boleh beberapa sekaligus untuk kegiatan yang sama).
4. Isi tanggal (dan **Sampai Tanggal** bila lebih dari satu hari).
5. Isi **jam ke-** mulai dan selesai. Kosongkan bila berlaku sehari penuh; isi jam mulai
   saja bila berlaku sampai selesai hari itu.
6. Isi alasan/nama kegiatan dan lampirkan surat bila ada.
7. Tekan **Ajukan ke Waka** (untuk lomba: **Simpan & Setujui Langsung**).

**Setelah diajukan (Izin Keluar):**
- WhatsApp terbuka otomatis berisi tautan persetujuan untuk Waka yang bertugas hari itu.
  Tinggal tekan **kirim**. Bila tidak terbuka, tekan tautan yang muncul di atas daftar.
- Waka dapat menyetujui lewat tautan WhatsApp tersebut **tanpa perlu login**.

**Setelah disetujui:**
- Buka **Detail**, lalu tekan **Lihat Surat + QR**. Surat ini ditunjukkan siswa kepada
  satpam saat keluar gerbang. **QR berganti setiap 10 detik** untuk mencegah dipalsukan.
- Isi **Nomor WhatsApp penerima** (siswa/orang tua), lalu tekan **Kirim Bukti melalui
  WhatsApp** untuk mengirimkan tautan surat.
- Presensi siswa pada jam tersebut otomatis menjadi **Izin Keluar**.

**Tab status:** Semua, Menunggu, Disetujui, Kedaluwarsa, Ditolak. Daftar dapat difilter
tanggal dan kelas, dicari berdasarkan nama/NIS, dan diekspor ke PDF (**Unduh Ringkasan**).

**Membatalkan:** selama Waka belum memutuskan, pengaju dapat menekan **Batalkan Pengajuan**
di halaman detail.

> **Batas waktu otomatis:** pengajuan yang belum diputuskan Waka sampai jam selesainya lewat
> akan **dibatalkan otomatis**. Contoh: izin untuk besok JP 1–8 masih bisa disetujui sampai
> JP 8 besok selesai. Setelah itu statusnya menjadi Ditolak dengan catatan "Otomatis
> dibatalkan sistem", dan pengaju menerima notifikasi.

---

## 5. Wali Kelas

Guru yang menjadi wali kelas mendapat menu tambahan **Wali Kelas**.

### 5.1 Rekap Kehadiran Kelas

1. Buka **Wali Kelas** (bila mewalikan lebih dari satu kelas, pilih kelasnya).
2. Atur rentang tanggal. Bila dikosongkan, seluruh riwayat ditampilkan.
3. Pilih **Per Hari** (satu status per siswa per hari) atau **Per Mapel** (dihitung per jam
   pelajaran).
4. Tekan baris/kartu siswa untuk melihat rincian per mata pelajaran dan riwayat lengkap.

Kolom yang ditampilkan: **Hadir, Sakit, Izin, Alpha, Izin Keluar**.

### 5.2 Jurnal Harian Kelas

Tab **Jurnal Harian** menampilkan semua jurnal guru di kelas perwalian. Wali kelas hanya
dapat melihat; pemeriksaan jurnal tetap dilakukan pengurus kelas.

---

## 6. Waka

Menu waka: **Beranda**, **Izin Keluar**, **Riwayat**, **Isi Jurnal**, **Jadwal**,
**Monitor**, **Rekap**, **Profil**.

### 6.1 Beranda

Menampilkan **Antrean Izin Keluar** yang menunggu keputusan dan **Izin Keluar Terbaru**.

### 6.2 Menyetujui atau menolak izin keluar

**Lewat aplikasi:**
1. Buka **Izin Keluar**, tab **Menunggu**.
2. Tekan **Detail** pada pengajuan.
3. Isi catatan bila perlu, lalu tekan **Setujui** atau **Tolak**.

**Lewat WhatsApp (tanpa login):**
1. Buka tautan yang dikirim guru piket.
2. Periksa data siswa dan alasannya, lalu tekan **Setujui** atau **Tolak**.

Setelah diputuskan, guru piket pengaju, pengurus kelas, dan (bila disetujui) guru yang
sedang mengajar kelas tersebut otomatis menerima notifikasi.

> Pengajuan jenis **Lomba / Dinas** tidak masuk antrean Waka karena langsung disetujui.

### 6.3 Memantau guru tidak hadir

Setiap kali guru menandai dirinya tidak hadir, Waka menerima notifikasi berisi nama guru,
mapel, kelas, dan alasannya. Tekan notifikasi untuk langsung membuka detailnya di Monitor Piket.

### 6.4 Jurnal sendiri & Rekap

Waka yang juga mengajar dapat mengisi jurnal seperti guru biasa ([bagian 3](#3-guru)).
Menu **Rekap** sama dengan Rekap Siswa milik admin ([2.14](#214-kesiswaan-pantauan)).

---

## 7. Pengurus Kelas

Menu pengurus kelas: **Beranda**, **Jurnal**, **Rekap**, **Jadwal**, **Profil**.

### 7.1 Memeriksa jurnal guru

1. Buka **Jurnal**. Jurnal yang perlu diperiksa ditandai **Menunggu**.
2. Tekan **Periksa** untuk membuka detailnya: materi, metode, foto, dan presensi.
3. Bandingkan dengan kenyataan di kelas, lalu pilih:
   - **Data Sudah Sesuai** — jurnal sesuai; status menjadi **Terverifikasi**.
   - **Perlu Diperbaiki** — jurnal belum sesuai; **wajib** isi catatan apa yang perlu
     diperbaiki. Guru menerima notifikasi dan akan memperbaikinya.

Jurnal dengan status guru **Tidak Hadir** otomatis diterima dan tidak perlu diperiksa.

### 7.2 Mengisi jurnal pengganti

Bila guru tidak hadir dan belum mengisi jurnal:
1. Buka **Jurnal**, lalu tekan **Isi Jurnal Pengganti**.
2. Pilih jadwal yang guru-nya tidak hadir.
3. Isi **Tugas Tambahan** dan **Alasan**.
4. Isi presensi teman sekelas (status otomatis mengikuti catatan piket/izin keluar).
5. Tekan **Simpan**. Waka dan wali kelas otomatis diberi tahu.

### 7.3 Rekap kehadiran kelas

Sama dengan rekap wali kelas ([5.1](#51-rekap-kehadiran-kelas)), khusus untuk kelas Anda.

### 7.4 Jadwal & daftar siswa

**Jadwal** menampilkan jadwal pelajaran kelas Anda. Daftar siswa beserta kehadiran hari ini
dapat dibuka dari kartu menu di **Beranda**.

---

## 8. Satpam

Akun satpam dibuatkan admin melalui **Manajemen Akun** dengan jenis akun **Satpam**.

### 8.1 Memeriksa siswa yang keluar

1. Minta siswa menunjukkan **Surat + QR** di HP-nya.
2. Pindai QR menggunakan kamera HP satpam. Halaman hasil langsung terbuka **tanpa perlu login**.
3. Periksa hasilnya:
   - **Disetujui** (hijau) — izin sah dan berlaku hari itu, lengkap dengan daftar siswa,
     jam, dan alasannya; siswa boleh keluar.
   - **Tidak Berlaku** (merah) — QR kedaluwarsa, tidak valid, atau izinnya belum/tidak
     disetujui; siswa tidak boleh keluar.

> QR berganti setiap 10 detik. Bila gagal terbaca, minta siswa menunggu sebentar lalu
> pindai ulang. Tangkapan layar QR lama tidak akan diterima.

### 8.2 Mengonfirmasi siswa kembali

1. Masuk ke akun satpam, lalu buka **Beranda (Portal Gerbang Satpam)**.
2. Bagian **Siswa Izin Keluar (Belum Kembali)** menampilkan siswa yang sedang di luar.
3. Saat siswa kembali, tekan **Konfirmasi Kembali**. Waktu kembalinya tercatat.

---

## 9. Notifikasi

Notifikasi muncul di ikon **lonceng** dan hanya ada di dalam aplikasi. Tekan sebuah
notifikasi untuk langsung membuka halaman terkait; tekan **Tandai Semua Dibaca** untuk
membersihkan titik merah.

| Kejadian | Penerima |
|---|---|
| Guru mengisi jurnal (hadir) | Pengurus kelas: *jurnal perlu diperiksa* |
| Guru memperbaiki jurnal hasil revisi | Pengurus kelas: *jurnal hasil revisi perlu diperiksa* |
| Pengurus meminta revisi | Guru pemilik jurnal |
| Guru menandai **tidak hadir** | Waka, pengurus kelas, wali kelas |
| Pengurus mengisi jurnal pengganti | Waka, wali kelas |
| Izin keluar **diajukan** | Waka, guru piket hari itu, pengurus kelas |
| Izin keluar/lomba **disetujui** | Guru piket pengaju, guru yang mengajar kelas itu pada jam tersebut, piket hari itu, pengurus kelas |
| Izin keluar **ditolak** | Guru piket pengaju, piket hari itu, pengurus kelas |
| Izin keluar **dibatalkan otomatis** | Guru piket pengaju, piket hari itu, pengurus kelas |
| Password diubah | Pemilik akun |

---

## 10. Istilah & Arti Status

### Status kehadiran siswa

| Status | Arti |
|---|---|
| **Hadir** | Mengikuti pelajaran |
| **Sakit** | Tidak masuk karena sakit (biasanya dengan surat) |
| **Izin** | Tidak masuk dengan izin |
| **Terlambat** | Datang terlambat; JP sebelum jam masuknya tercatat terlambat |
| **Alpha** | Tidak masuk tanpa keterangan |
| **Izin Keluar** | Meninggalkan kelas/sekolah dengan izin keluar atau lomba yang disetujui |

**Rekap Per Hari** memakai satu status per siswa per hari dengan urutan prioritas:
Alpha → Sakit → Izin → Izin Keluar → Hadir. Contoh: siswa yang alpha di satu jam dan hadir
di jam lain dihitung Alpha pada hari itu.

### Status jurnal

| Status | Arti |
|---|---|
| **Menunggu** | Menunggu diperiksa pengurus kelas |
| **Terverifikasi** | Diterima pengurus kelas (atau otomatis bila guru tidak hadir) |
| **Revisi** | Perlu diperbaiki guru |
| **Terlambat** | Diisi setelah jam pelajarannya selesai |

### Status pengajuan izin keluar

| Status | Arti |
|---|---|
| **Menunggu** | Menunggu keputusan Waka |
| **Disetujui** | Disetujui dan masih berlaku |
| **Kedaluwarsa** | Disetujui, tetapi tanggal berlakunya sudah lewat |
| **Ditolak** | Ditolak Waka atau dibatalkan otomatis karena melewati batas waktu |

---

## 11. Pertanyaan yang Sering Diajukan

**Tombol Isi Jurnal tidak bisa dipakai / jadwal terkunci.**
Pada mode Disiplin, jurnal hanya bisa diisi saat jam pelajarannya berlangsung. Tunggu
jamnya dimulai, atau minta admin mengubah mode di **Pengaturan › Isi Jurnal Guru**.

**Kamera tidak mau terbuka saat mengambil foto.**
Izinkan akses kamera untuk situs ini di pengaturan peramban, lalu muat ulang halaman.
Gunakan tombol ganti kamera bila yang terbuka kamera depan.

**Status siswa terkunci dan tidak bisa saya ubah.**
Status itu berasal dari catatan guru piket atau izin keluar yang sudah disetujui. Bila ada
yang keliru, hubungi guru piket untuk memperbaikinya di menu Presensi Siswa.

**Saya salah mengisi jurnal.**
Selama belum diverifikasi pengurus kelas, buka **Riwayat**, tekan jurnalnya, lalu
**Ubah Jurnal** atau **Hapus Jurnal**. Bila sudah terverifikasi, minta pengurus kelas
memintakan revisi.

**Menu piket tidak muncul padahal saya piket.**
Menu piket hanya muncul selama jam shift piket Anda pada hari tersebut. Pastikan jadwal
piket Anda sudah diatur admin di **Jadwal › Jadwal Piket**.

**WhatsApp tidak terbuka otomatis setelah mengajukan izin keluar.**
Peramban mungkin memblokir jendela otomatis. Tekan tautan **Buka WhatsApp** yang muncul di
bagian atas halaman Izin Keluar.

**Muncul pita "Ada data baru dari perangkat lain".**
Ada perubahan data oleh pengguna lain. Tekan **Muat Ulang** setelah selesai mengisi
formulir Anda agar isian tidak hilang.

**Siswa tidak muncul di daftar presensi.**
Siswa berstatus PKL atau tidak aktif tidak ditampilkan. Periksa datanya di
**Data Master › Data Siswa**.

---

## 12. Untuk Pengembang: Menjalankan Aplikasi

Aplikasi dibangun dengan **Laravel 13** (PHP 8.5), **MySQL**, **Tailwind CSS**, dan
JavaScript murni (tanpa framework JS).

### Instalasi

```sh
git clone <url-repo> jurnalkita
cd jurnalkita
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), lalu:

```sh
php artisan migrate --seed
php artisan storage:link
npm run build
```

Jalankan dengan `composer run dev`, atau gunakan Laravel Herd (situs otomatis tersedia di
`http://jurnalkita.test`).

### Akun demo (setelah `--seed`)

Semua akun demo memakai password **`password`**.

| Peran | Email |
|---|---|
| Admin | `admin@jurnalkita.test` |
| Waka | `waka@jurnalkita.test`, `waka2@jurnalkita.test` |
| Guru (juga piket & wali kelas) | `winartin@jurnalkita.test` |
| Guru biasa | `guru.biasa@jurnalkita.test` |
| Guru piket hari ini | `guru.piket@jurnalkita.test` |
| Pengurus kelas | `kelas1@jurnalkita.test`, `kelas2@jurnalkita.test`, dst. |
| Contoh pendaftar menunggu | `ahmad.daftar@jurnalkita.test`, `xirpl1.daftar@jurnalkita.test` |

> Akun satpam tidak dibuat oleh seeder. Buat melalui **Manajemen Akun** (jenis akun Satpam).
> **Ganti semua password demo** sebelum aplikasi dipakai sungguhan.

### Menjalankan test

```sh
php artisan test --compact
```

### Tips perawatan

- Buat **backup** (menu Backup Data) sebelum naik kelas, impor jadwal, atau memperbarui aplikasi.
- Setelah memperbarui kode di server: `composer install --no-dev`, `php artisan migrate`,
  `npm run build`, lalu `php artisan optimize`.
- Untuk mencoba fitur WhatsApp/QR di HP saat pengembangan, jalankan `./tunnel.sh` untuk
  mendapatkan tautan publik sementara (memerlukan ngrok).
