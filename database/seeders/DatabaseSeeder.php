<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JadwalWaka;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Support\HariSekolah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------ Tahun Ajaran
        $tahunAjaran = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);

        // ---------------------------------------------------------------- Akun tetap
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 'approved',
        ]);

        $waka = User::create([
            'name' => 'Hariyadi, M.Pd', 'email' => 'waka@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'waka', 'status' => 'approved', 'no_hp' => '085648830046',
        ]);

        // Waka juga gantian shift kayak guru piket -- dua akun contoh, shift-nya
        // dipasang biar $waka (dipakai di banyak tempat lain) kebagian giliran HARI
        // SAAT SEEDING, jadi langsung kelihatan jalan mau di-seed hari apa pun.
        $waka2 = User::create([
            'name' => 'Retno Wulandari, S.Pd', 'email' => 'waka2@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'waka', 'status' => 'approved', 'no_hp' => '082330166057',
        ]);
        $hariIni = HariSekolah::hariIni() ?? 'senin';
        $hariLain = collect(['senin', 'selasa', 'rabu', 'kamis', 'jumat'])->reject(fn ($h) => $h === $hariIni)->values();
        JadwalWaka::create(['user_id' => $waka->id, 'hari' => $hariIni]);
        JadwalWaka::create(['user_id' => $waka->id, 'hari' => $hariLain[0]]);
        JadwalWaka::create(['user_id' => $waka2->id, 'hari' => $hariLain[1]]);
        JadwalWaka::create(['user_id' => $waka2->id, 'hari' => $hariLain[2]]);
        JadwalWaka::create(['user_id' => $waka2->id, 'hari' => $hariLain[3]]);

        $satpam = User::create([
            'name' => 'Slamet Riyadi', 'email' => 'satpam@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'satpam', 'status' => 'approved', 'no_hp' => '08970022883',
        ]);

        // ----------------------------------------------------------------- Mapel
        $mapels = collect([
            ['kode' => 'MAT', 'nama' => 'Matematika'],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris'],
            ['kode' => 'PBO', 'nama' => 'Pemrograman Berorientasi Objek'],
            ['kode' => 'PWL', 'nama' => 'Pemrograman Web dan Perangkat Bergerak'],
        ])->map(fn ($m) => Mapel::create($m));

        // Waka juga guru beneran (bisa isi jurnal ngajar sendiri, bukan cuma
        // approve dispensasi) -- tanpa ini $waka->guru null, ketolak 403
        // "Akun tidak terhubung ke data guru" begitu buka menu Jurnal. Sengaja
        // TIDAK dikasih Jadwal manual di sini (biar nggak numpuk/bentrok sama
        // jadwal asli 4 kelas demo yang diisi belakangan oleh DataAsliSeeder) --
        // halaman Isi Jurnal sudah punya tampilan kosong yang layak kalau
        // jadwal hari itu belum ada.
        Guru::create(['user_id' => $waka->id, 'nama' => $waka->name, 'mapel_utama_id' => $mapels->firstWhere('kode', 'BIN')->id]);
        Guru::create(['user_id' => $waka2->id, 'nama' => $waka2->name, 'mapel_utama_id' => $mapels->firstWhere('kode', 'MAT')->id]);

        // ----------------------------------------------------------------- Guru
        // Dulu ada 5 guru isian generik (Budi Santoso dkk) buat ngisi jadwal contoh
        // di 4 kelas demo -- udah dihapus, soalnya sekarang 4 kelas demo itu (X RPL
        // 1/2, XI RPL 1, XI TKJ 1) kebagian jadwal ASLI dari DataAsliSeeder, jadi
        // guru isian generik itu cuma nambah kebingungan (nama fiktif, jadwal
        // ngawur numpuk-numpuk). Winartin doang yang dipertahankan di sini karena
        // namanya PERSIS sama dengan guru di data asli (dia otomatis "jadi" guru
        // sungguhan begitu DataAsliSeeder jalan, bukan didobel).
        $guruData = [
            ['nama' => 'Winartin, S.Pd', 'mapel' => 'BIG', 'akun' => true, 'piket' => 'senin'],
        ];

        $gurus = collect($guruData)->map(function ($g) use ($mapels) {
            // Email ikut pola yang sama kayak guru data asli (nama depan.nama
            // belakang, tanpa gelar) -- bukan "guru1@" lagi, biar konsisten &
            // nggak keliatan beda perlakuan padahal dia guru sungguhan juga.
            $user = $g['akun'] ? User::create([
                'name' => $g['nama'],
                'email' => 'winartin@jurnalkita.test',
                'email_verified_at' => now(), 'password' => Hash::make('password'),
                'role' => 'guru', 'status' => 'approved',
            ]) : null;

            $guru = Guru::create([
                'user_id' => $user?->id,
                'nip' => fake()->numerify('19#########'),
                'nama' => $g['nama'],
                'no_hp' => '08'.fake()->numerify('##########'),
                'mapel_utama_id' => $mapels->firstWhere('kode', $g['mapel'])->id,
            ]);

            if ($g['piket']) {
                JadwalPiket::create([
                    'guru_id' => $guru->id, 'hari' => $g['piket'],
                    'mulai' => '07:00', 'selesai' => '12:00',
                ]);
            }

            return $guru;
        });

        // -------------------------------------------------------- Jam pelajaran
        // Sumber: docs/data-asli/jam-pelajaran.md (jadwal SMKN 1 Boyolangu asli,
        // versi 35 menit/JP Senin-Kamis yang dikonfirmasi berlaku sekarang).
        // Senin-Kamis cuma sampai JP10 (JP11-13 tidak dipakai -- makanya tidak
        // dibuat baris untuk itu). Jumat polanya beda & sampai JP13.
        $jpSeninKamis = [
            1 => ['07:00', '07:35'], 2 => ['07:35', '08:10'], 3 => ['08:10', '08:45'], 4 => ['08:45', '09:20'],
            // Istirahat 1: 09:20-09:40
            5 => ['09:40', '10:15'], 6 => ['10:15', '10:50'], 7 => ['10:50', '11:25'],
            // Istirahat 2: 11:25-13:30
            8 => ['13:30', '14:05'], 9 => ['14:05', '14:40'], 10 => ['14:40', '15:15'],
        ];
        $jpJumat = [
            1 => ['07:00', '07:30'], 2 => ['07:30', '08:00'], 3 => ['08:00', '08:30'], 4 => ['08:30', '09:00'], 5 => ['09:00', '09:30'],
            // Istirahat 1: 09:30-09:50
            6 => ['09:50', '10:20'], 7 => ['10:20', '10:50'], 8 => ['10:50', '11:20'],
            // Istirahat 2: 11:20-13:30
            9 => ['13:30', '13:55'], 10 => ['13:55', '14:20'], 11 => ['14:20', '14:45'], 12 => ['14:45', '15:10'], 13 => ['15:10', '15:35'],
        ];
        foreach ($jpSeninKamis as $jamKe => [$mulai, $selesai]) {
            JamPelajaran::create(['jam_ke' => $jamKe, 'mulai' => $mulai, 'selesai' => $selesai, 'kategori' => 'senin_kamis']);
        }
        foreach ($jpJumat as $jamKe => [$mulai, $selesai]) {
            JamPelajaran::create(['jam_ke' => $jamKe, 'mulai' => $mulai, 'selesai' => $selesai, 'kategori' => 'jumat']);
        }

        // ----------------------------------------------------------------- Kelas
        $kelas = collect([
            ['tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 1],
            ['tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 2],
            ['tingkat' => 'XI', 'jurusan' => 'RPL', 'nomor' => 1],
            ['tingkat' => 'XI', 'jurusan' => 'TKJ', 'nomor' => 1],
        ])->map(fn ($k, $i) => Kelas::create([
            ...$k,
            'nama' => "{$k['tingkat']} {$k['jurusan']} {$k['nomor']}",
            'wali_id' => $gurus->first()->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]));

        // ----------------------------------------------------------------- Siswa
        $kelas->each(function (Kelas $k, $ki) {
            for ($n = 1; $n <= 8; $n++) {
                $isPengurus = $n === 1;
                // Kelas ke-0 & 1: pengurus punya akun. Sisanya: pengurus tanpa akun
                // (biar bisa dites "Buat Akun dari data" di menu Manajemen Akun).
                $pengurusPunyaAkun = $isPengurus && $ki < 2;
                $jk = fake()->randomElement(['L', 'P']);

                $user = $pengurusPunyaAkun ? User::create([
                    'name' => 'Pengurus '.$k->nama,
                    'email' => 'kelas'.($ki + 1).'@jurnalkita.test',
                    'email_verified_at' => now(), 'password' => Hash::make('password'),
                    'role' => 'siswa', 'status' => 'approved',
                ]) : null;

                Siswa::create([
                    'user_id' => $user?->id,
                    'kelas_id' => $k->id,
                    'nis' => fake()->unique()->numerify('2026####'),
                    'nama' => fake()->name($jk === 'L' ? 'male' : 'female'),
                    'jenis_kelamin' => $jk,
                    'no_absen' => $n,
                    'jabatan' => $isPengurus ? 'pengurus' : 'anggota',
                ]);
            }
        });

        // Jadwal & jurnal contoh buat 4 kelas demo (X RPL 1/2, XI RPL 1, XI TKJ 1)
        // udah datang dari DataAsliSeeder (jadwal ASLI, bukan isian generik lagi) --
        // lihat bagian bawah file ini.

        // ---------------------------- Akun demo: guru tanpa piket vs guru piket hari ini
        // Dipisah jelas biar gampang dites/didemokan. Piket & jadwalnya dipasang ke HARI
        // SAAT SEEDING (bukan hari tetap), jadi kartu "Piket Hari Ini" dan halaman Monitor
        // Piket selalu ada isinya — mau di-seed hari apa pun.
        $hariIni = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? 'senin';

        $guruBiasaUser = User::create([
            'name' => 'Fajar Nugroho, S.Pd', 'email' => 'guru.biasa@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'approved',
        ]);
        $guruBiasa = Guru::create([
            'user_id' => $guruBiasaUser->id, 'nip' => fake()->numerify('19#########'),
            'nama' => 'Fajar Nugroho, S.Pd', 'no_hp' => '08'.fake()->numerify('##########'),
            'mapel_utama_id' => $mapels->first()->id,
        ]);
        Jadwal::create([
            'kelas_id' => $kelas[1]->id, 'mapel_id' => $mapels->first()->id, 'guru_id' => $guruBiasa->id,
            'ruang' => fake()->randomElement(config('akademik.ruangan')),
            'hari' => $hariIni, 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);
        // Sengaja TIDAK dibuatkan jurnal -> contoh kelas "belum diisi" di Monitor Piket.

        $guruPiketUser = User::create([
            'name' => 'Siti Rahayu, S.Pd', 'email' => 'guru.piket@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'approved',
        ]);
        $guruPiket = Guru::create([
            'user_id' => $guruPiketUser->id, 'nip' => fake()->numerify('19#########'),
            'nama' => 'Siti Rahayu, S.Pd', 'no_hp' => '08'.fake()->numerify('##########'),
            'mapel_utama_id' => $mapels->last()->id,
        ]);
        JadwalPiket::create(['guru_id' => $guruPiket->id, 'hari' => $hariIni, 'mulai' => '07:00', 'selesai' => '12:00']);
        $jadwalPiketHariIni = Jadwal::create([
            'kelas_id' => $kelas[0]->id, 'mapel_id' => $mapels->last()->id, 'guru_id' => $guruPiket->id,
            'ruang' => fake()->randomElement(config('akademik.ruangan')),
            'hari' => $hariIni, 'jam_ke_mulai' => 5, 'jam_ke_selesai' => 6,
        ]);
        $jurnalHariIni = Jurnal::create([
            'jadwal_id' => $jadwalPiketHariIni->id, 'guru_id' => $guruPiket->id,
            'tanggal' => now(), 'jam_ke_mulai' => 5, 'jam_ke_selesai' => 6,
            'status_guru' => 'hadir', 'materi' => 'Contoh materi yang sudah diisi hari ini.',
            'status_verifikasi' => 'pending',
        ]);
        $kelas[0]->siswas->each(fn (Siswa $s) => Absensi::create([
            'jurnal_id' => $jurnalHariIni->id, 'siswa_id' => $s->id, 'status' => 'hadir',
        ]));

        // ------------------------------------------------------- Contoh dispensasi
        // Dispensasi selalu diajukan guru piket (tahap piket otomatis lolos),
        // lalu menunggu keputusan Waka Kesiswaan.
        $piketUser = $gurus->firstWhere(fn (Guru $g) => $g->user_id && $g->jadwalPikets()->exists())?->user_id ?? $admin->id;
        $siswaList = Siswa::inRandomOrder()->take(4)->get();
        $wakaStates = ['pending', 'approved', 'rejected', 'pending'];
        foreach ($siswaList as $i => $s) {
            $d = Dispensasi::create([
                'siswa_id' => $s->id,
                'diajukan_oleh_id' => $piketUser,
                'piket_id' => $piketUser,
                'tanggal' => now()->subDays($i),
                'alasan' => 'Mengikuti lomba tingkat kabupaten.',
                'no_hp' => '08'.fake()->numerify('##########'),
                'status_piket' => 'approved',
                'status_waka' => $wakaStates[$i],
                'waka_id' => $wakaStates[$i] === 'pending' ? null : $waka->id,
                'catatan_waka' => $wakaStates[$i] === 'rejected' ? 'Surat belum lengkap.' : null,
            ]);
            $d->segarkanStatusAkhir();
        }

        // ------------------------------------------- Contoh pendaftaran menunggu
        $pendingGuru = User::create([
            'name' => 'Ahmad Suryadi, S.Pd', 'email' => 'ahmad.daftar@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'pending',
        ]);
        Guru::create(['user_id' => $pendingGuru->id, 'nama' => 'Ahmad Suryadi, S.Pd']);

        $pendingKelas = User::create([
            'name' => 'Ketua XI RPL 1', 'email' => 'xirpl1.daftar@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'siswa', 'status' => 'pending',
        ]);
        Siswa::create([
            'user_id' => $pendingKelas->id, 'kelas_id' => $kelas[2]->id,
            'nis' => fake()->unique()->numerify('2026####'), 'nama' => 'Ketua XI RPL 1',
            'jenis_kelamin' => 'P', 'jabatan' => 'pengurus',
        ]);

        // ------------------------------------------------------- Data ASLI sekolah
        // Mapel/kelas/guru+akun/jadwal/piket KBM/piket Waka asli SMKN 1 Boyolangu.
        // Ditaruh PALING TERAKHIR (setelah semua contoh demo di atas) supaya query
        // "take(N)"/index tetap/kelas[0..3] di atas nggak kesenggol data asli yang
        // jumlahnya ratusan baris. Lihat DataAsliSeeder untuk detail sumber & proses.
        (new DataAsliSeeder)->run($tahunAjaran, $kelas);
    }
}
