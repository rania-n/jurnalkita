<?php

namespace Tests\Feature\Guru;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JurnalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Guru $guru;

    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->role('guru')->create();
        $this->guru = Guru::create(['user_id' => $this->user->id, 'nama' => 'Pak Guru']);

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'A', 'jenis_kelamin' => 'L', 'no_absen' => 1]);
        Siswa::create(['kelas_id' => $kelas->id, 'nis' => '002', 'nama' => 'B', 'jenis_kelamin' => 'P', 'no_absen' => 2]);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        $this->jadwal = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $this->guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
    }

    public function test_form_jurnal_render(): void
    {
        $this->actingAs($this->user)->get('/guru/jurnal/tambah')->assertOk()->assertsee('Form Jurnal');
    }

    public function test_simpan_jurnal_membuat_absensi_default_hadir(): void
    {
        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ])->assertRedirect();

        $jurnal = Jurnal::first();
        $this->assertSame($this->guru->id, $jurnal->guru_id);
        $this->assertSame(today()->toDateString(), $jurnal->tanggal->toDateString());
        $this->assertCount(2, $jurnal->absensis);
        $this->assertTrue($jurnal->absensis->every(fn ($a) => $a->status === 'hadir'));
    }

    public function test_tidak_bisa_buat_jurnal_ganda_untuk_jadwal_sama_hari_ini(): void
    {
        $payload = [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ];

        $this->actingAs($this->user)->post('/guru/jurnal', $payload)->assertRedirect();
        $this->actingAs($this->user)->post('/guru/jurnal', $payload)->assertRedirect();

        $this->assertSame(1, Jurnal::where('jadwal_id', $this->jadwal->id)->whereDate('tanggal', today())->count());
    }

    public function test_siswa_dengan_dispensasi_disetujui_otomatis_dispensasi(): void
    {
        $siswa = Siswa::first();
        Dispensasi::create([
            'siswa_id' => $siswa->id,
            'diajukan_oleh_id' => $this->user->id,
            'tanggal' => today(),
            'alasan' => 'Lomba',
            'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ]);

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ]);

        $this->assertSame('dispensasi', Jurnal::first()->absensis()->where('siswa_id', $siswa->id)->value('status'));
    }

    public function test_simpan_presensi_dan_foto(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id, 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $jurnal = Jurnal::first();
        $absen = $jurnal->absensis->first();

        $this->actingAs($this->user)->post("/guru/jurnal/{$jurnal->id}/presensi", [
            'presensi' => [
                $absen->id => ['status' => 'sakit', 'catatan' => 'demam'],
                $jurnal->absensis->last()->id => ['status' => 'hadir'],
            ],
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ])->assertRedirect("/guru/jurnal/{$jurnal->id}");

        $this->assertSame('sakit', $absen->fresh()->status);
        $this->assertSame('demam', $absen->fresh()->catatan);
        $this->assertNotNull($jurnal->fresh()->foto_bukti);
        Storage::disk('public')->assertExists($jurnal->fresh()->foto_bukti);
    }

    public function test_guru_lain_tidak_bisa_akses_jurnal_orang(): void
    {
        $jurnal = Jurnal::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'tanggal' => today(), 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x',
        ]);

        $lain = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $lain->id, 'nama' => 'Guru Lain']);

        $this->actingAs($lain)->get("/guru/jurnal/{$jurnal->id}")->assertForbidden();
    }

    public function test_jurnal_terverifikasi_tidak_bisa_diubah(): void
    {
        $jurnal = Jurnal::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'tanggal' => today(), 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x', 'status_verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($this->user)->post("/guru/jurnal/{$jurnal->id}", [
            'jam_ke_selesai' => 3, 'status_guru' => 'hadir', 'materi' => 'diubah',
        ])->assertForbidden();
    }
}
