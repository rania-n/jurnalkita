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
Beranda (jadwal hari ini + badge piket) · Riwayat Jurnal · Form Jurnal ·
Presensi Siswa · Detail Jurnal (+ edit selama pending/revisi, bisa hapus) ·
Jadwal Mengajar Saya (seminggu) · Jadwal Piket Saya · Dispensasi (daftar + ajukan
+ detail, bisa batal) · **Monitor Piket** (khusus guru piket) ·
**Rekap Wali Kelas** (khusus guru yang jadi wali — sudah selesai, dulu tercatat
B6 di bawah, ternyata sudah dikerjakan) · **Detail Siswa** (klik nama siswa di
tabel presensi → riwayat hadir/sakit/izin/alpha/dispensasi siswa itu, lintas
mapel — ini K4, selesai 2026-09-13) · Profil

> **Audit Guru selesai 2026-09-13**: fungsi CRUD (isi jurnal, ubah presensi, edit
> jurnal, hapus jurnal, ajukan dispensasi) dites langsung di browser dan aman
> semua. K4 (Detail Siswa) baru dibangun sesi ini. K6 (shortcut Piket &
> Dispensasi di beranda saat piket) ternyata **sudah ada duluan** — cuma
> dirapikan dikit (pakai `User::piketHariIni()`, bukan query manual ulang).

### Pengurus Kelas (layout mobile)
Beranda · Verifikasi Jurnal (daftar) · Periksa Jurnal (verifikasi / minta revisi) ·
Isi Jurnal Pengganti · Profil

### Waka (layout mobile)
Beranda · Antrean Dispensasi (daftar + detail + setujui/tolak) ·
**Monitor Piket** · Profil

> **Monitor Piket** — bukan jadwal piket pribadi, tapi pantauan lintas-guru: buat
> guru piket & Waka, per hari, semua jadwal di semua kelas ditampilkan lengkap
> dengan status jurnalnya (Hadir / Tugas Luar / Tidak Hadir / **Belum Diisi** —
> jurnal yang belum diisi sama sekali ditandai jelas). Ada filter tanggal/kelas/guru
> + ekspor CSV. Ini yang dimaksud "laporan piket", beda dari ekspor riwayat
> dispensasi (yang juga ada, terpisah).

---

## 🟢 Fiks dikerjakan — sudah diputuskan, tinggal jadwal

Diputuskan 2026-09-13: K1–K4 **jadi** (bukan opsional lagi), dikerjakan pas giliran
audit role pemiliknya (lihat "Urutan saran" di bawah). K5–K6 tetap boleh diambil
kapan saja, sela-sela modul mana pun.

**K4 dan K6 sudah selesai** (giliran audit Guru, 2026-09-13) — lihat catatan di
bagian "Guru" di atas. Sisa: K1, K2, K3, K5.

| # | Halaman | Untuk | Catatan | Dikerjakan pas audit |
|---|---|---|---|---|
| K1 | **Daftar siswa sekelas** (read-only) | Pengurus kelas | Lihat NIS / no. absen teman sekelas. ~½ hari. | Pengurus Kelas |
| K2 | **Dashboard Waka lebih berisi** | Waka | Sekarang cuma 1 kartu. Tambah statistik dispensasi (bulan ini: diajukan / disetujui / ditolak) + daftar terbaru. | Waka |
| K3 | **Detail Kelas** (roster + jadwal + wali) | Admin | Sekarang info kelas kepisah di 3 menu. Satu halaman rangkuman. | Admin (susulan, kecil) |
| K5 | **Halaman 403 / 404 custom** | Semua | Sekarang pakai bawaan Laravel (polos). | Kapan saja |

---

## 🔴 Backlog besar — JANGAN sekarang

Butuh keputusan guru pembimbing, effort besar, atau integrasi eksternal.
Sudah tercatat di `docs/scope.md`.

> B2, B3, B4, B7 (surat dispensasi + QR, satpam, WhatsApp, tahun ajaran/kenaikan
> kelas) — **sudah selesai dikerjakan**, lebih cepat dari rencana awal. Detail:
> `docs/spec.md` §A dan §E.

> B6 (rekap wali kelas) & B9 (kategori jam pelajaran dinamis) — **sudah selesai
> juga**, dikerjakan lebih awal dari rencana. Sisa backlog beneran cuma B1, B8, B10.

| # | Fitur | Kenapa ditunda | Prioritas |
|---|---|---|---|
| B1 | **Notifikasi** (lonceng + halaman) | guru piket → status dispensasi; guru → jurnal diminta revisi. Perlu tabel + realtime/polling. | Kalau masih ada waktu setelah semua role selesai |
| B8 | **Guru pengganti & tukar jam** | Alur persetujuan antar guru, butuh state machine baru. | **Paling akhir** — dikerjakan cuma kalau semua role (guru/pengurus kelas/waka/satpam) sudah beres & masih ada sisa waktu. Keputusan 2026-09-13. |
| B10 | **Geolokasi + deteksi telat** pada jurnal | Kolom lat/long/islate, izin lokasi browser. | Sama seperti B1 — belakangan |

---

## Urutan saran (diputuskan 2026-09-13)

Setelah audit **Admin** kelar (fungsi + tampilan + responsif, sedang jalan), lanjut
audit menyeluruh per role dengan urutan ini — alasannya di kolom kanan:

| Urutan | Role | Kenapa duluan | Ikut dikerjakan pas giliran ini |
|---|---|---|---|
| 1 | **Guru** | Ini inti aplikasi ("Jurnal & Absensi **Guru**") — paling sering dipakai, paling penting buat dinilai. | K4 (Detail Siswa), K6 (shortcut piket/dispensasi di beranda) |
| 2 | **Pengurus Kelas** (sekretaris) | Alur jurnal nggak selesai tanpa verifikasi mereka — langsung nyambung ke hasil kerja Guru di langkah 1. | K1 (Daftar siswa sekelas) |
| 3 | **Waka** | Tahap kedua approval dispensasi — kurang kritis dibanding 1–2, tapi masih dipakai rutin. | K2 (Dashboard Waka diperkaya) |
| 4 | **Satpam** | Alurnya paling sempit (scan QR + catat telat gerbang), paling kecil risikonya, aman di akhir. | — |
| — | Admin (susulan kecil) | Sisa printilan admin yang bukan bug (K3 Detail Kelas, tombol Detail, fitur backup) — diselipkan kapan senggang, bukan blocker. | K3, tombol Detail, fitur backup |
| **Paling akhir** | **B8 — Guru pengganti & tukar jam** | Fitur besar & baru, cuma dikerjakan **kalau 1–4 di atas sudah beres semua** dan masih ada sisa waktu sebelum deadline/sidang. | — |

B1 (Notifikasi) & B10 (Geolokasi) nunggu di belakang B8 — dibahas lagi nanti kalau
waktunya benar-benar masih sisa, nggak usah dipikirin dulu.
