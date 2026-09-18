<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JadwalWaka;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Data ASLI SMKN 1 Boyolangu, semester ganjil 2026/2027.
 *
 * Sumber: PDF "JADWAL KELAS SEMESTER GANJIL 2026-2027 FIX" (48 kelas X & XI,
 * kelas XII tidak ada jadwal karena PKL) + docs/data-asli/piket-per-hari.md
 * (ringkasan piket KBM & piket Waka bulan September 2026).
 *
 * database/seeders/data/jadwal-asli.json adalah hasil transkripsi PDF tsb yang
 * SUDAH dibersihkan lewat pipeline terpisah sebelum dipakai di sini:
 *  - 8 batch transkripsi (vision, per halaman) digabung & dedup nama mapel/guru
 *    (menyatukan variasi tanda baca gelar, mis. "S.Pd" vs "S.Pd.").
 *  - Rentang jam pelajaran (jam_ke_mulai/selesai) DIVERIFIKASI ULANG lewat deteksi
 *    garis tabel presisi-piksel (render PDF 300dpi, cari garis grid asli),
 *    bukan cuma estimasi visual -- ini mengoreksi ~421 dari 970 baris jadwal yang
 *    tadinya salah taksir batas kolom, dan menghilangkan SEMUA 58 "bentrok jadwal
 *    guru" yang tadinya terdeteksi (semuanya ternyata cuma salah baca batas JP,
 *    bukan bentrok asli -- setelah dikoreksi, 0 bentrok tersisa).
 *  - 5 nama (Erwan Septiono/Septiyono, Fitria Renyasari/Renytasari, Hendro
 *    Suwigyo/Suwignyo, Risqi Nur Imana/Imama, Danang Anjar Hynwanto/Hymawanto)
 *    disatukan manual -- beda ejaan 1-2 huruf antara transkripsi jadwal & piket,
 *    hampir pasti orang yang sama (lihat laporan akhir tugas untuk detail).
 *
 * File JSON ini adalah SUMBER KEBENARAN datanya; kelas ini murni "loader" yang
 * menerjemahkannya jadi Eloquent record + resolve nama -> id.
 */
class DataAsliSeeder extends Seeder
{
    /** @var array<string,int> nama mapel -> id */
    private array $mapelByName = [];

    /** @var array<string,int> nama guru (persis seperti di JSON) -> id gurus */
    private array $guruByName = [];

    /** @var array<string,int> nama kelas -> id kelas */
    private array $kelasByName = [];

    /** @var array<string,int> nama waka -> user_id (buat link Guru ke akun waka) */
    private array $wakaUserIdByName = [];

    /** @var string[] local-part email yang sudah dipakai (dari akun demo + yang baru dibuat di sini) */
    private array $usedSlugs = [];

    public function run(TahunAjaran $tahunAjaran, Collection $kelasDemo): void
    {
        $path = __DIR__.'/data/jadwal-asli.json';
        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $this->usedSlugs = User::pluck('email')->map(fn ($e) => Str::before($e, '@'))->all();

        $this->seedMapel($data['mapels_new']);
        $this->seedWaka($data['waka']);
        $this->seedGurus($data['gurus'], $data['guru_demo_reuse']);
        $this->seedKelas($data['kelas_new'], $kelasDemo, $data['kelas_reuse_names'], $tahunAjaran->id);
        $this->seedJadwal($data['jadwal']);
        $this->seedPiketKbm($data['piket_kbm']);
        $this->seedSiswaAsli();
    }

    /**
     * Data siswa ASLI per kelas -- satu file JSON per kelas di data/siswa/
     * (mis. data/siswa/xi-rpl-2.json), ditambahkan manual tiap kali ada
     * daftar absen asli baru dari user. Absen #1 di tiap file otomatis jadi
     * pengurus kelas (akun login dibuat) -- konvensi yang sama dipakai di
     * seluruh app buat pengurus kelas.
     *
     * File yang kelasnya BELUM ada di DB (mis. kelas XII -- nggak ikut
     * jadwal-asli.json karena XII lagi PKL jadi nggak punya jadwal) otomatis
     * dibuatkan Kelas-nya sendiri dari metadata di JSON ini (tingkat/jurusan/
     * nomor/wali_nama). Wali dicocokkan by name ke Guru yang udah ada --
     * nggak bikin akun Guru baru di sini, biar nggak nambah akun yang nggak
     * ada dasarnya (kalau nggak ketemu, wali_id dibiarkan null).
     */
    public function seedSiswaAsli(): void
    {
        foreach (glob(__DIR__.'/data/siswa/*.json') as $path) {
            $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

            $kelas = Kelas::where('nama', $data['kelas'])->first();
            if (! $kelas) {
                $kelas = Kelas::create([
                    'nama' => $data['kelas'],
                    'tingkat' => $data['tingkat'],
                    'jurusan' => $data['jurusan'],
                    'nomor' => $data['nomor'] ?? null,
                    'wali_id' => ! empty($data['wali_nama']) ? $this->cariGuruByNama($data['wali_nama']) : null,
                    'tahun_ajaran_id' => TahunAjaran::aktif()?->id,
                ]);
            }

            // Idempoten -- kalau kelas ini udah ada siswanya (mis. dari re-run
            // atau data manual sebelumnya), jangan dobel.
            if ($kelas->siswas()->exists()) {
                continue;
            }

            foreach ($data['siswa'] as $i => $s) {
                $noAbsen = $i + 1;
                $isPengurus = $noAbsen === 1;

                $user = $isPengurus ? User::create([
                    'name' => $s['nama'],
                    'email' => $this->emailFor($s['nama']),
                    'email_verified_at' => now(), 'password' => Hash::make('password'),
                    'role' => 'siswa', 'status' => 'approved',
                ]) : null;

                Siswa::create([
                    'user_id' => $user?->id,
                    'kelas_id' => $kelas->id,
                    // NIS asli (dari NISS di data sekolah, BUKAN NISN -- dua
                    // nomor itu beda) kalau ada -- fallback nomor acak unik
                    // cuma buat siswa yang NIS-nya belum diterbitkan sekolah
                    // (biasanya kelas X yang baru masuk).
                    'nis' => ! empty($s['nis']) ? $s['nis'] : fake()->unique()->numerify('2025########'),
                    'nama' => $s['nama'],
                    'jenis_kelamin' => $s['jenis_kelamin'],
                    'no_absen' => $noAbsen,
                    'jabatan' => $isPengurus ? 'pengurus' : 'anggota',
                ]);
            }
        }
    }

    /**
     * Wali kelas dari data/siswa/*.json (CSV presensi terbaru) ditulis beda
     * ejaan tipis (1-2 huruf) atau beda format gelar akademik ("S.E" vs "SE"
     * vs "S. E") dibanding nama Guru yang sama persis di tabel guru (dari
     * jadwal-asli.json, sumber lain) -- sama persis kasusnya kayak "5 nama
     * disatukan manual" yang udah didokumentasikan di atas buat guru/piket.
     * Dicek satu-satu manual pas import data siswa (2026-09).
     */
    private const ALIAS_WALI = [
        'Erna Qoriah, S.Pd' => 'Erna Qoriah, S.E.',
        'Retno Widiyastuti,S.Pd' => 'Retno Widyastuti, S.Pd., M.Pd',
        'Astra Bela Flamboyan, S.Psi' => 'Astra Bella Flamboyan, S.Psi',
        'Diana Hartanti, S.T' => 'Diana Hartanti, S.T., M.Pd',
        'Arvia Rienitasary, S.Pd' => 'Arvia Rienetasary, S.Pd',
        'Risqi Nur Imama, S.ST,Par' => 'Risqi Nur Imana, S.Tr.Par',
        'Indayah, S.Pd' => 'Indayah, S.Pd., M.Pd',
        'Elysa Yuli Nuraini, S.Si' => "Elysa Yuli Nur'aini, S.Si",
        'Fitria Dyah Ayu Hartati, S.Pd' => 'Fitria Diah Ayu Hartati, S.Pd',
        'Siswanti Purwaningsih, St' => 'Siswanti Purwaningsih, S.T., M.Pd',
        'Fitria Renytasari, S.Pd' => 'Fitria Renyasari, S.Pd',
        // "Karminah, S.Pd" (wali XI BD 3 di CSV) SENGAJA nggak ada aliasnya --
        // nggak ada guru dengan nama itu/mirip sama sekali di tabel guru,
        // biarin wali_id null daripada nebak salah.
    ];

    /**
     * Cocokkan nama guru longgar -- nggak peduli kapital, dan titik/koma/
     * spasi dibuang SEPENUHNYA (bukan cuma dirapikan), soalnya gelar
     * akademik ditulis beda-beda banget formatnya antar sumber data
     * ("S.E" vs "SE" vs "S. E" vs "S,E"). Alias di atas dicek duluan buat
     * nama yang ejaan intinya (bukan cuma gelar) beda tipis.
     */
    private function cariGuruByNama(string $nama): ?int
    {
        $normal = fn (string $s) => mb_strtolower(preg_replace('/[.,\s]+/u', '', $s));
        $target = $normal(self::ALIAS_WALI[$nama] ?? $nama);

        return Guru::all()->first(fn ($g) => $normal($g->nama) === $target)?->id;
    }

    private function seedMapel(array $mapelsNew): void
    {
        foreach ($mapelsNew as $m) {
            Mapel::create(['kode' => $m['kode'], 'nama' => $m['nama']]);
        }

        // keyBy nama mencakup 5 mapel demo (MAT/BIN/BIG/PBO/PWL) yang sudah ada
        // sebelumnya + semua yang baru saja dibuat -- termasuk Matematika/Bahasa
        // Indonesia/Bahasa Inggris yang SENGAJA tidak dibuat ulang di atas karena
        // namanya persis sama dengan mapel demo (dipakai ulang, bukan duplikat).
        $this->mapelByName = Mapel::pluck('id', 'nama')->all();
    }

    private function seedWaka(array $wakaList): void
    {
        foreach ($wakaList as $w) {
            $user = User::create([
                'name' => $w['nama'],
                'email' => $this->emailFor($w['nama']),
                'email_verified_at' => now(), 'password' => Hash::make('password'),
                'role' => 'waka', 'status' => 'approved',
                'no_hp' => '08'.fake()->numerify('##########'),
            ]);

            JadwalWaka::create(['user_id' => $user->id, 'hari' => $w['hari']]);

            $this->wakaUserIdByName[$w['nama']] = $user->id;
        }
    }

    private function seedGurus(array $gurus, array $guruDemoReuse): void
    {
        foreach ($gurus as $g) {
            $mapelUtamaId = $g['mapel_utama'] ? ($this->mapelByName[$g['mapel_utama']] ?? null) : null;

            if ($g['treatment'] === 'waka_link') {
                // Guru ini SALAH SATU dari 5 waka piket yang kebetulan juga mengajar
                // mapel di jadwal 48 kelas -- akunnya tetap role=waka (dibuat di
                // seedWaka di atas), cuma ditambahkan record Guru yang nempel ke
                // user_id akun waka itu, supaya dia bisa isi jurnal jadwal ngajarnya.
                $userId = $this->wakaUserIdByName[$g['nama']] ?? null;
            } else {
                $user = User::create([
                    'name' => $g['nama'],
                    'email' => $this->emailFor($g['nama']),
                    'email_verified_at' => now(), 'password' => Hash::make('password'),
                    'role' => 'guru', 'status' => 'approved',
                ]);
                $userId = $user->id;
            }

            $guru = Guru::create([
                'user_id' => $userId,
                'nip' => fake()->numerify('19#########'),
                'nama' => $g['nama'],
                'no_hp' => '08'.fake()->numerify('##########'),
                'mapel_utama_id' => $mapelUtamaId,
            ]);

            $this->guruByName[$g['nama']] = $guru->id;
        }

        // Guru demo yang namanya PERSIS sama dengan guru di data asli (cek dilakukan
        // di pipeline transkripsi) -- pakai record yang sudah ada, jangan buat baru.
        foreach ($guruDemoReuse as $namaDemo) {
            $existing = Guru::where('nama', $namaDemo)->first();
            if ($existing) {
                $this->guruByName[$namaDemo] = $existing->id;
            }
        }
    }

    private function seedKelas(array $kelasNew, Collection $kelasDemo, array $reuseNames, int $tahunAjaranId): void
    {
        // 4 kelas demo (X RPL 1, X RPL 2, XI RPL 1, XI TKJ 1) sudah dibuat seeder
        // utama lengkap dengan siswa & wali demo -- di sini cuma disambungkan biar
        // bisa dipakai resolve kelas_id pas bikin Jadwal, TIDAK dibuat Kelas baru.
        foreach ($reuseNames as $nama) {
            $existing = $kelasDemo->firstWhere('nama', $nama);
            if (! $existing) {
                throw new RuntimeException("Kelas demo yang diharapkan ada tidak ditemukan: {$nama}");
            }
            $this->kelasByName[$nama] = $existing->id;
        }

        foreach ($kelasNew as $k) {
            $waliId = $k['wali_nama'] ? ($this->guruByName[$k['wali_nama']] ?? null) : null;

            $kelas = Kelas::create([
                'nama' => $k['nama'],
                'tingkat' => $k['tingkat'],
                'jurusan' => $k['jurusan'],
                'nomor' => $k['nomor'],
                'wali_id' => $waliId,
                'tahun_ajaran_id' => $tahunAjaranId,
            ]);

            $this->kelasByName[$k['nama']] = $kelas->id;
        }
    }

    private function seedJadwal(array $jadwalList): void
    {
        foreach ($jadwalList as $j) {
            $kelasId = $this->kelasByName[$j['kelas_nama']] ?? null;
            $mapelId = $this->mapelByName[$j['mapel_nama']] ?? null;
            $guruId = $this->guruByName[$j['guru_nama']] ?? null;

            if (! $kelasId || ! $mapelId || ! $guruId) {
                throw new RuntimeException('Data asli: gagal resolve jadwal -> '.json_encode($j));
            }

            Jadwal::create([
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
                'guru_id' => $guruId,
                'ruang' => $j['ruang'],
                'hari' => $j['hari'],
                'jam_ke_mulai' => $j['jp_mulai'],
                'jam_ke_selesai' => $j['jp_selesai'],
            ]);
        }
    }

    private function seedPiketKbm(array $piketList): void
    {
        foreach ($piketList as $p) {
            $guruId = $this->guruByName[$p['nama']] ?? null;
            if (! $guruId) {
                throw new RuntimeException("Piket KBM: guru tidak ditemukan -> {$p['nama']}");
            }

            [$mulai, $selesai] = $p['shift'] === 'pagi' ? ['07:00', '11:00'] : ['11:00', '15:00'];

            JadwalPiket::create([
                'guru_id' => $guruId,
                'hari' => $p['hari'],
                'mulai' => $mulai,
                'selesai' => $selesai,
                'keterangan' => $p['keterangan'],
            ]);
        }
    }

    /**
     * Pola email: slug nama (huruf kecil, spasi jadi titik), gelar akademik
     * dibuang (semua yang muncul setelah koma pertama, atau prefiks "Dra./Drs."
     * di depan nama yang tidak pakai koma) -- lalu ditambah angka kalau ada
     * tabrakan nama depan yang sama.
     */
    private function emailFor(string $nama): string
    {
        $core = preg_replace('/^(Dra\.|Drs\.|Dr\.)\s+/i', '', trim($nama));
        $core = explode(',', $core)[0];
        $core = str_replace(["'", '.'], '', $core);

        $slug = (string) Str::of($core)->squish()->lower()->replace(' ', '.');
        $slug = preg_replace('/[^a-z0-9.]/', '', $slug);

        $base = $slug;
        $i = 2;
        while (in_array($slug, $this->usedSlugs, true)) {
            $slug = $base.$i;
            $i++;
        }
        $this->usedSlugs[] = $slug;

        return $slug.'@jurnalkita.test';
    }
}
