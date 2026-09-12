<?php

namespace Tests\Feature;

use App\Models\CatatanTerlambat;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SatpamTerlambatTest extends TestCase
{
    use RefreshDatabase;

    private User $satpam;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->satpam = User::factory()->role('satpam')->create();
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
    }

    public function test_satpam_bisa_catat_siswa_terlambat(): void
    {
        $this->actingAs($this->satpam)->post('/satpam/terlambat', [
            'siswa_id' => $this->siswa->id, 'jam_datang' => '07:15', 'catatan' => 'Ban bocor',
        ])->assertRedirect(route('satpam.dashboard'));

        $this->assertDatabaseHas('catatan_terlambats', [
            'siswa_id' => $this->siswa->id, 'dicatat_oleh_id' => $this->satpam->id, 'catatan' => 'Ban bocor',
        ]);
    }

    public function test_catatan_terlambat_tercatat_di_audit_log(): void
    {
        $this->actingAs($this->satpam)->post('/satpam/terlambat', [
            'siswa_id' => $this->siswa->id, 'jam_datang' => '07:15',
        ]);

        $this->assertDatabaseHas('audit_logs', ['aksi' => 'Catat Siswa Terlambat']);
    }

    public function test_dashboard_satpam_menampilkan_riwayat_terlambat_hari_ini(): void
    {
        $this->actingAs($this->satpam)->post('/satpam/terlambat', [
            'siswa_id' => $this->siswa->id, 'jam_datang' => '07:15',
        ]);

        $this->actingAs($this->satpam)->get('/satpam')->assertOk()->assertSee('Budi');
    }

    public function test_bukan_satpam_tidak_bisa_catat_terlambat(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->post('/satpam/terlambat', [
            'siswa_id' => $this->siswa->id, 'jam_datang' => '07:15',
        ])->assertRedirect(route('guru.dashboard'));
    }

    public function test_waka_dan_wali_kelas_lihat_jumlah_terlambat_di_rekap(): void
    {
        CatatanTerlambat::create([
            'siswa_id' => $this->siswa->id, 'tanggal' => today(), 'jam_datang' => '07:15',
            'dicatat_oleh_id' => $this->satpam->id,
        ]);

        $waka = User::factory()->role('waka')->create();
        $this->actingAs($waka)->get('/rekap/siswa')
            ->assertOk()->assertSee('text-navy">1</td>', false);
    }
}
