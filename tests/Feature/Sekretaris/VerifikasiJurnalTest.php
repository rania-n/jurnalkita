<?php

namespace Tests\Feature\Sekretaris;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiJurnalTest extends TestCase
{
    use RefreshDatabase;

    private User $sekretaris;

    private Kelas $kelas;

    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->sekretaris = User::factory()->role('siswa')->create();
        Siswa::create([
            'user_id' => $this->sekretaris->id, 'kelas_id' => $this->kelas->id,
            'nis' => '001', 'nama' => 'Ketua Kelas', 'jenis_kelamin' => 'L',
            'no_absen' => 1, 'jabatan' => 'pengurus',
        ]);
        Siswa::create([
            'kelas_id' => $this->kelas->id, 'nis' => '002', 'nama' => 'Anggota',
            'jenis_kelamin' => 'P', 'no_absen' => 2,
        ]);

        $guru = Guru::create(['nama' => 'Pak Guru']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $this->jadwal = Jadwal::create([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
    }

    private function jurnalBaru(array $overrides = []): Jurnal
    {
        return Jurnal::create(array_merge([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->jadwal->guru_id,
            'tanggal' => today(), 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ], $overrides));
    }

    public function test_daftar_hanya_menampilkan_jurnal_kelas_sendiri(): void
    {
        $milikKelas = $this->jurnalBaru();

        $kelasLain = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        $jadwalLain = Jadwal::create([
            'kelas_id' => $kelasLain->id, 'mapel_id' => $this->jadwal->mapel_id,
            'guru_id' => $this->jadwal->guru_id, 'hari' => 'senin',
            'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);
        Jurnal::create([
            'jadwal_id' => $jadwalLain->id, 'guru_id' => $this->jadwal->guru_id,
            'tanggal' => today(), 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
            'status_guru' => 'hadir', 'materi' => 'Rahasia kelas lain',
        ]);

        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal')
            ->assertOk()
            ->assertSee('Matematika')
            ->assertDontSee('Rahasia kelas lain');
    }

    public function test_halaman_periksa_dan_pengganti_render(): void
    {
        $jurnal = $this->jurnalBaru();
        $jurnal->absensis()->create(['siswa_id' => Siswa::where('nis', '002')->value('id'), 'status' => 'hadir']);

        $this->actingAs($this->sekretaris)->get("/sekretaris/jurnal/{$jurnal->id}")
            ->assertOk()->assertSee('Presensi');
        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal/pengganti')
            ->assertOk()->assertSee('Jurnal Pengganti');
        $this->actingAs($this->sekretaris)->get('/sekretaris')
            ->assertOk()->assertSee('Verifikasi Jurnal');
    }

    public function test_verifikasi_menerima_jurnal(): void
    {
        $jurnal = $this->jurnalBaru();

        $this->actingAs($this->sekretaris)
            ->post("/sekretaris/jurnal/{$jurnal->id}/verifikasi", ['keputusan' => 'terima'])
            ->assertRedirect('/sekretaris/jurnal');

        $jurnal->refresh();
        $this->assertSame('terverifikasi', $jurnal->status_verifikasi);
        $this->assertSame($this->sekretaris->siswa->id, $jurnal->verifikator_id);
    }

    public function test_minta_revisi_wajib_catatan(): void
    {
        $jurnal = $this->jurnalBaru();

        $this->actingAs($this->sekretaris)
            ->post("/sekretaris/jurnal/{$jurnal->id}/verifikasi", ['keputusan' => 'revisi'])
            ->assertSessionHasErrors('catatan');

        $this->actingAs($this->sekretaris)
            ->post("/sekretaris/jurnal/{$jurnal->id}/verifikasi", ['keputusan' => 'revisi', 'catatan' => 'Materi tidak sesuai'])
            ->assertRedirect('/sekretaris/jurnal');

        $this->assertSame('revisi', $jurnal->fresh()->status_verifikasi);
        $this->assertSame('Materi tidak sesuai', $jurnal->fresh()->catatan_verifikasi);
    }

    public function test_sekretaris_lain_tidak_bisa_verifikasi_jurnal_bukan_kelasnya(): void
    {
        $jurnal = $this->jurnalBaru();

        $lain = User::factory()->role('siswa')->create();
        $kelasLain = Kelas::create(['nama' => 'X TKJ 1', 'tingkat' => 'X', 'jurusan' => 'TKJ']);
        Siswa::create([
            'user_id' => $lain->id, 'kelas_id' => $kelasLain->id, 'nis' => '999',
            'nama' => 'Lain', 'jenis_kelamin' => 'L', 'jabatan' => 'pengurus',
        ]);

        $this->actingAs($lain)
            ->post("/sekretaris/jurnal/{$jurnal->id}/verifikasi", ['keputusan' => 'terima'])
            ->assertForbidden();
    }

    public function test_jurnal_pengganti_hanya_tugas_atau_tidak_hadir(): void
    {
        $this->actingAs($this->sekretaris)->post('/sekretaris/jurnal/pengganti', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x',
        ])->assertSessionHasErrors('status_guru');

        $this->actingAs($this->sekretaris)->post('/sekretaris/jurnal/pengganti', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'tugas', 'materi' => 'Kerjakan LKS hal. 10',
        ])->assertRedirect();

        $jurnal = Jurnal::first();
        $this->assertTrue($jurnal->diisi_oleh_pengurus);
        $this->assertSame('terverifikasi', $jurnal->status_verifikasi);
        $this->assertCount(2, $jurnal->absensis);
    }
}
