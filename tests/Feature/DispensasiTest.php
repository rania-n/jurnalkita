<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispensasiTest extends TestCase
{
    use RefreshDatabase;

    private User $piket;

    private User $waka;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $this->waka = User::factory()->role('waka')->create();

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->siswa = Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'no_absen' => 1,
        ]);
    }

    public function test_hanya_guru_piket_bisa_buka_form_ajukan(): void
    {
        $this->actingAs($this->piket)->get('/dispensasi-ajukan/baru')->assertOk();

        $guruBiasa = User::factory()->role('guru')->create();
        $this->actingAs($guruBiasa)->get('/dispensasi-ajukan/baru')->assertForbidden();
    }

    public function test_halaman_daftar_dan_detail_render(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();

        $this->actingAs($this->waka)->get('/dispensasi')->assertOk()->assertSee('Budi');
        $this->actingAs($this->waka)->get("/dispensasi/{$d->id}")->assertOk()->assertSee('Setujui');
        $this->actingAs($this->waka)->get('/waka')->assertOk()->assertSee('Antrean Dispensasi');
    }

    public function test_ajukan_langsung_lolos_tahap_piket(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'Lomba LKS tingkat kabupaten',
        ])->assertRedirect('/dispensasi');

        $d = Dispensasi::first();
        $this->assertSame('approved', $d->status_piket);
        $this->assertSame('pending', $d->status_waka);
        $this->assertSame('pending', $d->status_akhir);
    }

    public function test_alur_penuh_approve_menerapkan_dispensasi_ke_presensi(): void
    {
        // presensi awal siswa untuk jurnal hari ini
        $guru = Guru::create(['nama' => 'Pengajar']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $this->siswa->kelas_id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $absensi = Absensi::create([
            'jurnal_id' => $jurnal->id, 'siswa_id' => $this->siswa->id, 'status' => 'hadir',
        ]);

        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'alasan' => 'Lomba',
        ]);
        $d = Dispensasi::first();

        $this->actingAs($this->waka)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved'])
            ->assertRedirect('/dispensasi');

        $d->refresh();
        $this->assertSame('approved', $d->status_akhir);
        $this->assertSame('dispensasi', $absensi->fresh()->status);
    }

    public function test_waka_menolak_menghentikan_alur(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'Lomba',
        ]);
        $d = Dispensasi::first();

        $this->actingAs($this->waka)->post("/dispensasi/{$d->id}/waka", [
            'keputusan' => 'rejected', 'catatan' => 'Tidak ada surat resmi',
        ]);

        $this->assertSame('rejected', $d->fresh()->status_akhir);
    }

    public function test_siswa_tidak_bisa_akses_dispensasi(): void
    {
        $akunSiswa = User::factory()->role('siswa')->create();
        $this->actingAs($akunSiswa)->get('/dispensasi')
            ->assertRedirect(route('sekretaris.dashboard'));
    }

    public function test_guru_piket_hanya_lihat_pengajuan_sendiri(): void
    {
        // Pengajuan oleh piket lain
        $piketLain = User::factory()->role('guru')->create();
        $guruLain = Guru::create(['user_id' => $piketLain->id, 'nama' => 'Piket Lain']);
        JadwalPiket::create(['guru_id' => $guruLain->id, 'hari' => 'selasa']);
        $milikOrang = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $piketLain->id,
            'tanggal' => today(), 'alasan' => 'X', 'status_piket' => 'approved',
        ]);

        $this->actingAs($this->piket)->get("/dispensasi/{$milikOrang->id}")->assertForbidden();
        $this->actingAs($this->piket)->get('/dispensasi')->assertOk()->assertDontSee('Piket Lain');
    }

    public function test_guru_bukan_waka_tidak_bisa_approve_waka(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'Lomba',
        ]);
        $d = Dispensasi::first();

        // role:waka middleware melempar guru piket kembali ke dashboard-nya
        $this->actingAs($this->piket)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved'])
            ->assertRedirect(route('guru.dashboard'));

        $this->assertSame('pending', $d->fresh()->status_waka);
    }
}
