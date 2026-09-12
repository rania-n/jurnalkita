<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispensasiLaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $piket;

    private User $piketLain;

    private User $waka;

    private Siswa $siswaA;

    private Siswa $siswaB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->piket = User::factory()->role('guru')->create(['name' => 'Bu Sarah']);
        $guru = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Bu Sarah']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $this->piketLain = User::factory()->role('guru')->create(['name' => 'Pak Herman']);
        $guruLain = Guru::create(['user_id' => $this->piketLain->id, 'nama' => 'Pak Herman']);
        JadwalPiket::create(['guru_id' => $guruLain->id, 'hari' => 'selasa']);

        $this->waka = User::factory()->role('waka')->create();

        $kelasA = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $kelasB = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        $this->siswaA = Siswa::create(['kelas_id' => $kelasA->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
        $this->siswaB = Siswa::create(['kelas_id' => $kelasB->id, 'nis' => '002', 'nama' => 'Sinta', 'jenis_kelamin' => 'P']);

        Dispensasi::create([
            'siswa_id' => $this->siswaA->id, 'diajukan_oleh_id' => $this->piket->id, 'piket_id' => $this->piket->id,
            'tanggal' => '2026-09-01', 'alasan' => 'Lomba', 'status_piket' => 'approved',
        ]);
        Dispensasi::create([
            'siswa_id' => $this->siswaB->id, 'diajukan_oleh_id' => $this->piketLain->id, 'piket_id' => $this->piketLain->id,
            'tanggal' => '2026-09-05', 'alasan' => 'Sakit', 'status_piket' => 'approved',
        ]);
    }

    public function test_waka_bisa_filter_berdasarkan_tanggal(): void
    {
        $this->actingAs($this->waka)
            ->get('/dispensasi?dari=2026-09-01&sampai=2026-09-01')
            ->assertOk()->assertSee('Budi')->assertDontSee('Sinta');
    }

    public function test_waka_bisa_filter_berdasarkan_kelas(): void
    {
        $kelasB = Kelas::where('nama', 'X TKJ 1')->firstOrFail();

        $this->actingAs($this->waka)
            ->get("/dispensasi?kelas_id={$kelasB->id}")
            ->assertOk()->assertSee('Sinta')->assertDontSee('Budi');
    }

    public function test_waka_bisa_filter_berdasarkan_guru_piket(): void
    {
        $this->actingAs($this->waka)
            ->get("/dispensasi?guru_id={$this->piket->id}")
            ->assertOk()->assertSee('Budi')->assertDontSee('Sinta');
    }

    public function test_guru_piket_hanya_lihat_dan_ekspor_pengajuan_sendiri(): void
    {
        $response = $this->actingAs($this->piket)->get('/dispensasi');
        $response->assertOk()->assertSee('Budi')->assertDontSee('Sinta');

        $csv = $this->streamedCsv($this->piket);
        $this->assertStringContainsString('Budi', $csv);
        $this->assertStringNotContainsString('Sinta', $csv);
    }

    public function test_waka_ekspor_berisi_semua_baris_sesuai_filter(): void
    {
        $csv = $this->streamedCsv($this->waka);

        $this->assertStringContainsString('Tanggal', $csv);
        $this->assertStringContainsString('Nama Siswa', $csv);
        $this->assertStringContainsString('Budi', $csv);
        $this->assertStringContainsString('Sinta', $csv);
    }

    public function test_guru_bukan_piket_tidak_bisa_ekspor(): void
    {
        $guruBiasa = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guruBiasa->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guruBiasa)->get('/dispensasi/ekspor')->assertForbidden();
    }

    public function test_ekspor_tercatat_di_audit_log(): void
    {
        $this->streamedCsv($this->waka);

        $this->assertDatabaseHas('audit_logs', ['aksi' => 'Ekspor Laporan Dispensasi']);
    }

    /** Ambil isi CSV dari StreamedResponse (testResponse tidak bisa getContent() langsung). */
    private function streamedCsv(User $user): string
    {
        $response = $this->actingAs($user)->get('/dispensasi/ekspor');
        $response->assertOk();

        ob_start();
        $response->baseResponse->sendContent();

        return ob_get_clean();
    }
}
