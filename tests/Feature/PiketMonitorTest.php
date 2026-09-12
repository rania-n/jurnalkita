<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiketMonitorTest extends TestCase
{
    use RefreshDatabase;

    private User $piket;

    private User $waka;

    private Kelas $kelasA;

    private Kelas $kelasB;

    private Guru $guruA;

    private Guru $guruB;

    private Jurnal $jurnalDiisi;

    private Siswa $siswaKelasA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->next(Carbon::MONDAY)); // pin ke Senin, semua data di bawah juga "senin"

        $this->piket = User::factory()->role('guru')->create();
        $guruPiket = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guruPiket->id, 'hari' => 'senin']);

        $this->guruA = Guru::create(['nama' => 'Bu Sarah']);
        $this->guruB = Guru::create(['nama' => 'Pak Herman']);
        $this->waka = User::factory()->role('waka')->create();

        $this->kelasA = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->kelasB = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        $this->siswaKelasA = Siswa::create([
            'kelas_id' => $this->kelasA->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'no_absen' => 1,
        ]);

        $jadwalDiisi = Jadwal::create([
            'kelas_id' => $this->kelasA->id, 'mapel_id' => $mapel->id, 'guru_id' => $this->guruA->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $this->jurnalDiisi = Jurnal::create([
            'jadwal_id' => $jadwalDiisi->id, 'guru_id' => $this->guruA->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'Aljabar dasar',
        ]);
        Absensi::create(['jurnal_id' => $this->jurnalDiisi->id, 'siswa_id' => $this->siswaKelasA->id, 'status' => 'hadir']);

        Jadwal::create([
            'kelas_id' => $this->kelasB->id, 'mapel_id' => $mapel->id, 'guru_id' => $this->guruB->id,
            'hari' => 'senin', 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);
        // Sengaja tidak dibuatkan jurnal.
    }

    public function test_monitor_menampilkan_status_sudah_dan_belum_diisi(): void
    {
        $response = $this->actingAs($this->piket)->get('/piket/monitor');

        $response->assertOk()
            ->assertSee('X RPL 1')->assertSee('Hadir')->assertSee('Aljabar dasar')
            ->assertSee('X TKJ 1')->assertSee('Belum Diisi');
    }

    public function test_roster_shift_piket_tampil_di_atas_monitor(): void
    {
        JadwalPiket::create(['guru_id' => $this->guruA->id, 'hari' => 'senin', 'mulai' => '07:00', 'selesai' => '09:30']);

        $this->actingAs($this->waka)->get('/piket/monitor')
            ->assertOk()->assertSee('Petugas Piket Hari Ini')->assertSee('07:00')->assertSee('09:30');
    }

    public function test_mode_per_kelas_memisahkan_grup_per_kelas(): void
    {
        $this->actingAs($this->waka)
            ->get('/piket/monitor?mode=kelas')
            ->assertOk()->assertSeeInOrder(['X RPL 1', 'JP 1–2', 'X TKJ 1', 'JP 3–4']);
    }

    public function test_mode_per_guru_memisahkan_grup_per_guru(): void
    {
        $this->actingAs($this->waka)
            ->get('/piket/monitor?mode=guru')
            ->assertOk()->assertSee('Bu Sarah')->assertSee('Pak Herman');
    }

    public function test_akhir_pekan_tidak_ada_jadwal_tanpa_error(): void
    {
        $minggu = now()->next(Carbon::SUNDAY)->toDateString();

        $this->actingAs($this->waka)->get("/piket/monitor?tanggal={$minggu}")
            ->assertOk()->assertSee('Tidak ada jadwal pelajaran');
    }

    public function test_guru_biasa_bukan_piket_tidak_bisa_akses(): void
    {
        $guruBiasa = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guruBiasa->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guruBiasa)->get('/piket/monitor')->assertForbidden();
    }

    public function test_ekspor_ringkasan_berisi_baris_sesuai_status(): void
    {
        $csv = $this->unduh('/piket/monitor/ekspor');

        $this->assertStringContainsString('Kelas', $csv);
        $this->assertStringContainsString('X RPL 1', $csv);
        $this->assertStringContainsString('Aljabar dasar', $csv);
        $this->assertStringContainsString('Belum Diisi', $csv);
    }

    public function test_ekspor_detail_per_kelas_berisi_presensi_siswa(): void
    {
        $csv = $this->unduh("/piket/monitor/ekspor/kelas/{$this->kelasA->id}");

        $this->assertStringContainsString('No. Absen', $csv);
        $this->assertStringContainsString('Budi', $csv);
        $this->assertStringContainsString('Aljabar dasar', $csv);
        $this->assertStringContainsString('Hadir', $csv);
    }

    public function test_ekspor_detail_kelas_yang_belum_diisi_tetap_ada_baris(): void
    {
        $csv = $this->unduh("/piket/monitor/ekspor/kelas/{$this->kelasB->id}");

        $this->assertStringContainsString('Belum Diisi', $csv);
        $this->assertStringContainsString('JP 3-4', $csv);
    }

    public function test_ekspor_detail_per_guru(): void
    {
        $csv = $this->unduh("/piket/monitor/ekspor/guru/{$this->guruA->id}");

        $this->assertStringContainsString('Budi', $csv);
    }

    public function test_ekspor_detail_tipe_tidak_dikenal_404(): void
    {
        $this->actingAs($this->waka)
            ->get("/piket/monitor/ekspor/ngawur/{$this->kelasA->id}")
            ->assertNotFound();
    }

    private function unduh(string $url): string
    {
        $response = $this->actingAs($this->waka)->get($url);
        $response->assertOk();

        ob_start();
        $response->baseResponse->sendContent();

        return ob_get_clean();
    }
}
