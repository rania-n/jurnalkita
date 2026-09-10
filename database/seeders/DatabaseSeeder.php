<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------- Akun tetap
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 'approved',
        ]);

        $waka = User::create([
            'name' => 'Hariyadi, M.Pd', 'email' => 'waka@jurnalkita.test',
            'email_verified_at' => now(), 'password' => Hash::make('password'),
            'role' => 'waka', 'status' => 'approved',
        ]);

        // ----------------------------------------------------------------- Mapel
        $mapels = collect([
            ['kode' => 'MAT', 'nama' => 'Matematika'],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris'],
            ['kode' => 'PBO', 'nama' => 'Pemrograman Berorientasi Objek'],
            ['kode' => 'PWL', 'nama' => 'Pemrograman Web dan Perangkat Bergerak'],
        ])->map(fn ($m) => Mapel::create($m));

        // ----------------------------------------------------------------- Guru
        $guruData = [
            ['nama' => 'Winartin, S.Pd', 'mapel' => 'BIG', 'akun' => true, 'piket' => 'senin'],
            ['nama' => 'Budi Santoso, S.Pd', 'mapel' => 'MAT', 'akun' => true, 'piket' => 'rabu'],
            ['nama' => 'Drs. M. Yusuf', 'mapel' => 'BIN', 'akun' => true, 'piket' => 'rabu'],
            ['nama' => 'Sarah Amelia, M.Pd', 'mapel' => 'PBO', 'akun' => false, 'piket' => 'kamis'],
            ['nama' => 'Rendra Prakoso, S.Kom', 'mapel' => 'PWL', 'akun' => true, 'piket' => null],
            ['nama' => 'Dewi Anjani, S.Pd', 'mapel' => 'MAT', 'akun' => false, 'piket' => null],
        ];

        $gurus = collect($guruData)->map(function ($g, $i) use ($mapels) {
            $user = $g['akun'] ? User::create([
                'name' => $g['nama'],
                'email' => 'guru'.($i + 1).'@jurnalkita.test',
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
        $jamMulai = ['07:00', '07:45', '08:30', '09:15', '10:15', '11:00', '11:45', '12:30'];
        foreach ($jamMulai as $idx => $mulai) {
            $selesai = date('H:i', strtotime($mulai) + 45 * 60);
            JamPelajaran::create([
                'jam_ke' => $idx + 1, 'mulai' => $mulai, 'selesai' => $selesai,
                'kategori' => 'senin_kamis',
            ]);
            JamPelajaran::create([
                'jam_ke' => $idx + 1, 'mulai' => $mulai, 'selesai' => date('H:i', strtotime($mulai) + 35 * 60),
                'kategori' => 'jumat',
            ]);
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
            'wali_id' => $gurus[$i % $gurus->count()]->id,
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

        // ----------------------------------------------------------------- Jadwal
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $kelas->each(function (Kelas $k) use ($mapels, $gurus, $hariList) {
            foreach ($hariList as $hi => $hari) {
                Jadwal::create([
                    'kelas_id' => $k->id,
                    'mapel_id' => $mapels[$hi % $mapels->count()]->id,
                    'guru_id' => $gurus[$hi % $gurus->count()]->id,
                    'ruang' => fake()->randomElement(config('akademik.ruangan')),
                    'hari' => $hari,
                    'jam_ke_mulai' => 1,
                    'jam_ke_selesai' => 2,
                ]);
            }
        });

        // -------------------------------------------------- Contoh jurnal + absensi
        Jadwal::with('kelas.siswas')->take(3)->get()->each(function (Jadwal $jadwal, $i) {
            $jurnal = Jurnal::create([
                'jadwal_id' => $jadwal->id,
                'guru_id' => $jadwal->guru_id,
                'tanggal' => now()->subDays($i),
                'jam_ke_mulai' => $jadwal->jam_ke_mulai,
                'jam_ke_selesai' => $jadwal->jam_ke_selesai,
                'status_guru' => 'hadir',
                'materi' => 'Materi pertemuan '.($i + 1).': dasar dan latihan.',
                'metode' => 'Ceramah, diskusi, latihan',
                'status_verifikasi' => $i === 0 ? 'pending' : 'terverifikasi',
            ]);

            $jadwal->kelas->siswas->each(fn (Siswa $s) => Absensi::create([
                'jurnal_id' => $jurnal->id,
                'siswa_id' => $s->id,
                'status' => fake()->randomElement(['hadir', 'hadir', 'hadir', 'sakit', 'izin']),
            ]));
        });

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
    }
}
