<?php

namespace Tests\Feature;

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

class RekapSiswaTest extends TestCase
{
    use RefreshDatabase;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->siswa = Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L', 'no_absen' => 1,
        ]);

        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $jurnal = Jurnal::create([
                'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => now()->subDays($i),
                'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
            ]);
            Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $this->siswa->id, 'status' => 'alpha']);
        }
    }

    public function test_waka_bisa_akses_dan_lihat_peringatan_alpha_tinggi(): void
    {
        $waka = User::factory()->role('waka')->create();

        $this->actingAs($waka)->get('/rekap/siswa')
            ->assertOk()->assertSee('Budi')->assertSee('1 siswa');
    }

    public function test_admin_bisa_akses_oversight(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->get('/rekap/siswa')->assertOk()->assertSee('Budi');
    }

    public function test_guru_tidak_bisa_akses(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->get('/rekap/siswa')->assertRedirect(route('guru.dashboard'));
    }

    public function test_ekspor_csv(): void
    {
        $waka = User::factory()->role('waka')->create();

        $response = $this->actingAs($waka)->get('/rekap/siswa/ekspor');
        $response->assertOk();

        ob_start();
        $response->baseResponse->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('Budi', $csv);
        $this->assertStringContainsString('X RPL 1', $csv);
    }

    public function test_filter_kelas(): void
    {
        $kelasLain = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        Siswa::create(['kelas_id' => $kelasLain->id, 'nis' => '999', 'nama' => 'Sinta', 'jenis_kelamin' => 'P']);

        $waka = User::factory()->role('waka')->create();

        $this->actingAs($waka)->get("/rekap/siswa?kelas_id={$this->siswa->kelas_id}")
            ->assertOk()->assertSee('Budi')->assertDontSee('Sinta');
    }
}
