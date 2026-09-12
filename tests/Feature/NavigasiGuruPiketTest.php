<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NavigasiGuruPiketTest extends TestCase
{
    use RefreshDatabase;

    public function test_nav_piket_dispensasi_nongol_pas_hari_piketnya(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $guru = User::factory()->role('guru')->create();
        $g = Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $g->id, 'hari' => 'senin']);

        $this->assertTrue($guru->piketHariIni());

        $this->actingAs($guru)->get('/guru')->assertOk()->assertSee('Piket')->assertSee('Dispensasi');
    }

    public function test_nav_piket_dispensasi_disembunyikan_di_luar_hari_piketnya(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $guru = User::factory()->role('guru')->create();
        $g = Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $g->id, 'hari' => 'rabu']); // bukan hari ini (senin)

        $this->assertFalse($guru->piketHariIni());

        $this->actingAs($guru)->get('/guru')->assertOk()->assertDontSee('Dispensasi');
    }

    public function test_guru_tanpa_piket_sama_sekali_tidak_pernah_lihat_menu_piket(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $guru = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guru)->get('/guru')->assertOk()->assertDontSee('Dispensasi');
    }

    public function test_akses_fitur_dispensasi_tetap_kebuka_walau_bukan_hari_piketnya(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $guru = User::factory()->role('guru')->create();
        $g = Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $g->id, 'hari' => 'rabu']); // bukan hari ini

        // Nav-nya nggak nampilin menu piket, tapi HALAMAN & aksinya tetap kebuka --
        // isPiket() (permanen) yang dipakai buat akses, bukan piketHariIni() (cuma nav).
        $this->actingAs($guru)->get('/dispensasi-ajukan/baru')->assertOk();
        $this->actingAs($guru)->get('/dispensasi')->assertOk();
    }
}
