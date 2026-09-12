<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalPiketKonflikTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Guru $guru;

    private Kelas $kelas;

    private Mapel $mapel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->role('admin')->create();
        $this->guru = Guru::create(['nama' => 'Bu Sarah']);
        $this->kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        // Jam ke 1-2 = 07:00-08:30, buat cek tabrakan.
        JamPelajaran::create(['jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:45', 'kategori' => 'senin_kamis']);
        JamPelajaran::create(['jam_ke' => 2, 'mulai' => '07:45', 'selesai' => '08:30', 'kategori' => 'senin_kamis']);
        JamPelajaran::create(['jam_ke' => 3, 'mulai' => '08:30', 'selesai' => '09:15', 'kategori' => 'senin_kamis']);
    }

    private function jadwalPayload(array $override = []): array
    {
        return array_merge([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guru->id,
            'hari' => 'senin',
            'jam_ke_mulai' => 1,
            'jam_ke_selesai' => 2,
        ], $override);
    }

    public function test_jadwal_ditolak_kalau_bentrok_shift_piket(): void
    {
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'mulai' => '07:00', 'selesai' => '08:00']);

        $this->actingAs($this->admin)->post('/admin/jadwal-pelajaran', $this->jadwalPayload())
            ->assertSessionHas('error');

        $this->assertSame(0, Jadwal::count());
    }

    public function test_jadwal_boleh_di_luar_jam_shift_piket(): void
    {
        // Piket cuma jam ke 1 (07:00-07:45), jadwal mengajar mulai jam ke 3 (08:30) -> tidak bentrok.
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'mulai' => '07:00', 'selesai' => '07:45']);

        $this->actingAs($this->admin)->post('/admin/jadwal-pelajaran', $this->jadwalPayload([
            'jam_ke_mulai' => 3, 'jam_ke_selesai' => 3,
        ]))->assertSessionHasNoErrors()->assertSessionMissing('error');

        $this->assertSame(1, Jadwal::count());
    }

    public function test_piket_sehari_penuh_tanpa_jam_spesifik_selalu_bentrok(): void
    {
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin']); // mulai/selesai null

        $this->actingAs($this->admin)->post('/admin/jadwal-pelajaran', $this->jadwalPayload())
            ->assertSessionHas('error');

        $this->assertSame(0, Jadwal::count());
    }

    public function test_guru_lain_tidak_terpengaruh_piket_guru_lain(): void
    {
        $guruLain = Guru::create(['nama' => 'Pak Herman']);
        JadwalPiket::create(['guru_id' => $guruLain->id, 'hari' => 'senin', 'mulai' => '07:00', 'selesai' => '08:00']);

        $this->actingAs($this->admin)->post('/admin/jadwal-pelajaran', $this->jadwalPayload())
            ->assertSessionMissing('error');

        $this->assertSame(1, Jadwal::count());
    }
}
