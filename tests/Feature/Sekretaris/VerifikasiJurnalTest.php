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

        $this->actingAs($this->sekretaris)->get("/sekretaris/jurnal/{$jurnal->id}/fragment")
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
            ->assertRedirect("/sekretaris/jurnal?lihat={$jurnal->id}");

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
            ->assertRedirect("/sekretaris/jurnal?lihat={$jurnal->id}");

        $this->assertSame('revisi', $jurnal->fresh()->status_verifikasi);
        $this->assertSame('Materi tidak sesuai', $jurnal->fresh()->catatan_verifikasi);
    }

    /**
     * Jurnal pending yang tanggalnya udah kelewat hari (bukan hari ini lagi)
     * otomatis terverifikasi -- pengurus kelas nggak sempat periksa, daripada
     * numpuk jadi pending berhari-hari (sama pola kayak auto-batal dispensasi).
     */
    public function test_jurnal_pending_yang_udah_lewat_hari_otomatis_terverifikasi(): void
    {
        $jurnal = $this->jurnalBaru(['tanggal' => today()->subDay()]);

        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal')->assertOk();

        $this->assertSame('terverifikasi', $jurnal->fresh()->status_verifikasi);
        $this->assertStringContainsString('Otomatis diverifikasi', $jurnal->fresh()->catatan_verifikasi);
    }

    public function test_jurnal_pending_hari_ini_belum_diotomatis_verifikasi(): void
    {
        $jurnal = $this->jurnalBaru(['tanggal' => today()]);

        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal')->assertOk();

        $this->assertSame('pending', $jurnal->fresh()->status_verifikasi);
    }

    /**
     * Jurnal "pending" (perlu diperiksa) muncul PALING ATAS, walau dibuat
     * duluan (id lebih kecil, biasanya kalah kalau urut cuma "terbaru
     * duluan") -- nggak kelewat ketumpuk jurnal lain yang udah diperiksa.
     * Sengaja dua-duanya tanggal HARI INI (bukan kemarin) biar nggak kena
     * sapu auto-verifikasi (lihat test_jurnal_pending_yang_udah_lewat_hari...)
     * duluan sebelum sempat dicek urutannya.
     */
    public function test_jurnal_pending_ditampilkan_paling_atas(): void
    {
        // Dibuat DULUAN (id lebih kecil) -- kalau urutnya cuma "id/tanggal
        // terbaru duluan" doang, ini bakal kalah sama yang dibuat belakangan.
        $pending = $this->jurnalBaru();
        $terverifikasi = $this->jurnalBaru(['status_verifikasi' => 'terverifikasi']);

        // Assert lewat posisi data-ajax-url per jurnal (unik per id) --
        // BUKAN teks status badge, soalnya "Perlu diperiksa"/"Terverifikasi"
        // juga muncul duluan di tab bar filter di atas daftar, jadi nggak
        // representatif buat ngecek urutan KARTU-nya.
        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal')
            ->assertOk()
            ->assertSeeInOrder([
                "jurnal/{$pending->id}/fragment",
                "jurnal/{$terverifikasi->id}/fragment",
            ]);
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

    public function test_jurnal_pengganti_selalu_tidak_hadir(): void
    {
        $ketua = Siswa::where('nis', '001')->firstOrFail();
        $anggota = Siswa::where('nis', '002')->firstOrFail();

        // status_guru bukan lagi field yang divalidasi/dipilih dari form --
        // jurnal pengganti = guru nggak hadir, jadi server SELALU simpen
        // 'tidak_hadir', nggak peduli inputnya (di sini sengaja dikirim
        // 'hadir' buat mastiin server nggak asal percaya input klien).
        $this->actingAs($this->sekretaris)->post('/sekretaris/jurnal/pengganti', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'status_guru' => 'hadir', 'tugas_tambahan' => 'Kerjakan LKS hal. 10', 'alasan' => 'Rapat dinas luar kota',
            'presensi' => [
                $ketua->id => ['status' => 'sakit'],
                $anggota->id => ['status' => 'hadir'],
            ],
        ])->assertRedirect();

        $jurnal = Jurnal::first();
        $this->assertSame('tidak_hadir', $jurnal->status_guru);
        $this->assertTrue($jurnal->diisi_oleh_pengurus);
        $this->assertSame('terverifikasi', $jurnal->status_verifikasi);
        $this->assertCount(2, $jurnal->absensis);
        // Pengurus kelas beneran bisa nandain siapa yang nggak hadir, bukan
        // ke-hardcode "hadir" semua.
        $this->assertSame('sakit', $jurnal->absensis()->where('siswa_id', $ketua->id)->value('status'));
    }

    /** Endpoint polling buat banner "ada data baru" (initAutoRefresh() di app.js) -- lihat App\Support\Versi. */
    public function test_endpoint_versi_berubah_setelah_ada_jurnal_baru(): void
    {
        $versiAwal = $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal/versi')->assertOk()->json('versi');

        $this->jurnalBaru();

        $versiBaru = $this->get('/sekretaris/jurnal/versi')->assertOk()->json('versi');
        $this->assertNotSame($versiAwal, $versiBaru);
    }
}
