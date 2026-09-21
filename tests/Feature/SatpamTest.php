<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Support\QrDispensasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SatpamTest extends TestCase
{
    use RefreshDatabase;

    private User $satpam;

    private Dispensasi $disetujui;

    protected function setUp(): void
    {
        parent::setUp();

        $this->satpam = User::factory()->role('satpam')->create();

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
        $piket = User::factory()->role('guru')->create();

        $this->disetujui = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $piket->id, 'piket_id' => $piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba',
            'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ]);
    }

    public function test_scan_token_valid_menampilkan_disetujui(): void
    {
        $token = QrDispensasi::token($this->disetujui->id);

        $this->actingAs($this->satpam)
            ->get("/satpam/scan?id={$this->disetujui->id}&token={$token}")
            ->assertOk()->assertSee('Disetujui')->assertSee('Budi');
    }

    public function test_scan_token_salah_tidak_berlaku(): void
    {
        $this->actingAs($this->satpam)
            ->get("/satpam/scan?id={$this->disetujui->id}&token=ngawurbanget")
            ->assertOk()->assertSee('Tidak Berlaku')->assertDontSee('Budi');
    }

    public function test_scan_dispensasi_belum_disetujui_tidak_berlaku(): void
    {
        $kelas = Kelas::first();
        $siswaLain = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '002', 'nama' => 'Sinta', 'jenis_kelamin' => 'P']);
        $piket = User::factory()->role('guru')->create();
        $pending = Dispensasi::create([
            'siswa_id' => $siswaLain->id, 'diajukan_oleh_id' => $piket->id, 'piket_id' => $piket->id,
            'tanggal' => today(), 'alasan' => 'Sakit', 'status_piket' => 'approved',
        ]);
        $token = QrDispensasi::token($pending->id);

        $this->actingAs($this->satpam)
            ->get("/satpam/scan?id={$pending->id}&token={$token}")
            ->assertOk()->assertSee('Tidak Berlaku');
    }

    public function test_setiap_scan_tercatat_di_audit_log(): void
    {
        $token = QrDispensasi::token($this->disetujui->id);
        $this->actingAs($this->satpam)->get("/satpam/scan?id={$this->disetujui->id}&token={$token}");

        $this->assertDatabaseHas('audit_logs', ['aksi' => 'Scan QR Dispensasi', 'user_id' => $this->satpam->id]);
    }

    public function test_dashboard_satpam_menampilkan_riwayat_hari_ini(): void
    {
        $token = QrDispensasi::token($this->disetujui->id);
        $this->actingAs($this->satpam)->get("/satpam/scan?id={$this->disetujui->id}&token={$token}");

        $this->actingAs($this->satpam)->get('/satpam')
            ->assertOk()->assertSee('Valid');
    }

    /**
     * Scan QR TANPA login -- ini yang justru diharapkan. Satpam buka QR-nya
     * dari kamera HP langsung (belum tentu punya sesi login di browser HP-nya
     * saat itu), keamanannya dijamin token QR yang ganti tiap 10 detik
     * sendiri (lihat QrDispensasi), bukan dari sesi/role. Dulu route ini
     * kepentok middleware role:satpam, jadi malah kelempar ke halaman login
     * dulu -- padahal view-nya (satpam.hasil-scan) sendiri udah dari awal
     * dirancang tanpa shell/sidebar (layout guest).
     */
    public function test_scan_bisa_diakses_tanpa_login_sama_sekali(): void
    {
        $token = QrDispensasi::token($this->disetujui->id);

        $this->get("/satpam/scan?id={$this->disetujui->id}&token={$token}")
            ->assertOk()->assertSee('Disetujui')->assertSee('Budi');
    }

    /** Role lain (bukan satpam) tetap boleh scan juga -- token QR-nya yang jadi penjamin, bukan role. */
    public function test_scan_boleh_diakses_role_apapun_yang_lagi_login(): void
    {
        $guru = User::factory()->role('guru')->create();
        $token = QrDispensasi::token($this->disetujui->id);

        $this->actingAs($guru)->get("/satpam/scan?id={$this->disetujui->id}&token={$token}")
            ->assertOk()->assertSee('Disetujui');
    }

    public function test_dispensasi_disetujui_tapi_sudah_lewat_tanggalnya_tidak_berlaku(): void
    {
        $kelas = Kelas::first();
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '003', 'nama' => 'Rian', 'jenis_kelamin' => 'L']);
        $piket = User::factory()->role('guru')->create();
        $kadaluwarsa = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $piket->id, 'piket_id' => $piket->id,
            'tanggal' => today()->subDays(3), 'alasan' => 'Lomba minggu lalu',
            'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ]);
        $token = QrDispensasi::token($kadaluwarsa->id);

        $this->actingAs($this->satpam)
            ->get("/satpam/scan?id={$kadaluwarsa->id}&token={$token}")
            ->assertOk()->assertSee('Tidak Berlaku');
    }

    public function test_dispensasi_beberapa_hari_masih_berlaku_di_hari_kedua(): void
    {
        $kelas = Kelas::first();
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '004', 'nama' => 'Wati', 'jenis_kelamin' => 'P']);
        $piket = User::factory()->role('guru')->create();
        $multiHari = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $piket->id, 'piket_id' => $piket->id,
            'tanggal' => today()->subDay(), 'tanggal_selesai' => today()->addDay(), 'alasan' => 'Sakit 3 hari',
            'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ]);
        $token = QrDispensasi::token($multiHari->id);

        $this->actingAs($this->satpam)
            ->get("/satpam/scan?id={$multiHari->id}&token={$token}")
            ->assertOk()->assertSee('Disetujui');
    }
}
