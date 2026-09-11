# jurnalkita

Sistem Jurnal & Absensi Guru — Laravel 13 + Tailwind v4.

## Setup setelah clone

```bash
composer install
composer setup        # bikin .env, key, database sqlite, migrate + seed, build asset
php artisan serve
```

Kalau `composer setup` gagal, jalankan manual:

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
npm install && npm run build
```

Saat ngoding, biarkan `npm run dev` jalan di terminal terpisah.

### Login gagal / halaman error?

Hampir selalu karena `.env` belum ada (APP_KEY kosong) atau DB belum di-migrate.
Ulangi `composer setup`.

### Pakai MySQL (bukan sqlite)

Edit `.env`: `DB_CONNECTION=mysql`, isi `DB_DATABASE/USERNAME/PASSWORD`, buat
database-nya, lalu `php artisan migrate:fresh --seed`.

## Akun demo (password: `password`)

| Email | Peran |
|---|---|
| `admin@jurnalkita.test` | Admin |
| `guru1@jurnalkita.test` | Guru (juga piket, hari tetap "senin") |
| `guru.biasa@jurnalkita.test` | Guru **tanpa** piket sama sekali |
| `guru.piket@jurnalkita.test` | Guru **dengan** piket — piketnya dipasang ke hari saat `--seed` dijalankan, jadi kartu "Piket Hari Ini" & Monitor Piket selalu ada isinya |
| `kelas1@jurnalkita.test` | Pengurus kelas |
| `waka@jurnalkita.test` | Waka |

## Dokumen

- `docs/roadmap.md` — rencana & pembagian modul
- `docs/scope.md` — MVP vs backlog
