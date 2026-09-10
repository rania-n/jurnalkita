# Ruang Lingkup jurnalkita

Rencana kerja lengkap ada di **`docs/roadmap.md`**. Dokumen ini cuma memisahkan
**MVP** vs **backlog** supaya tidak keseret scope.

---

## MVP — dikerjakan sekarang (ringkas)

Auth + approval akun · Master data (admin, tabel desktop) · Buat akun dari data ·
Jurnal + absensi + foto bukti · Verifikasi jurnal oleh sekretaris ·
Dispensasi 2 tahap (piket → waka) · Dashboard + header per role · Audit log.

Detail & pembagian tim: `docs/roadmap.md`.

---

## BACKLOG — JANGAN dikerjakan di MVP

Butuh desain / effort besar, tidak memblokir MVP. Dicatat di sini biar tidak hilang.

### Fitur
- **Guru pengganti** (`gurupengganti`) — piket menunjuk guru pengganti saat guru tidak hadir.
- **Tukar jam / penyerahan jam** (`tukarjam`) — antar guru.
- **Ekspor laporan** untuk guru piket — per hari, per guru / semua guru, per kelas / semua kelas (Excel/PDF).
- **Surat dispensasi** yang disetujui → PDF + **QR code**.
- **Role satpam** — terima surat dispensasi approved untuk cek siswa keluar sekolah (scan QR).
- **Notifikasi** (in-app / email / WA).
- **Rekap wali kelas** — dashboard kehadiran + dispensasi murid kelasnya.
- **Riwayat & edit jurnal** lanjutan (revisi setelah ditolak sekretaris, dsb).

### Teknis
- Geolokasi + deteksi telat pada pengisian jurnal (`latitude`/`longitude`/`islate`).
- SMTP produksi (dev pakai `MAIL_MAILER=log`).
- Auto-verifikasi jurnal (`autoverified`) bila sekretaris tidak merespons dalam X jam.

### Peran & catatan yang belum final
- Detail teknis akun **waka** (dibuat admin) — perlu konfirmasi ke guru pembimbing.
- Apakah pengurus kelas bisa lebih dari 1 orang per kelas.

---

## Prinsip

1. Kalau fiturnya ada di daftar backlog ini → **NANTI**, bukan sekarang.
2. Utamakan alur MVP jalan end-to-end dulu, baru poles.
3. Struktur sesederhana mungkin; dirombak saat kebutuhannya nyata.
