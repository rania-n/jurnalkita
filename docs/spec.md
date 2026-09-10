# Spesifikasi Alur (dari guru pembimbing)

Aturan bisnis detail. Prioritas & pembagian modul: `docs/roadmap.md`.

---

## A. Dispensasi

### Alur
1. **Guru piket** yang mengisi form dispensasi (bukan siswa / pengurus kelas).
2. Submit → sistem kirim **link WhatsApp ke Waka** berisi tautan setujui/tolak
   (signed URL, tanpa perlu login — Waka jarang buka web).
3. Waka **Setujui**:
   - Presensi siswa untuk jam terkait otomatis jadi **"dispensasi" + keterangan**.
   - Surat dispensasi dikirim ke: **siswa** (WA), **guru piket** (sistem), **guru mapel** terkait (sistem).
4. Waka **Tolak**: alur berhenti, guru piket dapat notifikasi status "ditolak".

### Surat dispensasi
- Isi: keterangan / konfirmasi + **QR code**.
- QR **berganti tiap ±10 detik** (anti-screenshot).
- **Satpam scan QR** → muncul nama siswa + status "dispensasi disetujui" (atau tidak valid).
  Dipakai saat siswa dispen perlu keluar sekolah.

### Catatan implementasi (fase lanjut)
- QR rotating = kode `HMAC(secret_dispensasi, floor(waktu/10))`. Endpoint scan satpam
  menghitung ulang & cek status approve.
- Perlu halaman/peran **satpam** (scan). WA = integrasi API (Fonnte/Wablas/dsb).

---

## B. Jurnal Guru

### Saat mengisi
- **Tanggal**: otomatis hari ini.
- **Jam ke- (mulai)**: otomatis dikunci ke JP yang sedang berjalan menurut jam sekarang.
  Contoh: buka jam 07:17, JP1 = 07:00–07:30 → otomatis JP1.
- **Jam ke- (selesai)**: bebas dipilih guru.

### Presensi siswa
- Default semua **hadir**.
- Siswa yang punya **dispensasi disetujui untuk jam itu** → otomatis **"dispensasi" + keterangan**.
  - Berlaku **per jam**: siswa bisa "hadir" di jurnal guru A (JP1) tapi "dispensasi" di
    jurnal guru B (JP3), sesuai rentang waktu dispensasinya.
- Guru boleh **ubah manual** (dispensasi = default pada waktunya, seperti default hadir).
- Tiap siswa ada kolom **catatan** opsional yang bisa diisi guru.

### Setelah jurnal dikirim
- Jurnal masuk ke **pengurus kelas** (sekretaris) kelas tsb.
- Sekretaris memvalidasi materi & presensi:
  - Sesuai → **terverifikasi**.
  - Tidak sesuai → kirim **request perubahan** ke guru (status **"revisi"** + catatan).

### Sekretaris mengisi jurnal menggantikan guru
- Kalau guru menugaskan lewat WA & tidak sempat buka web, sekretaris boleh mengisi jurnal.
- **Hanya untuk status "tugas luar" / "tidak hadir"** — tidak boleh "hadir".

---

## C. Dampak ke skema (tambahan yang diperlukan)

| Tabel | Perubahan |
|---|---|
| `dispensasis` | `dibuat_oleh_id` = guru piket (hapus jalur pengajuan siswa); `token` (link approve + QR); `disetujui_pada`; `keterangan` |
| `absensis` | tambah `dispensasi_id?` (FK) supaya keterangan otomatis nyambung |
| `jurnals` | tambah `diisi_oleh_id` (guru sendiri / pengurus kelas) |
| peran | tambah **satpam** + halaman scan |

---

## D. Realistis untuk MVP vs lanjut

**MVP (kerjakan):**
- Jurnal: auto tanggal + auto JP-lock, jam selesai custom.
- Presensi: default hadir + auto-dispensasi pada waktunya + catatan per siswa.
- Sekretaris: verifikasi jurnal + request perubahan + isi jurnal pengganti (tugas luar / tidak hadir).
- Dispensasi: diisi guru piket → approve Waka **di aplikasi** (link WA = bonus kalau sempat).

**Lanjut / backlog (`docs/scope.md`):**
- Integrasi WhatsApp (link approve, kirim surat).
- Surat dispensasi PDF + QR rotating + halaman scan satpam.

---

## E. Tahun Ajaran / Kenaikan Kelas (backlog, penting)

- Perlu **setting tahun ajaran aktif** (mis. "2026/2027").
- Saat naik tahun: semua kelas naik tingkat (X→XI→XII), XII lulus, data tahun lama
  **diarsipkan** (tetap bisa dilihat, tidak ikut daftar aktif).
- Kemungkinan: kolom `tahun_ajaran` di kelas/siswa/jadwal + scope "aktif" + halaman arsip.
- Belum dikerjakan — cukup dicatat dulu.

## F. Jam Pelajaran — kategori dinamis

- Sekarang kategori tetap: Senin–Kamis / Jumat / Khusus.
- Nanti bisa **tambah kategori sendiri** (mis. "Ramadhan", "Ujian"). Butuh tabel kategori
  terpisah. Backlog.
