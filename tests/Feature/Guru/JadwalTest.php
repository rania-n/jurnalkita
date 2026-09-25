<?php

namespace Tests\Feature\Guru;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalTest extends TestCase
{
    use RefreshDatabase;

    public function test_jadwal_seminggu_dikelompokkan_per_hari_dan_urut_jam(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Bu Sarah']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'rabu', 'jam_ke_mulai' => 5, 'jam_ke_selesai' => 6,
        ]);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        // Jadwal guru lain tidak boleh ikut kebawa
        $lain = Guru::create(['nama' => 'Pak Budi']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $lain->id,
            'hari' => 'senin', 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);

        $response = $this->actingAs($user)->get('/guru/jadwal');

        $response->assertOk()
            ->assertSeeInOrder(['Senin', 'Rabu'])
            ->assertSee('Matematika');

        // Hari tanpa jadwal (selasa, kamis, jumat) tidak ditampilkan sebagai kartu kosong
        $response->assertDontSee('Tidak ada jadwal mengajar');
    }

    public function test_guru_belum_punya_jadwal_menampilkan_empty_state(): void
    {
        $user = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $user->id, 'nama' => 'Guru Baru']);

        $this->actingAs($user)->get('/guru/jadwal')
            ->assertOk()->assertSee('Belum ada jadwal');
    }

    public function test_tab_hari_memfilter_jadwal_dan_piket(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Bu Sarah']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'rabu']);

        // Hari dengan jadwal -> muncul, tanpa error (regresi: Collection::only()
        // meledak kalau dipanggil di atas hasil groupBy(), lihat JadwalController).
        $this->actingAs($user)->get('/guru/jadwal?hari=senin')
            ->assertOk()->assertSee('Matematika')->assertDontSee('Piket Harian');

        $this->actingAs($user)->get('/guru/jadwal?hari=rabu')
            ->assertOk()->assertSee('Piket Harian')->assertDontSee('Matematika');

        // Hari tanpa apa-apa -> empty state, bukan error 500
        $this->actingAs($user)->get('/guru/jadwal?hari=selasa')
            ->assertOk()->assertSee('Belum ada jadwal');
    }

    public function test_kartu_shortcut_piket_muncul_hanya_saat_guru_piket_hari_ini(): void
    {
        $this->travelTo(now()->next(Carbon::MONDAY)); // pin ke Senin, hindari flaky di akhir pekan

        $piket = User::factory()->role('guru')->create();
        $guruPiket = Guru::create(['user_id' => $piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guruPiket->id, 'hari' => 'senin']);

        $this->actingAs($piket)->get('/guru')
            ->assertOk()
            ->assertSee('Monitor Piket')
            ->assertSee('Buat Dispen');

        $bukanPiket = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $bukanPiket->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($bukanPiket)->get('/guru')
            ->assertOk()
            ->assertDontSee('Monitor Piket');
    }

    /**
     * Dulu ada halaman "Piket" sendiri (route piket.index) yang isinya cuma
     * jadwal piket doang -- dobel sama yang udah ditampilin di sini, jadi
     * dihapus. Kartu "Monitor Piket" & info Dispensasi yang tadinya di situ
     * dipindah ke halaman Jadwal ini.
     */
    public function test_kartu_monitor_piket_dan_info_dispensasi_muncul_kalau_ada_jadwal_piket(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $this->actingAs($user)->get('/guru/jadwal')
            ->assertOk()->assertSee('Monitor Piket')->assertSee('mengajukan dispensasi siswa', false);
    }

    public function test_kartu_monitor_piket_tidak_muncul_kalau_guru_tidak_ada_jadwal_piket(): void
    {
        $user = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $user->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($user)->get('/guru/jadwal')
            ->assertOk()->assertDontSee('Monitor Piket');
    }

    /**
     * Kartu Monitor Piket & info Dispensasi harus TETAP kelihatan walau lagi
     * nge-filter ke hari yang kebetulan bukan jadwal piketnya -- ini bukan
     * soal "piket hari ini", tapi "guru ini emang ada jadwal piket sama
     * sekali" (lihat $adaPiket, dihitung sebelum difilter per-hari).
     */
    public function test_kartu_monitor_piket_tetap_muncul_walau_difilter_ke_hari_lain(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'rabu']);

        $this->actingAs($user)->get('/guru/jadwal?hari=senin')
            ->assertOk()->assertSee('Monitor Piket');
    }

    public function test_route_halaman_piket_lama_sudah_tidak_ada(): void
    {
        $user = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $user->id, 'nama' => 'Guru Piket']);

        $this->actingAs($user)->get('/guru/piket')->assertNotFound();
    }
}
