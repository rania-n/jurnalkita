<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\Kelas;
use App\Models\PresensiPiket;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiSiswaPiketTest extends TestCase
{
    use RefreshDatabase;

    private User $piket;

    private Kelas $kelas;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $this->kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->siswa = Siswa::create([
            'kelas_id' => $this->kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'no_absen' => 1,
        ]);
    }

    public function test_form_presensi_siswa_tidak_lagi_menampilkan_pilihan_dispen(): void
    {
        $this->actingAs($this->piket)
            ->get(route('piket.presensi-siswa.index', ['kelas_id' => $this->kelas->id]))
            ->assertOk()
            ->assertSee('Sakit')
            ->assertSee('Terlambat')
            ->assertDontSee('Dispen')
            ->assertDontSee('Lomba / Dinas');
    }

    public function test_status_dispensasi_ditolak_di_presensi_siswa(): void
    {
        $this->actingAs($this->piket)->post(route('piket.presensi-siswa.store'), [
            'tanggal' => today()->toDateString(),
            'kelas_id' => $this->kelas->id,
            'siswa_ids' => [$this->siswa->id],
            'status' => 'dispensasi',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseCount('presensi_pikets', 0);
    }

    public function test_presensi_izin_tetap_tersimpan(): void
    {
        $this->actingAs($this->piket)->post(route('piket.presensi-siswa.store'), [
            'tanggal' => today()->toDateString(),
            'kelas_id' => $this->kelas->id,
            'siswa_ids' => [$this->siswa->id],
            'status' => 'izin',
        ])->assertRedirect();

        $this->assertSame('izin', PresensiPiket::first()->status);
    }
}
