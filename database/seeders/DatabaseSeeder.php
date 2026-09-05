<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. JAM PELAJARAN GLOBAL (Sesuai Dokumen Resmi)
        // ==========================================
        DB::table('jammapel')->insert([
            // --- SENIN - KAMIS (1 s.d. 13 JP) ---
            ['jamke' => 1, 'jammulai' => '07:00:00', 'jamselesai' => '07:35:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-1'],
            ['jamke' => 2, 'jammulai' => '07:35:00', 'jamselesai' => '08:10:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-2'],
            ['jamke' => 3, 'jammulai' => '08:10:00', 'jamselesai' => '08:45:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-3'],
            ['jamke' => 4, 'jammulai' => '08:45:00', 'jamselesai' => '09:20:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-4'],
            ['jamke' => 5, 'jammulai' => '09:40:00', 'jamselesai' => '10:15:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-5 (Setelah Istirahat 1)'],
            ['jamke' => 6, 'jammulai' => '10:15:00', 'jamselesai' => '10:50:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-6'],
            ['jamke' => 7, 'jammulai' => '10:50:00', 'jamselesai' => '11:25:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-7'],
            ['jamke' => 8, 'jammulai' => '13:30:00', 'jamselesai' => '14:05:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-8 (Setelah Istirahat 2/Ishoma)'],
            ['jamke' => 9, 'jammulai' => '14:05:00', 'jamselesai' => '14:40:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-9'],
            ['jamke' => 10, 'jammulai' => '14:40:00', 'jamselesai' => '15:15:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-10'],
            ['jamke' => 11, 'jammulai' => '15:15:00', 'jamselesai' => '15:50:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-11'],
            ['jamke' => 12, 'jammulai' => '15:50:00', 'jamselesai' => '16:25:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-12'],
            ['jamke' => 13, 'jammulai' => '16:25:00', 'jamselesai' => '17:00:00', 'kategori' => 'seninkamis', 'keterangan' => 'Jam ke-13'],

            // --- JUMAT (1 s.d. 13 JP) ---
            ['jamke' => 1, 'jammulai' => '07:00:00', 'jamselesai' => '07:30:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-1 Jumat'],
            ['jamke' => 2, 'jammulai' => '07:30:00', 'jamselesai' => '08:00:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-2 Jumat'],
            ['jamke' => 3, 'jammulai' => '08:00:00', 'jamselesai' => '08:30:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-3 Jumat'],
            ['jamke' => 4, 'jammulai' => '08:30:00', 'jamselesai' => '09:00:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-4 Jumat'],
            ['jamke' => 5, 'jammulai' => '09:00:00', 'jamselesai' => '09:30:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-5 Jumat'],
            ['jamke' => 6, 'jammulai' => '09:50:00', 'jamselesai' => '10:20:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-6 Jumat (Setelah Istirahat 1)'],
            ['jamke' => 7, 'jammulai' => '10:20:00', 'jamselesai' => '10:50:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-7 Jumat'],
            ['jamke' => 8, 'jammulai' => '10:50:00', 'jamselesai' => '11:20:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-8 Jumat'],
            ['jamke' => 9, 'jammulai' => '13:30:00', 'jamselesai' => '13:55:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-9 Jumat (Setelah Istirahat 2)'],
            ['jamke' => 10, 'jammulai' => '13:55:00', 'jamselesai' => '14:20:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-10 Jumat'],
            ['jamke' => 11, 'jammulai' => '14:20:00', 'jamselesai' => '14:45:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-11 Jumat'],
            ['jamke' => 12, 'jammulai' => '14:45:00', 'jamselesai' => '15:10:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-12 Jumat'],
            ['jamke' => 13, 'jammulai' => '15:10:00', 'jamselesai' => '15:35:00', 'kategori' => 'jumat', 'keterangan' => 'Jam ke-13 Jumat'],
        ]);

        // ==========================================
        // 2. MASTER USERS
        // ==========================================
        DB::table('users')->insert([
            ['username' => 'admin1', 'email' => 'admin@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'admin', 'statusapproval' => 'approved'],
            ['username' => 'anisa_guru', 'email' => 'anisa@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'fajar_guru', 'email' => 'fajar@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'badrus_guru', 'email' => 'badrus@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'lutfia_guru', 'email' => 'lutfia@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'zainul_guru', 'email' => 'zainul@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'kurnila_guru', 'email' => 'kurnila@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'hendro_guru', 'email' => 'hendro@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'wiwik_guru', 'email' => 'wiwik@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'erna_guru', 'email' => 'erna@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'sulistyowati_guru', 'email' => 'sulistyowati@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'widodo_guru', 'email' => 'widodo@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'laili_guru', 'email' => 'laili@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'winartin_guru', 'email' => 'winartin@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'mufatiroh_guru', 'email' => 'mufatiroh@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'guru', 'statusapproval' => 'approved'],
            ['username' => 'pengurusxirpl2', 'email' => 'pengurus.xirpl2@smkn1boyolangu.sch.id', 'password' => hash('sha256', 'password123'), 'rolebase' => 'siswa', 'statusapproval' => 'approved']
        ]);

        // ==========================================
        // 3. MASTER GURU (BERBASIS NIK)
        // ==========================================
        DB::table('guru')->insert([
            ['userid' => 2, 'nik' => '3504010101', 'nip' => '1981010101', 'namaguru' => 'Anisa Kusumawati, S.Pd', 'nomorhp' => '08123456701'],
            ['userid' => 3, 'nik' => '3504010102', 'nip' => '1982020202', 'namaguru' => 'Fajar Wahyu Pratiwi, S.S', 'nomorhp' => '08123456702'],
            ['userid' => 4, 'nik' => '3504010103', 'nip' => '1983030303', 'namaguru' => 'Badrus Sulaiman, S.Pd.', 'nomorhp' => '08123456703'],
            ['userid' => 5, 'nik' => '3504010104', 'nip' => '1984040404', 'namaguru' => 'Lutfia Marsalina, S.Pd.I, M.Pd.', 'nomorhp' => '08123456704'],
            ['userid' => 6, 'nik' => '3504010105', 'nip' => '1985050505', 'namaguru' => 'Zainul Arifin, S.Pd', 'nomorhp' => '08123456705'],
            ['userid' => 7, 'nik' => '3504010106', 'nip' => '1986060606', 'namaguru' => 'Kurnila Putri Islamawati, S.Pd', 'nomorhp' => '08123456706'],
            ['userid' => 8, 'nik' => '3504010107', 'nip' => '1987070707', 'namaguru' => 'Hendro Suwignyo, ST', 'nomorhp' => '08123456707'],
            ['userid' => 9, 'nik' => '3504010108', 'nip' => '1988080808', 'namaguru' => 'Wiwik Juniarsih, S.Pd', 'nomorhp' => '08123456708'],
            ['userid' => 10, 'nik' => '3504010109', 'nip' => '1989090909', 'namaguru' => 'Erna Qoriah, S.E.', 'nomorhp' => '08123456709'],
            ['userid' => 11, 'nik' => '3504010110', 'nip' => '1990101010', 'namaguru' => 'Sulistyowati, SS', 'nomorhp' => '08123456710'],
            ['userid' => 12, 'nik' => '3504010111', 'nip' => '1991111111', 'namaguru' => 'Widodo, S.Pd', 'nomorhp' => '08123456711'],
            ['userid' => 13, 'nik' => '3504010112', 'nip' => '1992121212', 'namaguru' => 'Laili Ermawati, S.Pd', 'nomorhp' => '08123456712'],
            ['userid' => 14, 'nik' => '3504010113', 'nip' => '1993010113', 'namaguru' => 'Winartin, S.Pd', 'nomorhp' => '08123456713'],
            ['userid' => 15, 'nik' => '3504010114', 'nip' => '1994020214', 'namaguru' => 'Mufatiroh, S.Ag', 'nomorhp' => '08123456714'],
        ]);

        // ==========================================
        // 4. MASTER KELAS (Wali Kelas: Winartin, S.Pd)
        // ==========================================
        DB::table('kelas')->insert([
            [
                'id' => 'xirpl2',
                'namakelas' => 'XI RPL 2',
                'nikwalikelas' => '3504010113', // NIK Ibu Winartin, S.Pd
                'jumlahsiswa' => 36
            ]
        ]);

        // ==========================================
        // 5. MASTER SISWA / PENGURUS KELAS
        // ==========================================
        DB::table('siswa')->insert([
            [
                'userid' => 16,
                'nisn' => '0089912345',
                'namasiswa' => 'Pengurus Kelas XI RPL 2',
                'kelasid' => 'xirpl2',
                'jabatankelas' => 'penguruskelas'
            ]
        ]);

        // ==========================================
        // 6. MASTER MATA PELAJARAN
        // ==========================================
        DB::table('mapel')->insert([
            ['kodemapel' => 'pkw', 'namamapel' => 'Kreativitas, Inovasi, dan Kewirausahaan'],
            ['kodemapel' => 'bing', 'namamapel' => 'Bahasa Inggris'],
            ['kodemapel' => 'rpl', 'namamapel' => 'Konsentrasi RPL'],
            ['kodemapel' => 'mtk', 'namamapel' => 'Matematika'],
            ['kodemapel' => 'pjok', 'namamapel' => 'PJOK'],
            ['kodemapel' => 'mpilrpl', 'namamapel' => 'Mapel Pilihan RPL'],
            ['kodemapel' => 'pp', 'namamapel' => 'Pendidikan Pancasila'],
            ['kodemapel' => 'sej', 'namamapel' => 'Sejarah'],
            ['kodemapel' => 'bjepang', 'namamapel' => 'Bahasa Jepang'],
            ['kodemapel' => 'bk', 'namamapel' => 'BK'],
            ['kodemapel' => 'bjawa', 'namamapel' => 'Bahasa Jawa'],
            ['kodemapel' => 'bind', 'namamapel' => 'Bahasa Indonesia'],
            ['kodemapel' => 'pai', 'namamapel' => 'Pendidikan Agama Islam dan Budi Pekerti'],
        ]);

        // ==========================================
        // 7. JADWAL KBM KELAS XI RPL 2 (Sesuai Gambar Dokumen Asli)
        // ==========================================
        DB::table('jadwal')->insert([
            // --- HARI SENIN ---
            ['kelasid' => 'xirpl2', 'mapelid' => 1, 'guruutamaid' => 1, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'senin', 'jamkemulai' => 2, 'jamkeselesai' => 4],
            ['kelasid' => 'xirpl2', 'mapelid' => 2, 'guruutamaid' => 2, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'senin', 'jamkemulai' => 5, 'jamkeselesai' => 6],
            ['kelasid' => 'xirpl2', 'mapelid' => 3, 'guruutamaid' => 3, 'gurupendampingid' => null, 'ruang' => 'Lab. RPL 2', 'hari' => 'senin', 'jamkemulai' => 8, 'jamkeselesai' => 10],

            // --- HARI SELASA ---
            ['kelasid' => 'xirpl2', 'mapelid' => 4, 'guruutamaid' => 4, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'selasa', 'jamkemulai' => 1, 'jamkeselesai' => 3],
            ['kelasid' => 'xirpl2', 'mapelid' => 2, 'guruutamaid' => 2, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'selasa', 'jamkemulai' => 4, 'jamkeselesai' => 5],
            ['kelasid' => 'xirpl2', 'mapelid' => 5, 'guruutamaid' => 5, 'gurupendampingid' => null, 'ruang' => 'LAP', 'hari' => 'selasa', 'jamkemulai' => 6, 'jamkeselesai' => 7],
            ['kelasid' => 'xirpl2', 'mapelid' => 3, 'guruutamaid' => 3, 'gurupendampingid' => null, 'ruang' => 'Lab. RPL 2', 'hari' => 'selasa', 'jamkemulai' => 8, 'jamkeselesai' => 10],

            // --- HARI RABU ---
            ['kelasid' => 'xirpl2', 'mapelid' => 3, 'guruutamaid' => 6, 'gurupendampingid' => null, 'ruang' => 'Lab. RPL 2', 'hari' => 'rabu', 'jamkemulai' => 1, 'jamkeselesai' => 4],
            ['kelasid' => 'xirpl2', 'mapelid' => 6, 'guruutamaid' => 7, 'gurupendampingid' => null, 'ruang' => 'Lab. RPL 2', 'hari' => 'rabu', 'jamkemulai' => 5, 'jamkeselesai' => 6],
            ['kelasid' => 'xirpl2', 'mapelid' => 7, 'guruutamaid' => 8, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'rabu', 'jamkemulai' => 7, 'jamkeselesai' => 8],
            ['kelasid' => 'xirpl2', 'mapelid' => 8, 'guruutamaid' => 9, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'rabu', 'jamkemulai' => 9, 'jamkeselesai' => 10],

            // --- HARI KAMIS ---
            ['kelasid' => 'xirpl2', 'mapelid' => 9, 'guruutamaid' => 10, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'kamis', 'jamkemulai' => 1, 'jamkeselesai' => 2],
            ['kelasid' => 'xirpl2', 'mapelid' => 10, 'guruutamaid' => 11, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'kamis', 'jamkemulai' => 3, 'jamkeselesai' => 3],
            ['kelasid' => 'xirpl2', 'mapelid' => 11, 'guruutamaid' => 12, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'kamis', 'jamkemulai' => 4, 'jamkeselesai' => 5],
            ['kelasid' => 'xirpl2', 'mapelid' => 3, 'guruutamaid' => 6, 'gurupendampingid' => null, 'ruang' => 'Lab. RPL 2', 'hari' => 'kamis', 'jamkemulai' => 8, 'jamkeselesai' => 10],

            // --- HARI JUMAT ---
            ['kelasid' => 'xirpl2', 'mapelid' => 12, 'guruutamaid' => 13, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'jumat', 'jamkemulai' => 2, 'jamkeselesai' => 4],
            ['kelasid' => 'xirpl2', 'mapelid' => 13, 'guruutamaid' => 14, 'gurupendampingid' => null, 'ruang' => 'R 58', 'hari' => 'jumat', 'jamkemulai' => 5, 'jamkeselesai' => 7],
            ['kelasid' => 'xirpl2', 'mapelid' => 3, 'guruutamaid' => 3, 'gurupendampingid' => null, 'ruang' => 'Lab. RPL 2', 'hari' => 'jumat', 'jamkemulai' => 8, 'jamkeselesai' => 10],
            ['kelasid' => 'xirpl2', 'mapelid' => 1, 'guruutamaid' => 1, 'gurupendampingid' => null, 'ruang' => 'Lab. RPL 2', 'hari' => 'jumat', 'jamkemulai' => 11, 'jamkeselesai' => 12],
        ]);
    }
}