# Daftar Halaman jurnalkita

Status semua layar. Detail modul & aturan: `docs/roadmap.md`, `docs/spec.md`.

---

## ✅ Sudah ada — jangan bikin lagi

### Auth
Login · Daftar (pilih peran) · Daftar guru · Daftar pengurus kelas ·
Lupa sandi · Reset sandi · Verifikasi email · Konfirmasi sandi

### Admin (layout desktop, tabel + modal)
Beranda (statistik) · Manajemen Akun (approval + buat akun + kirim reset) ·
Persetujuan Akun (halaman terpisah) · Data Guru · Data Kelas · Data Siswa ·
Mata Pelajaran · Jadwal Pelajaran · Jam Pelajaran · Jadwal Piket · Jadwal Waka ·
Tahun Ajaran · Audit Log
→ tambah/ubah = modal, hapus = konfirmasi. Sudah diaudit fungsi+tampilan+responsif
penuh (2026-09-13) — lihat catatan bug di bawah.

> **Hapus/batal** sudah tersedia dan dibatasi: jurnal hanya bisa dihapus guru
> pemiliknya selama belum diverifikasi; dispensasi hanya bisa dibatalkan guru piket
> yang mengajukan selama Waka belum memutuskan; akun bisa dihapus admin kapan saja
> (email otomatis dilepas supaya bisa dipakai lagi). Semuanya *soft delete*.

### Guru (layout mobile)
Beranda (jadwal hari ini + badge piket) · Riwayat Jurnal · **Form Jurnal**
(isi materi + presensi siswa + foto dalam SATU halaman, langsung simpan sekali
submit) · Detail Jurnal (+ **Ubah Jurnal** — materi & presensi digabung satu
halaman edit juga, bisa hapus) · Jadwal Mengajar Saya (seminggu) · Jadwal Piket
Saya · Dispensasi (daftar + ajukan + detail, bisa batal) · **Monitor Piket**
(khusus guru piket) · **Rekap Wali Kelas** (khusus guru yang jadi wali — sudah
selesai, dulu tercatat B6 di bawah, ternyata sudah dikerjakan) ·
**Detail Siswa** (klik nama siswa di tabel presensi → riwayat
hadir/sakit/izin/alpha/dispensasi siswa itu, lintas mapel — ini K4, selesai
2026-09-13) · Profil

> **Gabung Jurnal + Presensi jadi satu halaman (2026-09-13):** dulu isi jurnal
> itu 2 langkah (isi materi → submit → baru diarahkan ke halaman presensi
> terpisah), begitu juga ubahnya (popup materi + tombol "Ubah Presensi" ke
> halaman lain). Sekarang keduanya satu form: pilih jadwal (kelas ketauan dari
> situ) → materi & presensi & foto semuanya di satu halaman → sekali submit
> selesai. Partial `guru/jurnal/_presensi-grid.blade.php` dipakai bareng oleh
> Form Jurnal (create) & Ubah Jurnal (edit, sekarang halaman penuh — bukan
> modal kecil lagi, karena grid presensinya nggak muat di modal). Rute lama
> `jurnal.presensi` / `jurnal.presensi.save` dihapus, diganti `jurnal.edit`
> (`GET /guru/jurnal/{id}/ubah`) yang POST ke `jurnal.update` yang sama dipakai
> modal lama. Sama persis polanya sudah diterapkan juga di Isi Jurnal Pengganti
> milik Pengurus Kelas (lihat bagian "Pengurus Kelas").

> **Audit Guru selesai 2026-09-13**: fungsi CRUD (isi jurnal, ubah presensi, edit
> jurnal, hapus jurnal, ajukan dispensasi) dites langsung di browser dan aman
> semua. K4 (Detail Siswa) baru dibangun sesi ini. K6 (shortcut Piket &
> Dispensasi di beranda saat piket) ternyata **sudah ada duluan** — cuma
> dirapikan dikit (pakai `User::piketHariIni()`, bukan query manual ulang).

> **Polish Guru selesai 2026-09-13** (giliran audit yang sama, batch kedua):
> jam ke-mulai/selesai di Form Jurnal sekarang otomatis ikut jadwal yang
> dipilih (bukan lagi ikut jam saat ini); navbar mobile dapat tombol bulat
> "Isi Jurnal" yang lebih besar & beda warna di tengah; Riwayat Jurnal dapat
> tab status (Semua/Menunggu/Berhasil/Perlu Revisi); Detail Jurnal dirapikan
> jadi tampilan-dulu-baru-aksi — Ubah/Hapus dipindah ke paling bawah setelah
> presensi, dan Ubah dibuka lewat popup (bukan form yang langsung aktif);
> Jadwal Mengajar Saya sekarang ikut menampilkan jadwal piket per hari;
> Beranda dapat kartu "Hubungi Admin" (WA) + CTA "Isi Jurnal" saat tidak ada
> jadwal hari ini biar guru yang kurang teknologi tidak bingung; Profil bisa
> ganti password sendiri (field lain tetap statis, dikelola admin). Bug
> timezone (aplikasi jalan di UTC padahal sekolahnya WIB, bikin deteksi "jam
> pelajaran sekarang" meleset) ikut diperbaiki di `config/app.php`.
> **Revisi Guru selesai 2026-09-13** (giliran audit yang sama, batch ketiga —
> user testing langsung nemu beberapa bug & minta beberapa penyesuaian UX):
> tombol "Kembali" yang sempat nyasar/muter balik ke halaman itu sendiri
> (bug `url()->previous()` yang ke-reset gara-gara siklus submit form →
> redirect) diganti rute tujuan tetap di semua halaman guru; halaman Isi
> Jurnal nggak ada lagi tombol Kembali; tombol "Simpan Presensi" dulunya
> namanya "Simpan Jurnal & Absensi" (rancu, sekarang jelas presensi doang,
> beda dari popup "Ubah Jurnal"); Jadwal Mengajar Saya dapat tab
> Semua/Senin–Jumat (ada bug `Collection::only()` yang bikin 500 pas filter
> dipakai, sudah diperbaiki + ditambah test regresi); halaman Profil dulu ada
> ruang kosong lebar di layar desktop, sekarang 2 kolom; form ganti password
> (butuh password lama) diganti tombol "Reset Kata Sandi" (kirim tautan
> email) — lebih masuk akal buat yang lupa sandi lamanya; tombol "Keluar"
> yang dobel kelihatan bareng di desktop (sidebar + halaman Profil) — punya
> Profil sekarang cuma nongol di mobile; semua tombol Keluar sekarang minta
> konfirmasi dulu (`data-confirm`) biar nggak kepencet nggak sengaja; ikon
> lonceng notifikasi + ikon Hubungi Admin dipindah ke topbar (nongol di
> SEMUA halaman, bukan cuma dasbor); bottom-nav mobile: tombol FAB "Isi
> Jurnal" sekarang ada teks di bawah ikonnya (bukan cuma ikon polos), label
> "Jurnal" diganti "Riwayat" biar nggak ketuker sama "Isi Jurnal"; guru yang
> baru login disambut popup pilihan "Isi Jurnal Sekarang" / "Lihat Beranda
> Dulu" (blur backdrop, sekali per sesi login).
>
> **Notifikasi (B1) — SEKARANG LENGKAP SEMUA, 2026-09-13.** Awalnya cuma versi
> Guru (jurnal diminta revisi, dispensasi diputuskan) dikerjakan duluan atas
> permintaan langsung; sisanya ditunda sampai 4 role selesai — begitu ke-4
> role kelar, seluruh matriks di `spec.md` §G ikut dikerjakan sekaligus:
> Waka dapat notif tiap ada dispensasi baru, guru mapel terkait dapat notif
> kalau ada siswanya yang dispensasi di jam dia ngajar, pengurus kelas dapat
> notif tiap ada jurnal baru / jurnal hasil revisi yang perlu diperiksa.
> Tabel `notifications` bawaan Laravel (`Notifiable` trait di model User),
> ikon lonceng di topbar (badge titik merah kalau ada yang belum dibaca) →
> halaman `/notifikasi` (tandai satu / tandai semua dibaca) — infrastruktur
> yang sama dipakai semua peran, cuma nambah class `Notification` +
> pemicunya di controller terkait. Satpam sengaja nggak dapat notif apa
> pun — nggak ada event async yang relevan buat kerja mereka.

### Pengurus Kelas (layout mobile)
Beranda · Verifikasi Jurnal (daftar + tab status) · Periksa Jurnal (verifikasi /
minta revisi) · Isi Jurnal Pengganti · **Daftar Siswa Sekelas** (read-only,
ini K1) · **Jadwal Pelajaran Kelas** (seminggu, tab hari) · **Rekap Kehadiran**
(kehadiran siswa sekelas bulan berjalan) · Profil

> **Audit Pengurus Kelas selesai 2026-09-13**: ternyata K1 (Daftar Siswa
> Sekelas) **sudah dibangun duluan** (`Sekretaris\KelasController@siswa`),
> begitu juga 2 halaman bonus yang belum tercatat di sini — Jadwal Pelajaran
> Kelas & Rekap Kehadiran, keduanya sudah full berfungsi + ada test. Yang
> dikerjakan sesi ini: bug tombol Kembali (`url()->previous()` yang sama
> seperti di Guru) diperbaiki di Periksa Jurnal & Isi Jurnal Pengganti; Jadwal
> Pelajaran Kelas dapat tab hari (Semua/Senin–Jumat), sama kayak punya Guru;
> Isi Jurnal Pengganti sekarang jam ke-mulai/selesai auto-fill dari jadwal
> yang dipilih (dulu 2 dropdown manual terpisah, gampang salah input) — sama
> persis pola yang dipakai Form Jurnal Guru; tombol Verifikasi/Minta Revisi
> di Periksa Jurnal diganti pakai `x-ui.button` (dulu styling manual sendiri,
> sekarang konsisten sama tombol di halaman lain); komponen bersama
> `x-page-header` diperbaiki supaya judul panjang + tombol aksi nggak
> berdesakan di layar sempit (turun ke bawah dulu, baru sejajar mulai `sm:`)
> — ini otomatis ikut memperbaiki semua halaman lain yang pakai pola sama.
> Ikon "Hubungi Admin" di topbar diperluas ke semua peran (dulu guru doang).

### Waka (layout mobile)
**Beranda** (3 pintasan + statistik dispensasi bulan berjalan + daftar
terbaru — ini K2, selesai 2026-09-13) · Antrean Dispensasi (daftar + tab
status + filter + detail + setujui/tolak) · **Monitor Piket** ·
Rekap Kehadiran Siswa · Profil

> **Monitor Piket** — bukan jadwal piket pribadi, tapi pantauan lintas-guru: buat
> guru piket & Waka, per hari, semua jadwal di semua kelas ditampilkan lengkap
> dengan status jurnalnya (Hadir / Tugas Luar / Tidak Hadir / **Belum Diisi** —
> jurnal yang belum diisi sama sekali ditandai jelas). Ada filter tanggal/kelas/guru
> + ekspor CSV. Ini yang dimaksud "laporan piket", beda dari ekspor riwayat
> dispensasi (yang juga ada, terpisah).

> **Audit Waka selesai 2026-09-13**: halaman-halaman inti Waka (Antrean
> Dispensasi, Monitor Piket, Rekap Kehadiran Siswa) ternyata sudah rapi &
> konsisten dari audit Admin sebelumnya (tab bar, filter, ekspor CSV semua
> sudah ada) — nggak ada yang perlu diperbaiki di situ. Yang dikerjakan sesi
> ini: **K2** — dasbor Waka yang tadinya cuma 3 kartu pintasan (Antrean
> Dispensasi/Monitor Piket/Rekap) sekarang ditambah statistik "Dispensasi
> Bulan Ini" (Diajukan/Disetujui/Ditolak, dihitung dari kolom `tanggal`) +
> daftar 5 dispensasi terbaru (lintas status, bukan cuma yang pending) biar
> Waka lihat aktivitas tanpa buka Antrean Dispensasi dulu. Tombol
> Setujui/Tolak di halaman detail dispensasi diganti pakai `x-ui.button`
> (dulu styling manual sendiri, beda dari tombol sejenis di Pengurus Kelas
> yang sudah dirapikan duluan) biar konsisten. Test baru:
> `tests/Feature/Waka/DashboardTest.php`.

### Satpam (layout mobile)
Beranda (riwayat scan hari ini + riwayat terlambat hari ini + hapus) ·
Catat Siswa Terlambat · **Hasil Scan QR** (halaman hasil, dibuka dari kamera
bawaan HP — bukan scanner dalam web) · Profil

> **Scan QR tanpa library JS** — satpam scan pakai kamera bawaan HP seperti
> biasa (bukan buka kamera di dalam web app). QR-nya encode URL bertanda
> tangan (`QrDispensasi`, berputar tiap 10 detik) ke `/satpam/scan?id=&token=`,
> hasilnya langsung kebuka di halaman hasil (`x-layouts.guest`, tanpa
> navbar/sidebar — sengaja minimalis buat sekali lihat lalu lanjut scan
> berikutnya).

> **Audit Satpam selesai 2026-09-13 — SEMUA 4 ROLE SEKARANG BERES** (Guru →
> Pengurus Kelas → Waka → Satpam): fungsi inti (scan QR valid/kedaluwarsa/
> multi-hari, catat siswa terlambat, audit log) ternyata sudah dites lengkap
> sebelumnya (12 test di `SatpamTest.php` + `SatpamTerlambatTest.php`).
> Yang diperbaiki sesi ini: bug tombol Kembali (`url()->previous()`) di
> Catat Siswa Terlambat, sama seperti role lain. Ditemukan juga gap nyata:
> `CatatanTerlambat` sudah pakai trait `SoftDeletes` dari awal, tapi rute
> hapusnya belum pernah dibangun — jadi salah pencet siswa pas catat telat
> nggak ada cara benerin. Ditambahkan `terlambat.destroy` (DELETE), dibatasi
> punya sendiri + hari ini saja (catatan hari sebelumnya dibiarkan permanen
> demi jejak audit, sama semangatnya kayak jurnal terverifikasi). 3 test baru
> di `SatpamTerlambatTest.php`.

---

## 🟢 Fiks dikerjakan — sudah diputuskan, tinggal jadwal

Diputuskan 2026-09-13: K1–K4 **jadi** (bukan opsional lagi), dikerjakan pas giliran
audit role pemiliknya (lihat "Urutan saran" di bawah). K5–K6 tetap boleh diambil
kapan saja, sela-sela modul mana pun.

**K1, K2, K3, K4, K6 sudah selesai.** K4 & K6 pas giliran audit Guru
(2026-09-13) — lihat bagian "Guru" di atas. K1 ternyata **sudah ada dari
sesi sebelumnya**, ketauan pas audit Pengurus Kelas (2026-09-13) — lihat
bagian "Pengurus Kelas" di atas. K2 dikerjakan pas audit Waka (2026-09-13)
— lihat bagian "Waka" di atas. K3 dikerjakan 2026-09-13 sebagai susulan
Admin setelah audit ke-4 role kelar. Sisa: K5 (kapan saja, tidak mendesak).

| # | Halaman | Untuk | Catatan | Dikerjakan pas audit |
|---|---|---|---|---|
| ~~K1~~ | ~~Daftar siswa sekelas (read-only)~~ | Pengurus kelas | **Selesai** — sudah ada duluan, dikonfirmasi 2026-09-13. | Pengurus Kelas |
| ~~K2~~ | ~~Dashboard Waka lebih berisi~~ | Waka | **Selesai** — statistik dispensasi bulan ini + daftar terbaru ditambahkan, 2026-09-13. | Waka |
| ~~K3~~ | ~~Detail Kelas (roster + jadwal + wali)~~ | Admin | **Selesai** — `master.kelas.show`, dibuka lewat tombol Detail di Data Kelas, 2026-09-13. | Admin (susulan, kecil) |
| K5 | **Halaman 403 / 404 custom** | Semua | Sekarang pakai bawaan Laravel (polos). | Kapan saja |

> **Susulan Admin selesai 2026-09-13** (setelah audit ke-4 role kelar,
> "kapan senggang" dari rencana awal): **K3 — Detail Kelas** (roster siswa +
> jadwal pelajaran seminggu + wali kelas dalam satu halaman, tombol "Detail"
> pakai komponen `x-admin.row-actions` yang memang sudah punya slot itu dari
> awal, cuma belum dipakai di manapun). **Tombol Detail** — dicek lagi tabel
> admin lain, tetap tidak ada yang datanya benar-benar kepotong, jadi cuma
> dipakai di Data Kelas (K3) yang memang butuh. **Fitur Backup Data** —
> halaman baru (`/admin/backup`), tombol unduh langsung (`mysqldump` via
> `Symfony\Process`, streaming, TIDAK disimpan di server), plus riwayat
> unduh (dicatat di Audit Log). Sengaja simpel: tanpa penjadwalan otomatis,
> tanpa penyimpanan persisten — admin unduh manual kapan perlu, lalu simpan
> sendiri. Test: `tests/Feature/Admin/BackupTest.php` (akses/role doang —
> `mysqldump` sungguhan di luar cakupan test suite yang pakai SQLite
> in-memory, sudah diverifikasi manual lewat browser terhadap MySQL dev).

---

## 🔴 Backlog besar — JANGAN sekarang

Butuh keputusan guru pembimbing, effort besar, atau integrasi eksternal.
Sudah tercatat di `docs/scope.md`.

> B2, B3, B4, B7 (surat dispensasi + QR, satpam, WhatsApp, tahun ajaran/kenaikan
> kelas) — **sudah selesai dikerjakan**, lebih cepat dari rencana awal. Detail:
> `docs/spec.md` §A dan §E.

> B6 (rekap wali kelas) & B9 (kategori jam pelajaran dinamis) — **sudah selesai
> juga**, dikerjakan lebih awal dari rencana. B1 (Notifikasi) **juga sudah
> selesai** (2026-09-13, lihat bagian "Guru" di atas — dikerjakan bertahap,
> versi Guru duluan lalu dilengkapi penuh begitu ke-4 role kelar). Sisa
> backlog beneran cuma B8 & B10.

| # | Fitur | Kenapa ditunda | Prioritas |
|---|---|---|---|
| ~~B1~~ | ~~**Notifikasi** (lonceng + halaman)~~ | — | **Selesai 2026-09-13.** Seluruh matriks `spec.md` §G jalan. |
| B8 | **Guru pengganti & tukar jam** | Alur persetujuan antar guru, butuh state machine baru — belum ada spec/rancangan sama sekali di dokumen manapun. | **Paling akhir** — dikerjakan cuma kalau semua role (guru/pengurus kelas/waka/satpam) sudah beres & masih ada sisa waktu. Keputusan 2026-09-13. Semua role SUDAH beres, tapi belum ada rancangan alurnya — butuh diklarifikasi dulu sebelum dikerjakan, bukan ditebak. |
| B10 | **Geolokasi + deteksi telat** pada jurnal | Kolom lat/long/islate, izin lokasi browser — perlu keputusan produk (izin lokasi itu sensitif). | Belakangan, butuh keputusan eksplisit dulu. |

---

## Urutan saran (diputuskan 2026-09-13)

Setelah audit **Admin** kelar (fungsi + tampilan + responsif, sedang jalan), lanjut
audit menyeluruh per role dengan urutan ini — alasannya di kolom kanan:

| Urutan | Role | Kenapa duluan | Ikut dikerjakan pas giliran ini |
|---|---|---|---|
| 1 | ~~**Guru**~~ ✅ | Ini inti aplikasi ("Jurnal & Absensi **Guru**") — paling sering dipakai, paling penting buat dinilai. | K4 (Detail Siswa), K6 (shortcut piket/dispensasi di beranda) |
| 2 | ~~**Pengurus Kelas**~~ ✅ (sekretaris) | Alur jurnal nggak selesai tanpa verifikasi mereka — langsung nyambung ke hasil kerja Guru di langkah 1. | K1 (Daftar siswa sekelas, ternyata sudah ada) |
| 3 | ~~**Waka**~~ ✅ | Tahap kedua approval dispensasi — kurang kritis dibanding 1–2, tapi masih dipakai rutin. | K2 (Dashboard Waka diperkaya) |
| 4 | ~~**Satpam**~~ ✅ | Alurnya paling sempit (scan QR + catat telat gerbang), paling kecil risikonya, aman di akhir. | Rute hapus `CatatanTerlambat` (gap, model sudah `SoftDeletes` tapi belum ada UI-nya) |
| — | ~~Admin (susulan kecil)~~ ✅ | Sisa printilan admin yang bukan bug (K3 Detail Kelas, tombol Detail, fitur backup) — diselipkan kapan senggang, bukan blocker. | K3, tombol Detail, fitur backup — **semua selesai 2026-09-13** |
| **Paling akhir** ← *butuh rancangan dulu sebelum dikerjakan* | **B8 — Guru pengganti & tukar jam** | Fitur besar & baru, cuma dikerjakan **kalau 1–4 di atas sudah beres semua** dan masih ada sisa waktu sebelum deadline/sidang. | — |

**Semua 4 role (Guru/Pengurus Kelas/Waka/Satpam), susulan kecil Admin
(K3/tombol Detail/backup), dan B1 (Notifikasi, lengkap semua peran) sudah
selesai per 2026-09-13.** Sisa cuma B8 (butuh rancangan alur dulu sebelum
dikerjakan, bukan cuma "kalau ada waktu") dan B10 (butuh keputusan produk
soal izin lokasi browser).
