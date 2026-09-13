# Scope jurnalkita

## MVP (dikerjakan)

Auth + approval akun · Master data (admin) · Buat akun dari data ·
Jurnal + absensi + foto · Verifikasi jurnal oleh sekretaris ·
Dispensasi 2 tahap (piket → waka) · Dashboard per peran · Audit log ·
Ekspor laporan dispensasi (CSV) · **Monitor Piket** (pantauan kehadiran guru per
hari, semua kelas, status jurnal per jadwal + ekspor CSV) · **Surat dispensasi +
QR berputar + role satpam** (surat & persetujuan lewat tautan bertanda tangan
tanpa login, QR HMAC berputar tiap 10 detik, satpam scan pakai kamera HP bawaan
tanpa library JS) · **Link WhatsApp gratis** (`wa.me`, tanpa API berbayar — kirim
link persetujuan ke Waka & surat ke siswa) · **Tahun Ajaran & Kenaikan Kelas**
(naik tingkat X→XI→XII, XII lulus, kelas/siswa tahun lama diarsipkan — lihat
`spec.md` §E).

Ketiga fitur **wajib** dari guru pembimbing sudah selesai semua.

Aturan alur detail: `docs/spec.md`.

## Backlog (NANTI, jangan sekarang)

- **Guru pengganti & tukar jam** — sengaja **paling akhir**, cuma dikerjakan kalau
  audit semua role (guru → pengurus kelas → waka → satpam) sudah selesai duluan
  dan masih ada sisa waktu. Urutan lengkap: `docs/halaman.md` §"Urutan saran".
- Notifikasi buat Pengurus Kelas/Waka/Satpam (siapa dapat apa — sudah
  dirancang, lihat `spec.md` §G). **Versi Guru sudah jalan** (2026-09-13,
  lebih cepat dari rencana atas permintaan langsung): jurnal diminta revisi →
  notif ke guru; dispensasi diputuskan Waka → notif ke guru piket pengaju.
  Lihat `docs/halaman.md` bagian Guru.
- Rekap jurnal per guru (portofolio mengajar)
- SMTP produksi

## Keputusan tim (dulu "tanya guru pembimbing", sekarang diputuskan sendiri)

Sesuai arahan: 3 keputusan ini bagian dari kreativitas tim, boleh diputuskan
sendiri asal ada alasan & berguna.

- **Boleh > 1 pengurus per kelas?** → **Tidak, 1 pengurus per kelas.** Akunnya
  sendiri boleh dipakai login di banyak HP sekaligus (bawaan Laravel, tidak
  ada pembatasan device) — itu sudah cukup buat kebutuhan "dipegang beberapa
  pengurus", tanpa perlu multi-akun per kelas yang malah bikin data ganda.
- **N3 "buku piket ketertiban"** (siswa telat gerbang pagi) → **Masuk
  jurnalkita**, tabel terpisah dari `absensis` (`catatan_terlambats`) karena
  beda konsep (telat masuk sekolah vs telat/tidak hadir di jam pelajaran).
  Dicatat satpam, ikut muncul di Rekap Siswa & rekap Wali Kelas.
- **Detail akun waka** → **Tetap akun mandiri** (bukan turunan dari data guru),
  sama seperti admin/satpam. Alasannya: Waka Kesiswaan di sekolah ini murni
  tugas struktural (bukan mengajar mata pelajaran lewat sistem ini), jadi
  tidak perlu dipaksa punya data `Guru`. Kalau nanti ternyata Waka-nya juga
  mengajar, tinggal dibuatkan akun guru terpisah — dua akun beda kebutuhan.

## Analisis alur kerja per peran

Lihat `docs/alur-kerja-dan-analisis-kebutuhan.md` — alur kerja nyata tiap peran
(guru, piket, waka, pengurus kelas, satpam, admin) dicocokkan sama fitur yang ada,
plus 3 temuan baru (rekap kehadiran siswa, rekap jurnal guru, buku piket ketertiban).
