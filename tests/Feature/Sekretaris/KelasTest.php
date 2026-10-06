<?php

namespace Tests\Feature\Sekretaris;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Support\HariSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasTest extends TestCase
{
    use RefreshDatabase;

    private User $sekretaris;

    private Kelas $kelasSaya;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kelasSaya = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->sekretaris = User::factory()->role('siswa')->create();
        Siswa::create([
            'user_id' => $this->sekretaris->id, 'kelas_id' => $this->kelasSaya->id,
            'nis' => '001', 'nama' => 'Ketua Kelas', 'jenis_kelamin' => 'L',
            'no_absen' => 1, 'jabatan' => 'pengurus',
        ]);
        Siswa::create([
            'kelas_id' => $this->kelasSaya->id, 'nis' => '002', 'nama' => 'Anggota Biasa',
            'jenis_kelamin' => 'P', 'no_absen' => 2, 'jabatan' => 'anggota',
        ]);
    }

    public function test_daftar_siswa_hanya_kelas_sendiri(): void
    {
        $kelasLain = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        Siswa::create(['kelas_id' => $kelasLain->id, 'nis' => '999', 'nama' => 'Siswa Lain', 'jenis_kelamin' => 'L']);

        $this->actingAs($this->sekretaris)->get('/sekretaris/kelas')
            ->assertOk()
            ->assertSee('Ketua Kelas')->assertSee('Anggota Biasa')->assertSee('Pengurus')
            ->assertDontSee('Siswa Lain');
    }

    public function test_daftar_siswa_urut_no_absen(): void
    {
        $this->actingAs($this->sekretaris)->get('/sekretaris/kelas')
            ->assertOk()->assertSeeInOrder(['Ketua Kelas', 'Anggota Biasa']);
    }

    public function test_daftar_siswa_menampilkan_kehadiran_hari_ini(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $ketua = Siswa::where('nama', 'Ketua Kelas')->firstOrFail();
        Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $ketua->id, 'status' => 'sakit']);
        // Anggota Biasa belum ada jurnal hari ini sama sekali -- tetap tampil, ditandai "belum ada jurnal".

        $this->actingAs($this->sekretaris)->get('/sekretaris/kelas')
            ->assertOk()
            ->assertSee('Sakit')
            ->assertSee('Belum ada jurnal');
    }

    /** Absensi TERAKHIR hari ini yang dipakai kalau siswa kena beberapa jurnal (JP beda) hari yang sama. */
    public function test_daftar_siswa_pakai_absensi_jp_terakhir_kalau_lebih_dari_satu_jurnal_hari_ini(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $ketua = Siswa::where('nama', 'Ketua Kelas')->firstOrFail();

        $jadwalPagi = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnalPagi = Jurnal::create([
            'jadwal_id' => $jadwalPagi->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'pagi',
        ]);
        Absensi::create(['jurnal_id' => $jurnalPagi->id, 'siswa_id' => $ketua->id, 'status' => 'izin']);

        $jadwalSiang = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 5, 'jam_ke_selesai' => 6,
        ]);
        $jurnalSiang = Jurnal::create([
            'jadwal_id' => $jadwalSiang->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 5, 'jam_ke_selesai' => 6, 'status_guru' => 'hadir', 'materi' => 'siang',
        ]);
        // Siswanya balik lagi & udah hadir pas JP 5-6, meski JP 1-2 tadi izin.
        Absensi::create(['jurnal_id' => $jurnalSiang->id, 'siswa_id' => $ketua->id, 'status' => 'hadir']);

        $this->actingAs($this->sekretaris)->get('/sekretaris/kelas')
            ->assertOk()
            ->assertSee('Hadir')
            ->assertDontSee('Izin');
    }

    public function test_jadwal_kelas_dikelompokkan_per_hari(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $kelasLain = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        Jadwal::create([
            'kelas_id' => $kelasLain->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);

        $this->actingAs($this->sekretaris)->get('/sekretaris/jadwal')
            ->assertOk()->assertSee('Senin')->assertSee('Matematika')->assertSee('Bu Sarah')
            ->assertDontSee('JP 3–4');
    }

    public function test_jadwal_hari_ini_ditandai_dan_kasih_status_jurnal(): void
    {
        $hariIni = HariSekolah::hariIni() ?? 'senin';
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwalSudahDiisi = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => $hariIni, 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        Jurnal::create([
            'jadwal_id' => $jadwalSudahDiisi->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => $hariIni, 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);

        $this->actingAs($this->sekretaris)->get('/sekretaris/jadwal')
            ->assertOk()
            ->assertSee('Hari Ini')
            ->assertSee('Hadir')
            ->assertSee('Belum Diisi');
    }

    public function test_jadwal_kosong_menampilkan_empty_state(): void
    {
        $this->actingAs($this->sekretaris)->get('/sekretaris/jadwal')
            ->assertOk()->assertSee('Belum ada jadwal pelajaran');
    }

    public function test_tab_hari_memfilter_jadwal_kelas(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        // Regresi: Collection::only() meledak di atas hasil groupBy() (lihat
        // Guru\JadwalController) -- pastikan filter tab hari nggak error 500.
        $this->actingAs($this->sekretaris)->get('/sekretaris/jadwal?hari=senin')
            ->assertOk()->assertSee('Matematika');

        $this->actingAs($this->sekretaris)->get('/sekretaris/jadwal?hari=selasa')
            ->assertOk()->assertSee('Belum ada jadwal pelajaran')->assertDontSee('Matematika');
    }

    public function test_rekap_menghitung_seluruh_riwayat_tanpa_filter_tanggal(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => now(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $ketua = Siswa::where('nama', 'Ketua Kelas')->firstOrFail();
        Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $ketua->id, 'status' => 'sakit']);

        $jurnalBulanLalu = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => now()->subMonthsNoOverflow(2),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'y',
        ]);
        Absensi::create(['jurnal_id' => $jurnalBulanLalu->id, 'siswa_id' => $ketua->id, 'status' => 'alpha']);

        // Kosong (belum difilter) = tampilkan SELURUH riwayat -- konsisten
        // sama Rekap Kehadiran Kelas milik Wali Kelas & Rekap Siswa Waka.
        $response = $this->actingAs($this->sekretaris)->get('/sekretaris/rekap');

        $response->assertOk()->assertSee('Ketua Kelas');
        $response->assertSee('text-sakit">1</span>', false);
        $response->assertSee('text-alpha">1</span>', false);
    }

    public function test_rekap_bisa_difilter_rentang_tanggal(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => now(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $ketua = Siswa::where('nama', 'Ketua Kelas')->firstOrFail();
        Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $ketua->id, 'status' => 'sakit']);

        $jurnalBulanLalu = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => now()->subMonthsNoOverflow(2),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'y',
        ]);
        Absensi::create(['jurnal_id' => $jurnalBulanLalu->id, 'siswa_id' => $ketua->id, 'status' => 'alpha']);

        $response = $this->actingAs($this->sekretaris)
            ->get('/sekretaris/rekap?dari='.now()->startOfMonth()->toDateString());

        $response->assertOk()->assertSee('Ketua Kelas');
        // Difilter dari awal bulan ini -- alpha 2 bulan lalu nggak ikut kehitung.
        $response->assertSee('text-sakit">1</span>', false);
        $response->assertDontSee('text-alpha">', false);
    }

    public function test_bukan_pengurus_kelas_tidak_bisa_akses(): void
    {
        $bukanPengurus = User::factory()->role('siswa')->create();
        Siswa::create([
            'user_id' => $bukanPengurus->id, 'kelas_id' => $this->kelasSaya->id,
            'nis' => '003', 'nama' => 'Anggota Lain', 'jenis_kelamin' => 'L', 'jabatan' => 'anggota',
        ]);

        // Bukan pengurus tetap terhubung ke kelas lewat kelasSekretaris(), jadi ini
        // menguji kalau memang akun BUKAN pengurus (tidak ada relasi siswa) yang ditolak.
        $tanpaSiswa = User::factory()->role('siswa')->create();

        $this->actingAs($tanpaSiswa)->get('/sekretaris/kelas')->assertForbidden();
    }

    public function test_header_menampilkan_kelas_yang_diampu_bukan_cuma_nama_sendiri(): void
    {
        // Header topbar cuma nampilin chip role di mobile -- buat pengurus kelas,
        // chip-nya sengaja disi nama kelasnya (bukan cuma "Pengurus Kelas" generik),
        // biar kelihatan kelas siapa tanpa buka menu lain.
        $this->actingAs($this->sekretaris)->get('/sekretaris')->assertOk()->assertSee('Pengurus X RPL 1');
    }

    public function test_rekap_membedakan_per_hari_dan_per_mapel(): void
    {
        $guru = Guru::create(['nama' => 'Pak Joko']);
        $mapelA = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $mapelB = Mapel::create(['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam']);

        $jadwalA = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapelA->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jadwalB = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapelB->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);

        $jurnalA = Jurnal::create([
            'jadwal_id' => $jadwalA->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'Aljabar',
        ]);
        $jurnalB = Jurnal::create([
            'jadwal_id' => $jadwalB->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4, 'status_guru' => 'hadir', 'materi' => 'Fisika',
        ]);

        $siswa = Siswa::where('nama', 'Ketua Kelas')->firstOrFail();
        // Pada hari yang sama, hadir di Mapel A, sakit di Mapel B
        Absensi::create(['jurnal_id' => $jurnalA->id, 'siswa_id' => $siswa->id, 'status' => 'hadir']);
        Absensi::create(['jurnal_id' => $jurnalB->id, 'siswa_id' => $siswa->id, 'status' => 'sakit']);

        // Default 'tipe=hari': hari ini dihitung sakit (prioritas sakit > hadir)
        $responseHari = $this->actingAs($this->sekretaris)->get('/sekretaris/rekap?tipe=hari');
        $responseHari->assertOk()->assertSee('Per Hari')->assertSee('Per Mapel');

        // 'tipe=mapel': memuat data rekap per jam mapel
        $responseMapel = $this->actingAs($this->sekretaris)->get('/sekretaris/rekap?tipe=mapel');
        $responseMapel->assertOk();
    }

    public function test_sekretaris_bisa_akses_fragmen_detail_rekap_siswa(): void
    {
        $guru = Guru::create(['nama' => 'Pak Joko']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $this->kelasSaya->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'Aljabar',
        ]);

        $siswa = Siswa::where('nama', 'Ketua Kelas')->firstOrFail();
        Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $siswa->id, 'status' => 'hadir', 'catatan' => 'Siswa aktif']);

        $response = $this->actingAs($this->sekretaris)
            ->get(route('sekretaris.kelas.rekap.siswa.fragment', $siswa));

        $response->assertOk()
            ->assertSee('Matematika')
            ->assertSee('Pak Joko')
            ->assertSee('Ketua Kelas')
            ->assertSee('Siswa aktif');
    }

    public function test_sekretaris_tidak_bisa_akses_fragmen_detail_siswa_kelas_lain(): void
    {
        $kelasLain = Kelas::create(['nama' => 'XI RPL 2', 'tingkat' => 'XI', 'jurusan' => 'RPL']);
        $siswaLain = Siswa::create(['kelas_id' => $kelasLain->id, 'nis' => '999', 'nama' => 'Siswa Luar', 'jenis_kelamin' => 'L']);

        $this->actingAs($this->sekretaris)
            ->get(route('sekretaris.kelas.rekap.siswa.fragment', $siswaLain))
            ->assertForbidden();
    }
}
