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
            ->assertOk()->assertSee('Belum ada jadwal mengajar');
    }

    public function test_kartu_shortcut_piket_muncul_hanya_saat_guru_piket_hari_ini(): void
    {
        $this->travelTo(now()->next(Carbon::MONDAY)); // pin ke Senin, hindari flaky di akhir pekan

        $piket = User::factory()->role('guru')->create();
        $guruPiket = Guru::create(['user_id' => $piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guruPiket->id, 'hari' => 'senin']);

        $this->actingAs($piket)->get('/guru')
            ->assertOk()
            ->assertSee('Piket Hari Ini')
            ->assertSee('Ajukan Dispensasi');

        $bukanPiket = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $bukanPiket->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($bukanPiket)->get('/guru')
            ->assertOk()
            ->assertDontSee('Piket Hari Ini');
    }
}
