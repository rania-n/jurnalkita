# Roadmap jurnalkita — Backend MVP + Restrukturisasi per Role

> Dokumen kerja tim. FE dasar sudah jadi & ter-commit. Sekarang: backend MVP,
> dibagi per modul untuk 5 orang. Backlog non-MVP ada di `docs/scope.md`.

## Keputusan pondasi

- **Kolom waktu**: standar Laravel (`created_at`/`updated_at` + `SoftDeletes`).
  Unique yang mengabaikan data terhapus → `Rule::unique(...)->withoutTrashed()`.
- **Auth**: Laravel Breeze (Blade). View Breeze diganti desain Figma yang sudah ada.
- **Admin**: tabel desktop + modal (`x-layouts.admin`). Role lain: layout mobile-diperbesar.
- **Data ≠ Akun**: form "tambah data guru/siswa" tanpa password. Buat akun login = langkah terpisah.
- **Role** (`users.role`): `admin`, `guru`, `siswa`, `waka`.
  - piket = guru yang punya `jadwal_pikets` (bukan role).
  - sekretaris = `siswa` dengan `jabatan = 'pengurus'` / akun kelas (bukan role).
  - wali kelas = guru yang jadi `kelas.wali_id` (bukan role).
  - waka = akun dibuat admin.

## MVP — dikerjakan sekarang

- Auth: register (guru sendiri; akun kelas dibuat admin), login & reset via email,
  verifikasi email, **approval akun oleh admin**.
- Master data (admin, tabel desktop): guru, siswa, kelas, mapel, jam pelajaran,
  jadwal pelajaran, jadwal piket, guru–mapel.
- Buat akun login dari data guru/siswa.
- Jurnal mengajar (terikat jadwal) + absensi siswa (default hadir) + foto bukti.
- Verifikasi jurnal oleh sekretaris → status verifikasi / minta revisi.
- Dispensasi: ajukan → approve piket → approve waka → status akhir.
- Dashboard + header per role, middleware role.
- Audit log (pencatatan pasif + penampil read-only di admin).

## Fase 0 — Rapikan FE (sebelum backend)

- `x-layouts.app`: konten desktop mengisi area setelah sidebar (rata kiri, max-w ~880px),
  daftar kartu 2 kolom di `lg`, bagian form dikelompokkan kartu. Hilangkan "HP di tengah".
- Tombol Setujui/Tolak: gaya senada (sudah: dua-duanya border + warna lembut).
- Hapus `public/hot` bila muncul lagi (sisa `npm run dev`).
- Empty state di semua daftar.
- Placeholder `beranda`/`profil` → dashboard asli per role (di modul masing-masing).

## Skema database (migrasi fresh, standar Laravel)

Semua tabel: `id`, `timestamps()`, `softDeletes()` kecuali disebut lain.

| Tabel | Kolom inti |
|---|---|
| **users** | `name`, `email` unik, `email_verified_at`, `password`, `role` enum(admin,guru,siswa,waka), `status` enum(pending,approved,rejected) default pending |
| **gurus** | `user_id?` unik, `nip?`, `nama`, `no_hp?` |
| **kelas** | `nama`, `tingkat` enum(X,XI,XII), `jurusan`, `wali_id?`→gurus |
| **siswas** | `user_id?` unik, `kelas_id`→kelas, `nis` unik, `nama`, `jenis_kelamin` enum(L,P), `no_absen?`, `jabatan` enum(anggota,pengurus) default anggota |
| **mapels** | `kode` unik, `nama` |
| **guru_mapel** (pivot, tanpa softDeletes) | `guru_id`, `mapel_id` (unik berpasangan) |
| **jam_pelajarans** | `jam_ke`, `mulai` time, `selesai` time, `kategori` enum(senin_kamis,jumat,khusus), `keterangan?` |
| **jadwals** | `kelas_id`, `mapel_id`, `guru_id`, `guru_pendamping_id?`, `ruang`, `hari` enum(senin..jumat), `jam_ke_mulai`, `jam_ke_selesai` |
| **jadwal_pikets** | `guru_id`, `hari` enum(senin..jumat), `mulai` time?, `selesai` time?, `keterangan?` |
| **jurnals** | `jadwal_id`, `guru_id`, `tanggal` date, `jam_ke_mulai`, `jam_ke_selesai`, `status_guru` enum(hadir,tugas,tidak_hadir), `materi` text, `metode?`, `tugas_tambahan?`, `foto_bukti?`, `status_verifikasi` enum(pending,terverifikasi,revisi), `verifikator_id?`→siswas, `catatan_verifikasi?` |
| **absensis** (tanpa softDeletes) | `jurnal_id`, `siswa_id`, `status` enum(hadir,sakit,izin,alpha,dispensasi), `catatan?`; unik (jurnal_id,siswa_id) |
| **dispensasis** | `siswa_id`, `diajukan_oleh_id`→users, `tanggal` date, `jam_ke_mulai?`, `jam_ke_selesai?`, `alasan` text, `surat_path?`, `no_hp?`, `status_piket`/`status_waka`/`status_akhir` enum(pending,approved,rejected), `piket_id?`/`waka_id?`→users, `catatan_piket?`/`catatan_waka?` |
| **audit_logs** (hanya created_at) | `user_id?`, `aksi`, `deskripsi` text, `subject_type?`, `subject_id?`, `ip?` |

Breeze menambah `password_reset_tokens`, `sessions`. Migrasi & model & controller lama
(`kelas`,`siswas`,`mapels`,`jadwals`) **diganti** skema ini.

Tiap tabel wajib **factory + seeder** dengan data yang cocok dengan contoh di Blade.

## Struktur FE per role

```
resources/views/
  auth/         login, register-guru, register-kelas, forgot/reset/verify
  admin/        x-layouts.admin (desktop tabel): dashboard, users, guru/ siswa/ kelas/
                mapel/ jam-pelajaran/ jadwal/ jadwal-piket/, audit-log/
  guru/         x-layouts.app: dashboard, jurnal/ (riwayat+create+show), absensi, piket/
  sekretaris/   x-layouts.app: dashboard, verifikasi-jurnal/, dispensasi/
  waka/         x-layouts.app: dashboard, dispensasi/
  profil.blade.php
  components/   + x-layouts.admin, x-admin.table, x-modal, header role di x-layouts.app
```

Route: `Route::middleware(['auth','verified'])` → group `->prefix('admin')->middleware('role:admin')` dst.
Setelah login → redirect dashboard per role.

## Pembagian tim (5 orang: 2 kuat + 3 belajar)

Modul paling terkait erat / berisiko dipegang yang berpengalaman; sisanya CRUD berpola
sama, paralel. Yang berpengalaman review PR rekan sefase.

| # | Modul | Pemilik | Sifat |
|---|---|---|---|
| 1 | Fondasi & Auth — migrasi+model+seeder, Breeze+role+approval+middleware, `x-layouts.admin`, header role, restrukturisasi folder/route | Kuat #1 | Blocker, dahulukan |
| 2 | Approval & Verifikasi — dispensasi (piket→waka→akhir), verifikasi jurnal (sekretaris), audit log, dashboard piket/waka/sekretaris | Kuat #2 | State machine |
| 3 | Admin shell + Master Data A — `x-admin.table` + `x-modal`, CRUD kelas (pola acuan) → siswa, mapel | Belajar A | CRUD berulang |
| 4 | Master Data B + Buat Akun — CRUD jam pelajaran, jadwal pelajaran, jadwal piket (FK); alur "buat akun dari data" | Belajar B | CRUD + FK |
| 5 | Jurnal & Absensi (guru) — dashboard guru, form jurnal terikat jadwal, absensi default hadir, upload foto, riwayat + edit selama pending | Belajar C | Satu alur mandiri |

Urutan: #1 dulu → #3/#4/#5 paralel → #2 integrasi setelah model jurnal/dispensasi ada.
Bagian mekanis #1 (pindah folder view, seeder awal) boleh dibantu Belajar A/C.

## Detail modul — lihat `/Users/rania/.claude/plans/` atau bagian bawah dokumen ini

### 1. Fondasi & Auth
- `php artisan breeze:install blade`; ganti view auth dengan desain Figma.
- Semua migrasi + model (relasi, cast enum, SoftDeletes).
- `role` + `status` di users; middleware `role:*`; redirect dashboard per role; blokir login bila `status != approved`.
- Seeder: admin, guru/siswa/kelas/mapel/jadwal, 1 akun kelas, 1 waka, contoh jurnal+dispensasi.
- `x-layouts.admin`, header role, pindah folder view, rombak `routes/web.php`.
- Hapus model/controller/migrasi lama.

### 2. Approval & Verifikasi
- Dispensasi: ajukan (guru/akun kelas) → `approvePiket`/`rejectPiket` (guru kena piket) → `approveWaka`/`rejectWaka`. `status_akhir` dihitung dari dua status.
- Verifikasi jurnal: akun kelas lihat jurnal kelasnya yang `pending` → `terverifikasi` / `revisi` + catatan.
- Audit log: observer model kunci → `audit_logs`; halaman admin read-only + filter.
- Dashboard piket / waka / sekretaris.

### 3. Admin shell + Master Data A
- `x-admin.table` (kolom, baris, aksi, empty state) + `x-modal` + konfirmasi hapus.
- CRUD kelas (Controller resource + Form Request + view tabel + modal) sebagai pola → siswa, mapel.
- Form data tanpa password.

### 4. Master Data B + Buat Akun
- CRUD jam pelajaran (grup kategori, tambah/hapus baris), jadwal pelajaran (FK), jadwal piket (FK).
- Buat akun: baris tanpa `user_id` → tombol "Buat Akun" → modal (email, role otomatis, password sementara / email set-password) → set `user_id`.

### 5. Jurnal & Absensi
- Dashboard guru: `jadwals` hari ini milik guru + tombol "Isi Jurnal" per slot; badge piket.
- Form jurnal: pilih slot → simpan `jurnals` + auto-buat `absensis` semua siswa kelas (default hadir); ubah yang tidak hadir; upload `foto_bukti`.
- Riwayat: daftar jurnal guru + status; edit selama `pending`.

## Keputusan lintas modul

- `php artisan storage:link`; file di `storage/app/public/`.
- `MAIL_MAILER=log` untuk dev; SMTP asli menyusul.
- Policy per model (guru hanya edit jurnal sendiri & selama pending).
- Form Request per aksi; unik `->withoutTrashed()`.
- Seeder wajib per modul.
- Branch per modul; PR di-review. Jangan push tanpa persetujuan.

## Verifikasi end-to-end

1. `composer install && npm install && php artisan migrate:fresh --seed && npm run build`
2. `php artisan route:list` — group role ada `role:*`.
3. Login tiap role → dashboard masing-masing; `status=pending` ditolak.
4. Admin: CRUD tabel master lewat modal; buat akun dari data guru.
5. Guru: isi jurnal dari jadwal → absensi otomatis → upload foto → simpan.
6. Sekretaris: verifikasi jurnal.
7. Ajukan dispensasi → piket approve → waka approve → `status_akhir=approved`.
8. Admin → Audit Log tercatat.
9. `./vendor/bin/pint --test` bersih; `php artisan test` hijau.
