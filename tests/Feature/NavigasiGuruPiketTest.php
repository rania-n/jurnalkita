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

        // Hari piket: cuma piket & dispensasi, jurnal/jadwal mengajar sengaja disembunyikan
        // (baik di nav maupun di badan halaman Beranda) -- hari itu dia nggak ngajar.
        $this->actingAs($guru)->get('/guru')->assertOk()
            ->assertSee('Piket')->assertSee('Dispensasi')
            ->assertDontSee('Jadwal Mengajar Hari Ini')->assertDontSee('Riwayat Jurnal');
    }

    public function test_nav_piket_dispensasi_disembunyikan_di_luar_hari_piketnya(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $guru = User::factory()->role('guru')->create();
        $g = Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $g->id, 'hari' => 'rabu']); // bukan hari ini (senin)

        $this->assertFalse($guru->piketHariIni());

        // Bukan hari piket: balik ke nav guru biasa, jadwal mengajar tetap tampil.
        $this->actingAs($guru)->get('/guru')->assertOk()
            ->assertDontSee('Dispensasi')->assertSee('Jadwal Mengajar Hari Ini');
    }

    /**
     * Riwayat Jurnal tetap disembunyikan hari piket (dia nggak ngajar hari
     * itu). Tapi menu "Piket" yang dulu berdiri sendiri (cuma nampilin jadwal
     * piket doang, dobel/kurang fungsi) udah dihapus & diganti "Jadwal" --
     * halaman Jadwal udah nampilin jadwal piket + kartu Monitor Piket +
     * info Dispensasi sekaligus.
     */
    public function test_menu_hari_piket_ganti_jadwal_bukan_jurnal(): void
    {
        $labels = collect(config('navigation.guru-piket'))->pluck('label');

        $this->assertFalse($labels->contains('Riwayat'));
        $this->assertFalse($labels->contains('Piket'));
        $this->assertTrue($labels->contains('Jadwal'));
        $this->assertTrue($labels->contains('Dispensasi'));
    }

    public function test_guru_tanpa_piket_sama_sekali_tidak_pernah_lihat_menu_piket(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $guru = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guru)->get('/guru')->assertOk()->assertDontSee('Dispensasi');
    }

    public function test_chip_header_bilang_guru_piket_pas_hari_piketnya(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $guru = User::factory()->role('guru')->create();
        $g = Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $g->id, 'hari' => 'senin']);

        $this->actingAs($guru)->get('/guru')->assertOk()->assertSee('Guru Piket');

        JadwalPiket::query()->update(['hari' => 'rabu']); // pindah, bukan hari ini lagi
        $this->actingAs($guru)->get('/guru')->assertOk()->assertDontSee('Guru Piket');
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
