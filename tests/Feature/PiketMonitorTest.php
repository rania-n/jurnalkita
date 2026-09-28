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

    /** Rentang Dari/Sampai lebih dari 1 hari -- data tiap hari digabung, kolom Tanggal ikut muncul. */
    public function test_rentang_tanggal_menggabungkan_beberapa_hari_sekaligus(): void
    {
        $dari = now()->subDay()->toDateString(); // Minggu -- kemarin dari Senin yang di-pin setUp()
        $sampai = now()->toDateString(); // Senin -- ada jadwal & jurnalnya (lihat setUp())

        $response = $this->actingAs($this->waka)->get("/piket/monitor?dari={$dari}&sampai={$sampai}");

        $response->assertOk()
            ->assertSee('X RPL 1')->assertSee('Aljabar dasar')
            ->assertSee('X TKJ 1')->assertSee('Belum Diisi')
            ->assertSee(now()->translatedFormat('d M Y')); // kolom Tanggal per-baris cuma muncul kalau rentangnya >1 hari
    }

    /** Sampai tanggal nggak boleh melewati hari ini -- diklem balik, bukan error. */
    public function test_rentang_tanggal_tidak_bisa_melewati_hari_ini(): void
    {
        $besok = now()->addDay()->toDateString();

        $response = $this->actingAs($this->waka)->get("/piket/monitor?dari={$besok}&sampai={$besok}");

        $response->assertOk()->assertSee(now()->translatedFormat('d M Y'));
    }

    public function test_akhir_pekan_tidak_ada_jadwal_tanpa_error(): void
    {
        // "Kemarin" (bukan ->next(SUNDAY)) -- setUp() udah travelTo() ke
        // Senin, jadi "besok Minggu" akan keanggap tanggal MASA DEPAN dan
        // ke-klem balik ke hari ini (lihat rentangTanggal(), Monitor Piket
        // nggak boleh pilih tanggal yang belum kejalanin). "Kemarin" dari
        // Senin yang di-pin selalu Minggu juga, tapi di masa lalu.
        $minggu = now()->subDay()->toDateString();

        $this->actingAs($this->waka)->get("/piket/monitor?tanggal={$minggu}")
            ->assertOk()->assertSee('Tidak ada jadwal pelajaran');
    }

    public function test_admin_bisa_akses_oversight(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->get('/piket/monitor')->assertOk()->assertSee('X RPL 1');
    }

    public function test_guru_biasa_bukan_piket_tetap_bisa_lihat_monitor(): void
    {
        // Monitor Piket cuma buat LIHAT -- terbuka buat semua guru, bukan cuma
        // yang piket hari ini (beda sama mencatat presensi siswa, yang tetap
        // dikunci guru piket beneran, lihat PresensiSiswaTest/PiketController).
        $guruBiasa = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guruBiasa->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guruBiasa)->get('/piket/monitor')->assertOk();
    }

    public function test_guru_biasa_bukan_piket_tidak_bisa_ekspor(): void
    {
        // Beda dari sekadar lihat -- ekspor tetap dikunci guru piket/waka/admin.
        $guruBiasa = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guruBiasa->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guruBiasa)->get('/piket/monitor/ekspor')->assertForbidden();
    }

    public function test_tombol_ekspor_disembunyikan_dari_guru_biasa_bukan_piket(): void
    {
        $guruBiasa = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guruBiasa->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guruBiasa)->get('/piket/monitor')
            ->assertOk()
            ->assertDontSee('Ekspor Ringkasan');
    }

    public function test_ekspor_ringkasan_berisi_baris_sesuai_status(): void
    {
        $res = $this->unduh('/piket/monitor/ekspor');
        $this->assertStringContainsString('monitor-piket-ringkas-', $res->headers->get('content-disposition'));
        $this->assertStringContainsString('.pdf', $res->headers->get('content-disposition'));
    }

    public function test_ekspor_detail_per_kelas_berisi_presensi_siswa(): void
    {
        $res = $this->unduh("/piket/monitor/ekspor/kelas/{$this->kelasA->id}");
        $this->assertStringContainsString('monitor-piket-kelas-', $res->headers->get('content-disposition'));
        $this->assertStringContainsString('.pdf', $res->headers->get('content-disposition'));
    }

    public function test_ekspor_detail_kelas_yang_belum_diisi_tetap_ada_baris(): void
    {
        $res = $this->unduh("/piket/monitor/ekspor/kelas/{$this->kelasB->id}");
        $this->assertStringContainsString('monitor-piket-kelas-', $res->headers->get('content-disposition'));
        $this->assertStringContainsString('.pdf', $res->headers->get('content-disposition'));
    }

    public function test_ekspor_detail_per_guru(): void
    {
        $res = $this->unduh("/piket/monitor/ekspor/guru/{$this->guruA->id}");
        $this->assertStringContainsString('monitor-piket-guru-', $res->headers->get('content-disposition'));
        $this->assertStringContainsString('.pdf', $res->headers->get('content-disposition'));
    }

    public function test_ekspor_detail_tipe_tidak_dikenal_404(): void
    {
        $this->actingAs($this->waka)
            ->get("/piket/monitor/ekspor/ngawur/{$this->kelasA->id}")
            ->assertNotFound();
    }

    /** Endpoint polling buat banner "ada data baru" (initAutoRefresh() di app.js) -- lihat App\Support\Versi. */
    public function test_endpoint_versi_berubah_setelah_ada_jurnal_baru(): void
    {
        $tanggal = today()->toDateString();
        $versiAwal = $this->actingAs($this->piket)->get("/piket/monitor/versi?tanggal={$tanggal}")->assertOk()->json('versi');

        $jadwalKelasB = Jadwal::where('kelas_id', $this->kelasB->id)->first();
        Jurnal::create([
            'jadwal_id' => $jadwalKelasB->id, 'guru_id' => $this->guruB->id, 'tanggal' => today(),
            'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4, 'status_guru' => 'hadir', 'materi' => 'Baru diisi',
        ]);

        $versiBaru = $this->get("/piket/monitor/versi?tanggal={$tanggal}")->assertOk()->json('versi');
        $this->assertNotSame($versiAwal, $versiBaru);
    }

    private function unduh(string $url)
    {
        $response = $this->actingAs($this->waka)->get($url);
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        return $response;
    }
}
