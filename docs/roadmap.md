# Roadmap jurnalkita

## Status modul

| # | Modul | Status | Isi |
|---|---|---|---|
| 1 | Fondasi & Auth | ✅ selesai | Migrasi 13 tabel, model, seeder, Breeze (login/daftar/reset/verify), role + approval, dashboard per peran |
| — | FE + Admin desktop | ✅ selesai | Layout admin (tabel + modal), FE semua peran, tombol back, container auth |
| 3 | Master Data (admin) | ✅ selesai | CRUD guru, kelas (nama otomatis), siswa, mapel, jadwal pelajaran, jadwal piket, jam pelajaran + validasi + audit log |
| 4 | Manajemen Akun | ✅ selesai | Buat akun (dari data / baru), setujui/tolak pendaftaran, kirim reset sandi via email |
| 5 | Jurnal & Absensi (guru) | ✅ selesai | Isi jurnal (auto tanggal + jam), absensi otomatis (default hadir / dispensasi), editor presensi + foto, riwayat + edit selama pending |
| 2 | Approval & Verifikasi | 🔨 hampir | 2b & 2c selesai, 2a dikerjakan teman |

## Modul 2 — 3 bagian

| Bagian | Status | Isi |
|---|---|---|
| 2a. Audit Log (halaman) | 🔨 dikerjakan teman | Halaman `/admin/audit-log` read-only + filter |
| 2b. Verifikasi Jurnal | ✅ selesai | Pengurus kelas (`/sekretaris/jurnal`) lihat jurnal kelasnya → setujui / minta revisi. Guru bisa memperbaiki jurnal saat diminta revisi (balik ke antre). Bisa isi jurnal pengganti (tugas luar / tidak hadir), auto terverifikasi |
| 2c. Dispensasi | ✅ selesai | Guru piket ajukan (tahap piket otomatis lolos karena pengaju = piket) → Waka setujui / tolak → status akhir. Saat disetujui, absensi siswa di jam terkait otomatis jadi "dispensasi" + catatan |

**Catatan alur dispensasi:** sesuai spec guru, dispensasi **diisi** guru piket lalu langsung ke Waka —
tidak ada langkah "piket menyetujui" terpisah. Kolom `status_piket` tetap ada (default `approved`
saat diajukan) untuk jaga-jaga bila nanti perlu.

## Urutan

```
1  ──►  3, 4, 5 (paralel)  ──►  2
                                ├─ 2a (audit log)  — mandiri, bisa kapan saja
                                ├─ 2b (verifikasi jurnal) — butuh Modul 5 ✅
                                └─ 2c (dispensasi) — mandiri
```

Modul 2b & 2c sudah bisa dikerjakan sekarang (Modul 5 selesai).

## Setelah Modul 2 = MVP backend selesai

Sisanya masuk **backlog** (`docs/scope.md`): integrasi WhatsApp, surat dispensasi PDF + QR,
peran satpam, ekspor laporan piket, tahun ajaran / kenaikan kelas, notifikasi.

## Aturan main

- Kolom waktu: standar Laravel (`created_at` + `SoftDeletes`). Unik: `Rule::unique()->withoutTrashed()`.
- Form data guru/siswa **tanpa password**; buat akun = langkah terpisah.
- `MAIL_MAILER=log` untuk dev (email masuk ke `storage/logs`).
- Branch per modul, PR di-review. Jangan push tanpa izin.
- Alur detail: `docs/spec.md`.

## Akun demo (password: `password`)

`admin@jurnalkita.test` · `guru1@jurnalkita.test` · `kelas1@jurnalkita.test` · `waka@jurnalkita.test`
