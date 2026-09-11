# Pembagian Kerja — Halaman Tambahan

Buat Zahwa, Vara, dan Dude. **Satu orang pegang satu area** biar nggak tabrakan pas merge.
Kode tugas (Z1, V2, dst) dipakai buat ngobrol di grup — "udah kelar Z1?"
Daftar halaman lengkap ada di `docs/halaman.md`.

| Siapa | Area | Folder yang disentuh |
|---|---|---|
| **Zahwa** | Guru | `resources/views/guru/`, `dashboards/guru.blade.php`, `Controllers/Guru/` |
| **Vara** | Pengurus Kelas | `resources/views/sekretaris/`, `dashboards/sekretaris.blade.php`, `Controllers/Sekretaris/` |
| **Dude** | Waka + halaman error | `dashboards/waka.blade.php`, `resources/views/errors/` |

---

## Cara mulai

```bash
git pull
git checkout -b fitur/<areamu>       # zahwa: fitur/guru-jadwal
                                     # vara : fitur/kelas-pengurus
                                     # dude : fitur/waka-error

composer install
npm install
php artisan migrate:fresh --seed   # isi database contoh
npm run dev                         # biarkan jalan sambil ngoding
php artisan serve
```

Akun buat testing (semua sandinya `password`):

| Peran | Email |
|---|---|
| Admin | `admin@jurnalkita.test` |
| Guru (juga piket) | `guru1@jurnalkita.test` |
| Pengurus kelas | `kelas1@jurnalkita.test` |
| Waka | `waka@jurnalkita.test` |

---

## Aturan main

1. **Jangan edit file di luar areamu.** Butuh banget? Bilang dulu di grup.
2. File di `resources/views/components/` itu **milik bersama** — jangan diubah sendiri.
3. Pakai komponen yang udah ada, jangan bikin HTML dari nol. Lihat contoh di tiap tugas.
4. Kelar 1 halaman → langsung PR. Jangan nunggu semua kelar.
5. Mentok lebih dari 30 menit → tanya di grup. Nggak usah sungkan.

### Sebelum PR, wajib:

```bash
php artisan test          # harus hijau semua
./vendor/bin/pint --dirty # rapihin format kode
npm run build
```

---

## 📦 Zahwa — Guru

### Z1. Kartu shortcut di beranda guru

Kalau guru lagi kena jadwal piket hari ini, munculkan 2 kartu tombol:
**"Piket Hari Ini"** dan **"Ajukan Dispensasi"**.

- **Ubah:** `resources/views/dashboards/guru.blade.php`
- **Contek:** `resources/views/dashboards/sekretaris.blade.php` (bentuk kartunya)
- Di file itu udah ada variabel `$piketHariIni` (true/false), tinggal dipakai.
- Route tujuan: `route('piket.index')` dan `route('dispensasi.create')`

**Selesai kalau:**
- [ ] Login `guru1@jurnalkita.test` → kartu muncul di beranda
- [ ] Kartu **tidak** muncul buat guru yang hari itu nggak piket
- [ ] Tombol diklik → masuk ke halaman yang benar
- [ ] Di HP 1 kolom, di desktop 2 kolom

---

### Z2. Halaman "Jadwal Mengajar Saya"

Sekarang guru cuma bisa lihat jadwal **hari ini** di beranda. Bikin halaman
jadwal **seminggu**, dikelompokkan per hari (Senin → Jumat).

- **Buat:** `resources/views/guru/jadwal.blade.php`
- **Buat:** `app/Http/Controllers/Guru/JadwalController.php`
- **Ubah:** `routes/web.php` (masukkan ke grup `Route::middleware('role:guru')`)
- **Ubah:** `config/navigation.php` → tambah menu di bagian `'guru'`
- **Contek:** `resources/views/guru/piket.blade.php` (pola kelompok-per-hari)

Controller-nya kira-kira begini:

```php
public function index(): View
{
    $guru = auth()->user()->guru ?? abort(403);

    $jadwalPerHari = $guru->jadwals()
        ->with('mapel', 'kelas')
        ->orderBy('jam_ke_mulai')
        ->get()
        ->groupBy('hari');

    return view('guru.jadwal', compact('jadwalPerHari'));
}
```

Nama hari ambil dari `config('akademik.hari')` → `['senin' => 'Senin', ...]`
supaya urutannya benar dan tulisannya seragam.

**Selesai kalau:**
- [ ] Buka `/guru/jadwal` → tampil jadwal seminggu
- [ ] Dikelompokkan per hari, urut Senin → Jumat
- [ ] Dalam satu hari, urut dari jam ke- paling kecil
- [ ] Cuma jadwal **guru yang login**, bukan punya guru lain
- [ ] Hari yang kosong tidak ditampilkan (atau ditulis "tidak ada jadwal")
- [ ] Kalau guru belum punya jadwal sama sekali → muncul `<x-ui.empty>`
- [ ] Menu "Jadwal" muncul di navigasi bawah (HP) dan sidebar (desktop)

**Bonus kalau cepat kelar:** klik nama siswa di halaman presensi → muncul rekap
kehadiran siswa itu (hadir/sakit/izin/alpha).

---

## 📦 Vara — Pengurus Kelas

### V1. Daftar Siswa Sekelas

Halaman tabel **read-only** (tidak ada tambah/ubah/hapus) berisi teman sekelas.
Kolom: **No. Absen · Nama · NIS · Jabatan**.

- **Buat:** `resources/views/sekretaris/kelas.blade.php`
- **Buat:** `app/Http/Controllers/Sekretaris/KelasController.php`
- **Ubah:** `routes/web.php` (grup `role:siswa` yang ada `prefix('sekretaris')`)
- **Ubah:** `config/navigation.php` → bagian `'sekretaris'`
- **Contek:** `resources/views/admin/siswa/index.blade.php` — ambil bagian
  `<x-admin.table>` nya saja, jangan tombol aksinya.

Cara ambil kelas si pengurus (method-nya sudah ada di model User):

```php
$kelas = auth()->user()->kelasSekretaris() ?? abort(403);
$siswas = $kelas->siswas()->orderBy('no_absen')->get();
```

**Selesai kalau:**
- [ ] Login `kelas1@jurnalkita.test` → buka `/sekretaris/kelas`, tabel tampil
- [ ] Urut berdasarkan no. absen
- [ ] Yang jabatannya `pengurus` dikasih badge (pakai `<x-ui.status-badge>`)
- [ ] **Cuma siswa kelasnya sendiri** — nggak bocor ke kelas lain
- [ ] Tidak ada tombol ubah/hapus sama sekali
- [ ] Di HP tabel bisa digeser ke samping, nggak bikin halaman melar

---

### V2. Jadwal Pelajaran Kelas

Jadwal pelajaran kelasnya sendiri, **seminggu**, dikelompokkan per hari.
Tampilkan: jam ke-, mata pelajaran, guru, ruang.

- **Buat:** `resources/views/sekretaris/jadwal.blade.php`
- **Ubah:** `KelasController` (tambah method baru) + `routes/web.php` + `config/navigation.php`
- **Contek:** `resources/views/guru/piket.blade.php`

```php
$jadwalPerHari = $kelas->jadwals()
    ->with('mapel', 'guru')
    ->orderBy('jam_ke_mulai')
    ->get()
    ->groupBy('hari');
```

**Selesai kalau:**
- [ ] Buka `/sekretaris/jadwal` → tampil jadwal seminggu
- [ ] Urut Senin → Jumat, dalam sehari urut jam ke-
- [ ] Cuma jadwal kelasnya sendiri
- [ ] Ada nama guru pengajarnya
- [ ] Kalau belum ada jadwal → `<x-ui.empty>`

**Bonus kalau cepat kelar:** rekap kehadiran sekelas bulan ini
(per siswa: berapa kali hadir/sakit/izin/alpha).

---

## 📦 Dude — Waka + Halaman Error

### D1. Halaman error custom

Sekarang kalau salah ketik URL munculnya halaman putih polos bawaan Laravel.
Bikin versi yang sesuai desain aplikasi.

- **Buat folder:** `resources/views/errors/`
- **Buat file:** `404.blade.php`, `403.blade.php`, `419.blade.php`, `500.blade.php`
- **Contek:** `resources/views/components/layouts/guest.blade.php` (buat pembungkusnya)

Isinya cukup: ikon besar, kode error, penjelasan singkat bahasa Indonesia,
tombol "Kembali ke Beranda".

| File | Judul | Penjelasan |
|---|---|---|
| `404` | Halaman tidak ditemukan | Alamat yang kamu buka nggak ada. |
| `403` | Tidak punya akses | Halaman ini bukan untuk peranmu. |
| `419` | Sesi berakhir | Kamu terlalu lama diam. Silakan muat ulang. |
| `500` | Ada gangguan | Terjadi kesalahan di sistem. Coba lagi nanti. |

**Selesai kalau:**
- [ ] Buka `http://127.0.0.1:8000/halaman-ngawur` → muncul 404 versi baru
- [ ] Keempat halaman pakai warna & font yang sama dengan aplikasi
- [ ] Ada tombol balik ke beranda yang jalan
- [ ] Rapi di HP maupun desktop
- [ ] Test cepat: buka URL ngawur buat 404

> ⚠️ Halaman **500** nggak akan kelihatan selama `APP_DEBUG=true` di `.env` —
> yang muncul halaman error Laravel yang detail itu. Buat ngetes, ubah sementara
> jadi `APP_DEBUG=false`, terus balikin lagi.

---

### D2. Dashboard Waka

Sekarang isinya cuma 1 kartu, kosong banget. Tambahkan **statistik dispensasi
bulan ini** + **5 pengajuan terbaru**.

- **Ubah:** `resources/views/dashboards/waka.blade.php`
- **Contek:** `resources/views/admin/dashboard.blade.php` (kartu statistiknya)
  dan `resources/views/dispensasi/index.blade.php` (daftar kartunya)

Statistik yang ditampilkan (bulan berjalan):

| Kartu | Hitungannya |
|---|---|
| Menunggu keputusan | `status_piket = approved` **dan** `status_waka = pending` |
| Disetujui bulan ini | `status_akhir = approved` |
| Ditolak bulan ini | `status_akhir = rejected` |

```php
use App\Models\Dispensasi;

$bulanIni = fn () => Dispensasi::whereMonth('tanggal', now()->month)
    ->whereYear('tanggal', now()->year);

$disetujui = $bulanIni()->where('status_akhir', 'approved')->count();
```

**Selesai kalau:**
- [ ] Login `waka@jurnalkita.test` → 3 kartu statistik tampil dengan angka benar
- [ ] Angkanya cuma menghitung **bulan ini**, bukan semua data
- [ ] Ada daftar 5 pengajuan terbaru, tiap baris bisa diklik ke halaman detail
- [ ] Kalau belum ada data sama sekali → `<x-ui.empty>`, bukan halaman kosong
- [ ] Kartu: 1 kolom di HP, 3 kolom di desktop

**Bonus kalau cepat kelar:** halaman **Detail Kelas** buat admin — satu halaman
berisi wali kelas + daftar siswa + jadwal seminggu.

---

## Kalau mentok

1. Cari halaman yang mirip di folder `resources/views/`, lihat cara kerjanya.
2. `php artisan route:list` → lihat semua route yang ada.
3. Masih bingung lebih dari 30 menit → tanya di grup. Serius, nggak apa-apa.
