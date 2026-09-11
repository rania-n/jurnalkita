# Daftar Halaman jurnalkita

Status semua layar. Detail modul & aturan: `docs/roadmap.md`, `docs/spec.md`.

---

## ✅ Sudah ada — jangan bikin lagi

### Auth
Login · Daftar (pilih peran) · Daftar guru · Daftar pengurus kelas ·
Lupa sandi · Reset sandi · Verifikasi email · Konfirmasi sandi

### Admin (layout desktop, tabel + modal)
Beranda (statistik) · Manajemen Akun (approval + buat akun + kirim reset) ·
Data Guru · Data Kelas · Data Siswa · Mata Pelajaran · Jadwal Pelajaran ·
Jam Pelajaran · Jadwal Piket
→ tambah/ubah = modal, hapus = konfirmasi. **Belum:** Audit Log (dikerjakan teman).

> **Hapus/batal** sudah tersedia dan dibatasi: jurnal hanya bisa dihapus guru
> pemiliknya selama belum diverifikasi; dispensasi hanya bisa dibatalkan guru piket
> yang mengajukan selama Waka belum memutuskan; akun bisa dihapus admin kapan saja
> (email otomatis dilepas supaya bisa dipakai lagi). Semuanya *soft delete*.

### Guru (layout mobile)
Beranda (jadwal hari ini + badge piket) · Riwayat Jurnal · Form Jurnal ·
Presensi Siswa · Detail Jurnal (+ edit selama pending/revisi) ·
Jadwal Piket Saya · Dispensasi (daftar + ajukan + detail) · Profil

### Pengurus Kelas (layout mobile)
Beranda · Verifikasi Jurnal (daftar) · Periksa Jurnal (verifikasi / minta revisi) ·
Isi Jurnal Pengganti · Profil

### Waka (layout mobile)
Beranda · Antrean Dispensasi (daftar + detail + setujui/tolak) · Profil

---

## 🟡 Halaman kecil yang belum ada — boleh ditambah kapan saja

Kecil, tidak butuh keputusan besar, bisa dikerjakan siapa saja sela-sela modul.

| # | Halaman | Untuk | Catatan |
|---|---|---|---|
| K1 | **Daftar siswa sekelas** (read-only) | Pengurus kelas | Lihat NIS / no. absen teman sekelas. ~½ hari. |
| K2 | **Dashboard Waka lebih berisi** | Waka | Sekarang cuma 1 kartu. Tambah statistik dispensasi (bulan ini: diajukan / disetujui / ditolak) + daftar terbaru. |
| K3 | **Detail Kelas** (roster + jadwal + wali) | Admin | Sekarang info kelas kepisah di 3 menu. Satu halaman rangkuman. |
| K4 | **Detail Siswa** (riwayat kehadiran) | Guru / Admin | Klik nama siswa di presensi → rekap hadir/sakit/izin/alpha siswa itu. |
| K5 | **Halaman 403 / 404 custom** | Semua | Sekarang pakai bawaan Laravel (polos). |
| K6 | **Dashboard Guru: shortcut Piket & Dispensasi** | Guru | Kartu aksi di beranda saat guru sedang jadi piket. |

---

## 🔴 Backlog besar — JANGAN sekarang

Butuh keputusan guru pembimbing, effort besar, atau integrasi eksternal.
Sudah tercatat di `docs/scope.md`.

| # | Fitur | Kenapa ditunda |
|---|---|---|
| B1 | **Notifikasi** (lonceng + halaman) | guru piket → status dispensasi; guru → jurnal diminta revisi. Perlu tabel + realtime/polling. |
| B2 | **Surat dispensasi** (halaman/PDF + QR berputar 10 dtk) | Perlu desain surat + logika HMAC QR. |
| B3 | **Peran Satpam + halaman scan QR** | Peran baru, alur scan, validasi token. |
| B4 | **Integrasi WhatsApp** | API pihak ketiga (Fonnte/Wablas) + biaya. Link approve Waka, kirim surat. |
| B5 | **Ekspor laporan piket** (Excel/PDF per hari/guru/kelas) | Perlu library ekspor + definisi "kejadian piket". |
| B6 | **Rekap wali kelas** (dashboard khusus) | Wali kelas belum jadi peran/menu. Rekap kehadiran per siswa. |
| B7 | **Tahun ajaran & kenaikan kelas** | Kolom `tahun_ajaran`, scope aktif, proses naik kelas, arsip. Lihat `spec.md` §E. |
| B8 | **Guru pengganti & tukar jam** | Alur persetujuan antar guru. |
| B9 | **Kategori jam pelajaran dinamis** (Ramadhan, Ujian, dll) | Perlu tabel kategori terpisah. `spec.md` §F. |
| B10 | **Geolokasi + deteksi telat** pada jurnal | Kolom lat/long/islate, izin lokasi browser. |

---

## Urutan saran

1. **Sekarang:** review MVP (guru) + selesaikan Audit Log (teman).
2. **Kalau tim masih ada waktu sebelum backend "resmi" dianggap selesai:** ambil K1–K6 (kecil, aman paralel).
3. **Backlog (B1–B10):** bahas dulu dengan guru pembimbing mana yang benar-benar dipakai untuk demo/penilaian. Kemungkinan besar B2+B3 (surat + satpam) yang paling "wow" untuk sidang.
