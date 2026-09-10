# Roadmap jurnalkita

## Status

- **FE**: hampir selesai. Auth + layout + semua layar peran (guru, sekretaris, waka)
  + admin desktop.
- **Backend**: fondasi jadi (migrasi 13 tabel, model, seeder, auth Breeze, role,
  approval, dashboard per peran). Belum: controller CRUD & alur approval.

## Peran (`users.role`)

`admin` · `guru` · `siswa` (akun kelas / sekretaris) · `waka`
- **piket** = guru yang punya jadwal piket (bukan peran).
- **wali kelas** = guru yang jadi `kelas.wali_id` (bukan peran).

## MVP yang tersisa (backend)

| # | Modul | Isi |
|---|---|---|
| 2 | Approval & Verifikasi | dispensasi piket→waka, verifikasi jurnal (sekretaris), audit log |
| 3 | Master Data A | CRUD kelas, siswa, mapel |
| 4 | Master Data B + Akun | CRUD jam pelajaran, jadwal, jadwal piket, guru; buat akun dari data |
| 5 | Jurnal & Absensi | form jurnal terikat jadwal + absensi + foto |

Modul 3/4/5 paralel. Modul 2 setelah model jurnal/dispensasi dipakai.

## Aturan main

- Kolom waktu: standar Laravel (`created_at` + `SoftDeletes`).
- Unik: `Rule::unique(...)->withoutTrashed()`.
- Form data guru/siswa **tanpa password**; buat akun = langkah terpisah.
- Storage: `php artisan storage:link`, file di `storage/app/public/`.
- Email: `MAIL_MAILER=log` untuk dev.
- Branch per modul, PR di-review. Jangan push tanpa izin.

## Akun demo (password: `password`)

`admin@jurnalkita.test` · `guru1@jurnalkita.test` · `kelas1@jurnalkita.test` · `waka@jurnalkita.test`

## Backlog

Lihat `docs/scope.md`.
