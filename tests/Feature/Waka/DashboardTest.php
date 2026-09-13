<?php

namespace Tests\Feature\Waka;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $waka;

    private User $piket;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waka = User::factory()->role('waka')->create();

        $this->piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->siswa = Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'no_absen' => 1,
        ]);
    }

    private function dispensasi(array $overrides = []): Dispensasi
    {
        $d = Dispensasi::create(array_merge([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba', 'status_piket' => 'approved',
        ], $overrides));
        $d->segarkanStatusAkhir();

        return $d;
    }

    public function test_statistik_bulan_ini_menghitung_benar(): void
    {
        $this->dispensasi(); // pending
        $disetujui = $this->dispensasi();
        $disetujui->update(['status_waka' => 'approved']);
        $disetujui->segarkanStatusAkhir();

        $ditolak = $this->dispensasi();
        $ditolak->update(['status_waka' => 'rejected']);
        $ditolak->segarkanStatusAkhir();

        // Bulan lalu -- tidak boleh ikut kehitung di statistik bulan ini.
        $this->dispensasi(['tanggal' => now()->subMonthNoOverflow()]);

        $response = $this->actingAs($this->waka)->get('/waka');

        $response->assertOk()
            ->assertSee('Dispensasi Bulan Ini')
            ->assertSeeInOrder(['Diajukan', '3'])
            ->assertSeeInOrder(['Disetujui', '1'])
            ->assertSeeInOrder(['Ditolak', '1']);
    }

    public function test_dispensasi_terbaru_tampil_di_dasbor(): void
    {
        $this->dispensasi();

        $this->actingAs($this->waka)->get('/waka')
            ->assertOk()->assertSee('Dispensasi Terbaru')->assertSee('Budi');
    }

    public function test_dasbor_kosong_menampilkan_empty_state(): void
    {
        $this->actingAs($this->waka)->get('/waka')
            ->assertOk()->assertSee('Belum ada dispensasi');
    }
}
