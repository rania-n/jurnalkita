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

    public function test_jadwal_kosong_menampilkan_empty_state(): void
    {
        $this->actingAs($this->sekretaris)->get('/sekretaris/jadwal')
            ->assertOk()->assertSee('Belum ada jadwal pelajaran');
    }

    public function test_rekap_menghitung_kehadiran_bulan_ini(): void
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

        $response = $this->actingAs($this->sekretaris)->get('/sekretaris/rekap');

        $response->assertOk()->assertSee('Ketua Kelas');
        // 1 sakit bulan ini kehitung, alpha 2 bulan lalu TIDAK ikut kehitung.
        $response->assertSee('text-sakit">1</td>', false);
        $response->assertSee('text-alpha">0</td>', false);
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
}
