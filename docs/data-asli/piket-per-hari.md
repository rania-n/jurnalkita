# Piket KBM & Piket Waka — Dirangkum per Hari

Sumber: `~/Downloads/PIKET BULAN SEPTEMBER nuw20260828_10270412.pdf` (jadwal piket
bulan September 2026, 22 baris tanggal).

## Piket Waka — pola per hari (KONSISTEN penuh sebulan, tidak berubah)

| Hari | Waka bertugas |
|---|---|
| Senin | Setiyo Winarko, S.Pd |
| Selasa | Niken Hari Pratiwi, S.Psi., M.Pd |
| Rabu | Hardini Indahing Budi, S.E., M.Pd |
| Kamis | Hendro Suwigyo, ST |
| Jumat | Fajar Luthfianto, S.Pd |

Ini cocok langsung ke tabel `jadwal_wakas` (1 baris per hari, `user_id` merujuk
ke akun masing-masing). **Kelima nama ini butuh akun `role=waka` sendiri-sendiri**
(user minta akun terpisah per orang, bukan 1 akun bersama — beda dengan
pengurus kelas siswa yang boleh akun bersama).

## Piket KBM (petugas pagi/siang + koordinator) — DISEDERHANAKAN per hari

⚠️ **Catatan penting**: data asli di PDF sebenarnya rotasi 2 mingguan
(tim piket ganti-gantian tiap minggu genap/ganjil untuk hari yang sama —
misal Selasa minggu 1 beda tim sama Selasa minggu 2). Skema `jadwal_pikets`
di aplikasi ini cuma bisa nyimpen **1 tim per hari** (kolom `hari` doang,
nggak ada konsep minggu ganjil/genap). Jadi di bawah ini dipilih **kemunculan
pertama** tiap hari sebagai tim yang dipakai — bukan berarti data aslinya
cuma 1 tim, cuma keterbatasan skema saat ini. Boleh disebut ke user sebagai
temuan buat pengembangan lanjut (nambah kolom minggu genap/ganjil) kalau
suatu saat dibutuhkan presisi penuh.

| Hari | Petugas Pagi (07:00–11:00) | Koordinator Pagi | Petugas Siang (11:00–15:00) | Koordinator Siang |
|---|---|---|---|---|
| Selasa | Sulistyowati, SS; Wiwik Yuniarsih, S.Pd; Sri Kusumastuti, S.Pd | Lilik Suratmi, S.Pd | Kasmi, S.Pd., M.Pd; Siti Munawaroh, S.Kom.,M.Pd; Niken Dewi Hastika, S.Pd | Widodo, S.Pd |
| Rabu | Tutut Sriatin, S.Pd; Rika Okta Maulida, S.Ds.; Mufatiroh, S.Ag | Elyana Frisca Monica, S.Pd | Siswanti Purwaningsih, S.T., M.Pd; Shinta Indyar Shanty Susanto, S.Kom; Dhuana Putri Puspitasary, S.Pd | Erwan Septiono, S.Pd |
| Kamis | Yuni Jiastuti, S.Pd; Yuli Ratnasari, S.Pd; Agus Pramono, S.Sn | Danang Anjar Hynwanto, S.Pd | Risqi Nur Imana, S.Tr.Par; Luluk Munfarida, S.Pd; Tuhu Eries Kudori, S.Sn | Istiana Suhartati, S.T |
| Jumat | Arif Setyobudi, S.Pd; Sunarti, S.Pd; Isti Mufadah, S.Pd | Joko Priyanto, S.Kom | Dra. Hanik Pangestuti; Andri Krisdianto, SE.,M.Pd; Fitria Renyasari, S.Pd | Agung Yulianto, S.Pd |
| Senin | Septiani, S.Pd.,M.Pd; Martiin, S.Pd; Winarsih, S.Pd, M.Pd | Titik Sukmasari, S.Pd | Nurul Azizah, S.Pd; Rifkotin Na'imah, S.Pd; Dra. Susakti Yuharini | Lutfia Marsalina, S.Pd.I, M.Pd. |

`keterangan` field di `jadwal_pikets` dipakai buat nandain "Koordinator Piket
Pagi"/"Koordinator Piket Siang" vs biarin null buat petugas biasa.
