# Ruang Lingkup jurnalkita

Dokumen ini memisahkan **yang dikerjakan sekarang** dari **yang ditunda**, supaya
tidak keseret scope. Semua ide yang muncul dicatat di sini biar tidak hilang.

---

## SEKARANG — "FE MVP selesai"

Batasnya jelas: **25 layar yang sudah ada di Figma** (`docs/figma/`), dibuat rapi,
konsisten, responsif (HP → desktop), dan bisa dinavigasi. Belum ada backend/auth
betulan — route masih menampilkan view dengan data contoh.

### Yang termasuk

- **Fondasi** (sudah): token warna, font Inter, layout + komponen, build Vite.
- **Auth** (sudah, akan disesuaikan ke Figma): login, lupa sandi, pilih peran,
  registrasi guru, registrasi pengurus kelas.
- **Jurnal**: form jurnal mengajar → input presensi siswa.
- **Dispensasi**: daftar, form pengajuan, detail + approve/tolak.
- **Piket**: daftar jadwal piket + form, daftar data guru + form.
- **Master (admin)**: menu master data, kelas, siswa, mapel, jadwal pelajaran,
  jam pelajaran (lihat + edit).
- **Navigasi**: bottom nav minimal (HP) / sidebar (desktop), tombol back kiri-atas.
- **Perbaikan UX kecil** yang aman: teks minimal 12–13px, dialog konfirmasi hapus,
  notifikasi sukses, istilah diseragamkan ("Presensi"), "JP-1" → "Jam ke-1 · 07:00",
  input presensi default semua "Hadir".

### Struktur (keputusan MVP)

- View per **fitur**, bukan per role: `resources/views/{auth,jurnal,dispensasi,piket,master}/`.
- Route dikelompokkan per fitur. **Belum ada middleware role** — ditambahkan saat backend.
- Data contoh inline di Blade, ditandai `{{-- TODO: data dari controller --}}`.

---

## NANTI — butuh desain dulu, lalu FE, lalu backend

Belum ada frame Figma-nya. Tidak menghalangi MVP.

### Layar yang belum ada
- **Dashboard / Beranda** per peran (isi bottom nav mengarah ke sini).
- **Profil** (hanya lihat, tidak bisa diubah sendiri).
- **Riwayat jurnal** + edit jurnal yang sudah dibuat.
- **Notifikasi**.
- **Output surat dispensasi** yang sudah disetujui (PDF/cetak).

### Peran & hak akses (perlu dikonfirmasi ke guru pembimbing)
- **Guru** — akun dasar, daftar sendiri. Bisa jadi pengajar (isi jurnal + presensi).
- **Guru Piket** — guru yang kebagian piket. Approve dispensasi tahap 1.
  Ekspor laporan harian: semua guru / per guru, semua kelas / per kelas.
- **Wali Kelas** — guru yang jadi FK di satu kelas. Lihat rekap kehadiran +
  dispensasi murid kelasnya.
- **Waka Kesiswaan** — approve dispensasi tahap 2. Detail akun dibuat admin.
- **Admin** — kelola semua master data.
- **Pengurus Kelas / Siswa** — daftar sendiri. Lihat riwayat jurnal, bantu admin KBM harian.
- **Satpam** (paling belakang) — terima surat dispensasi yang sudah disetujui staff +
  waka, untuk cek siswa keluar sekolah. Kemungkinan pakai **QR code**.

### Fitur backend besar (fase setelah FE)
- Autentikasi + verifikasi akun oleh admin.
- Alur approval dispensasi 2 tahap (piket → waka) + status.
- Ekspor laporan (Excel/PDF) untuk guru piket.
- Rekap wali kelas.
- Generate surat dispensasi + QR.
- Notifikasi.

---

## Prinsip

1. Kalau layarnya belum ada di Figma, itu **NANTI**.
2. Perbaikan yang tidak mengubah alur = aman dikerjakan sekarang.
3. Struktur dibuat sesederhana mungkin dulu; dirombak saat kebutuhannya nyata.
