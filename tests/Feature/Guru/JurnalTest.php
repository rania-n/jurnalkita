<?php

namespace Tests\Feature\Guru;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengaturanJurnal;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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
        Storage::fake('public');

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ])->assertRedirect();

        $jurnal = Jurnal::first();
        $this->assertSame($this->guru->id, $jurnal->guru_id);
        $this->assertSame(today()->toDateString(), $jurnal->tanggal->toDateString());
        $this->assertCount(2, $jurnal->absensis);
        $this->assertTrue($jurnal->absensis->every(fn ($a) => $a->status === 'hadir'));
    }

    public function test_tidak_bisa_buat_jurnal_ganda_untuk_jadwal_sama_hari_ini(): void
    {
        Storage::fake('public');

        $payload = [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ];

        $this->actingAs($this->user)->post('/guru/jurnal', $payload)->assertRedirect();
        $this->actingAs($this->user)->post('/guru/jurnal', $payload)->assertRedirect();

        $this->assertSame(1, Jurnal::where('jadwal_id', $this->jadwal->id)->whereDate('tanggal', today())->count());
    }

    public function test_siswa_dengan_dispensasi_disetujui_otomatis_dispensasi(): void
    {
        Storage::fake('public');

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
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ]);

        $this->assertSame('dispensasi', Jurnal::first()->absensis()->where('siswa_id', $siswa->id)->value('status'));
    }

    public function test_form_jurnal_menampilkan_presensi_saat_jadwal_terpilih(): void
    {
        $this->actingAs($this->user)->get('/guru/jurnal/tambah?jadwal='.$this->jadwal->id)
            ->assertOk()->assertSee('Presensi')->assertSee('A')->assertSee('B');
    }

    public function test_simpan_jurnal_dengan_presensi_manual_dalam_satu_form(): void
    {
        Storage::fake('public');
        $siswaA = Siswa::where('nis', '001')->firstOrFail();
        $siswaB = Siswa::where('nis', '002')->firstOrFail();

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
            'presensi' => [
                $siswaA->id => ['status' => 'sakit', 'catatan' => 'demam'],
                $siswaB->id => ['status' => 'hadir'],
            ],
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ])->assertRedirect();

        $jurnal = Jurnal::first();
        $this->assertSame('sakit', $jurnal->absensis()->where('siswa_id', $siswaA->id)->value('status'));
        $this->assertSame('demam', $jurnal->absensis()->where('siswa_id', $siswaA->id)->value('catatan'));
        $this->assertNotNull($jurnal->foto_bukti);
        Storage::disk('public')->assertExists($jurnal->foto_bukti);
    }

    public function test_ubah_jurnal_dan_presensi_dalam_satu_form(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id, 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x',
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ]);
        $jurnal = Jurnal::first();
        $siswaA = Siswa::where('nis', '001')->firstOrFail();
        $siswaB = Siswa::where('nis', '002')->firstOrFail();

        $this->actingAs($this->user)->get("/guru/jurnal/{$jurnal->id}/ubah")
            ->assertOk()->assertSee('A')->assertSee('B');

        $this->actingAs($this->user)->post("/guru/jurnal/{$jurnal->id}", [
            'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'materi baru',
            'presensi' => [
                $siswaA->id => ['status' => 'sakit', 'catatan' => 'demam'],
                $siswaB->id => ['status' => 'hadir'],
            ],
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ])->assertRedirect("/guru/jurnal?lihat={$jurnal->id}");

        $jurnal->refresh();
        $this->assertSame('materi baru', $jurnal->materi);
        $this->assertSame('sakit', $jurnal->absensis()->where('siswa_id', $siswaA->id)->value('status'));
        $this->assertSame('demam', $jurnal->absensis()->where('siswa_id', $siswaA->id)->value('catatan'));
        $this->assertNotNull($jurnal->foto_bukti);
        Storage::disk('public')->assertExists($jurnal->foto_bukti);
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

        $this->actingAs($lain)->get("/guru/jurnal/{$jurnal->id}/fragment")->assertForbidden();
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

    public function test_guru_bisa_hapus_jurnal_yang_belum_diverifikasi(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id, 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'salah kelas',
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ]);
        $jurnal = Jurnal::firstOrFail();

        $this->actingAs($this->user)->delete("/guru/jurnal/{$jurnal->id}")
            ->assertRedirect('/guru/jurnal');

        $this->assertSoftDeleted('jurnals', ['id' => $jurnal->id]);
    }

    public function test_jurnal_yang_sudah_diverifikasi_tidak_bisa_dihapus(): void
    {
        $jurnal = Jurnal::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'tanggal' => today(), 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x', 'status_verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($this->user)->delete("/guru/jurnal/{$jurnal->id}")->assertForbidden();
        $this->assertNotSoftDeleted('jurnals', ['id' => $jurnal->id]);
    }

    public function test_guru_lain_tidak_bisa_hapus_jurnal_orang(): void
    {
        $jurnal = Jurnal::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'tanggal' => today(), 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x',
        ]);

        $lain = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $lain->id, 'nama' => 'Guru Lain']);

        $this->actingAs($lain)->delete("/guru/jurnal/{$jurnal->id}")->assertForbidden();
        $this->assertNotSoftDeleted('jurnals', ['id' => $jurnal->id]);
    }

    public function test_presensi_ikut_jurnal_lain_di_kelas_sama_hari_ini(): void
    {
        $siswaA = $this->jadwal->kelas->siswas()->where('nis', '001')->first();

        // Jurnal pertama (JP 1-2): siswa A ditandai sakit.
        $jurnal1 = Jurnal::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'tanggal' => today(), 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $jurnal1->absensis()->create(['siswa_id' => $siswaA->id, 'status' => 'sakit', 'catatan' => 'Demam']);
        $jurnal1->absensis()->create([
            'siswa_id' => $this->jadwal->kelas->siswas()->where('nis', '002')->value('id'),
            'status' => 'hadir',
        ]);

        // Jadwal kedua, JP beda, kelas SAMA, guru SAMA (biar gampang di-akses lewat 1 user) -- form Isi Jurnal
        // jadwal kedua ini harusnya nawarin siswa A sebagai "sakit" duluan, bukan "hadir" dari nol.
        $jadwal2 = Jadwal::create([
            'kelas_id' => $this->jadwal->kelas_id, 'mapel_id' => $this->jadwal->mapel_id, 'guru_id' => $this->guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4,
        ]);

        $response = $this->actingAs($this->user)->get("/guru/jurnal/tambah?jadwal={$jadwal2->id}");

        $response->assertOk();
        $presensiAwal = $response->viewData('presensiAwal');
        $this->assertSame('sakit', $presensiAwal[$siswaA->id]['status']);
        $this->assertSame('Demam', $presensiAwal[$siswaA->id]['catatan']);
    }

    /* ---------------------- Pengaturan mode isi jurnal (Admin\PengaturanJurnalController) --------------------- */

    public function test_mode_disiplin_default_diblokir_pas_istirahat(): void
    {
        $this->travelTo(Carbon::parse('next monday 09:30:00'));
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '09:00']);
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 2, 'mulai' => '09:40', 'selesai' => '10:20']);

        Storage::fake('public');

        // Default (belum ada baris PengaturanJurnal sama sekali) -> 'disiplin'.
        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ])->assertForbidden();
    }

    public function test_mode_bebas_hari_ini_boleh_isi_pas_istirahat(): void
    {
        $this->travelTo(Carbon::parse('next monday 09:30:00'));
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '09:00']);
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 2, 'mulai' => '09:40', 'selesai' => '10:20']);
        PengaturanJurnal::ambil()->update(['mode' => 'bebas_hari_ini']);

        Storage::fake('public');

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Bab 1',
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ])->assertRedirect();

        $jurnal = Jurnal::where('jadwal_id', $this->jadwal->id)->firstOrFail();
        $this->assertTrue($jurnal->tanggal->isSameDay(today()));
    }

    public function test_mode_bebas_kemarin_boleh_isi_susulan_jadwal_kemarin(): void
    {
        // $this->jadwal hari-nya 'senin' -- pindah ke SELASA biar "kemarin" = senin.
        $this->travelTo(Carbon::parse('next tuesday 10:00:00'));
        PengaturanJurnal::ambil()->update(['mode' => 'bebas_kemarin']);

        Storage::fake('public');

        $this->actingAs($this->user)->post('/guru/jurnal', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'materi' => 'Susulan kemarin',
            'foto_bukti' => UploadedFile::fake()->image('kelas.jpg'),
        ])->assertRedirect();

        $jurnal = Jurnal::where('jadwal_id', $this->jadwal->id)->firstOrFail();
        $this->assertTrue($jurnal->tanggal->isSameDay(Carbon::yesterday()));
    }

    public function test_form_isi_jurnal_kemarin_render_pas_mode_bebas_kemarin(): void
    {
        $this->travelTo(Carbon::parse('next tuesday 10:00:00'));
        PengaturanJurnal::ambil()->update(['mode' => 'bebas_kemarin']);

        $this->actingAs($this->user)->get('/guru/jurnal/tambah?hari=kemarin')
            ->assertOk()->assertSee('Kemarin');
    }
}
