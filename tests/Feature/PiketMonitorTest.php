<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
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

    private Jadwal $jadwalDiisi;

    private Jadwal $jadwalBelumDiisi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->next(Carbon::MONDAY)); // pin ke Senin, semua data di bawah juga "senin"

        $this->piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $guruMengajar = Guru::create(['nama' => 'Bu Sarah']);
        $this->waka = User::factory()->role('waka')->create();

        $this->kelasA = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->kelasB = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        $this->jadwalDiisi = Jadwal::create([
            'kelas_id' => $this->kelasA->id, 'mapel_id' => $mapel->id, 'guru_id' => $guruMengajar->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        Jurnal::create([
            'jadwal_id' => $this->jadwalDiisi->id, 'guru_id' => $guruMengajar->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'Aljabar dasar',
        ]);

        $this->jadwalBelumDiisi = Jadwal::create([
            'kelas_id' => $this->kelasB->id, 'mapel_id' => $mapel->id, 'guru_id' => $guruMengajar->id,
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

    public function test_filter_berdasarkan_kelas(): void
    {
        // 'X TKJ 1' tetap muncul di dropdown filter dan label 'Belum Diisi' selalu ada
        // di rekap (walau nilainya 0), jadi cek baris tabelnya lewat jam jadwal kelas B
        // ('JP 3–4') yang unik -- itu yang harus hilang kalau filter benar.
        $this->actingAs($this->waka)
            ->get("/piket/monitor?kelas_id={$this->kelasA->id}")
            ->assertOk()->assertSee('X RPL 1')->assertSee('JP 1–2')->assertDontSee('JP 3–4');
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

    public function test_ekspor_csv_berisi_baris_sesuai_status(): void
    {
        $response = $this->actingAs($this->waka)->get('/piket/monitor/ekspor');
        $response->assertOk();

        ob_start();
        $response->baseResponse->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('Kelas', $csv);
        $this->assertStringContainsString('X RPL 1', $csv);
        $this->assertStringContainsString('Aljabar dasar', $csv);
        $this->assertStringContainsString('Belum Diisi', $csv);
    }
}
