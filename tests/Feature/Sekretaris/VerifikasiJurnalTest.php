<?php

namespace Tests\Feature\Sekretaris;

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
use Illuminate\Support\Carbon;
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
            ->assertOk()
            ->assertSee('Data Sudah Sesuai')
            ->assertSee('Perlu Diperbaiki');
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

    /** Jurnal tetap menunggu pemeriksaan sekre walau tanggal mengajarnya sudah lewat. */
    public function test_jurnal_pending_yang_udah_lewat_hari_tetap_menunggu_pemeriksaan(): void
    {
        $jurnal = $this->jurnalBaru(['tanggal' => today()->subDay()]);

        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal')
            ->assertOk()
            ->assertSee("jurnal/{$jurnal->id}/fragment");

        $this->assertSame('pending', $jurnal->fresh()->status_verifikasi);
        $this->assertTrue($jurnal->fresh()->menungguPemeriksaan());
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
     * Keduanya tanggal HARI INI agar urutan diuji dalam satu antrean pemeriksaan.
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

    public function test_laporan_guru_tidak_hadir_tidak_masuk_antrean_pemeriksaan(): void
    {
        $jurnal = $this->jurnalBaru([
            'status_guru' => 'tidak_hadir',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->assertFalse($jurnal->menungguPemeriksaan());
        $this->assertSame(0, Jurnal::query()->inReviewQueue()->count());
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

    /* ---------------------- Aturan mode isi jurnal buat Jurnal Pengganti (sama kayak Isi Jurnal Guru) --------------------- */

    public function test_pengganti_mode_disiplin_diblokir_pas_istirahat(): void
    {
        // Istirahat -- di antara JP1 (selesai 09:00) & JP2 (mulai 09:40).
        $this->travelTo(Carbon::parse('next monday 09:30:00'));
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '09:00']);
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 2, 'mulai' => '09:40', 'selesai' => '10:20']);

        // Default (belum ada baris PengaturanJurnal sama sekali) -> 'disiplin'.
        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal/pengganti')
            ->assertOk()->assertSee('Belum waktunya mengisi jurnal');

        $ketua = Siswa::where('nis', '001')->firstOrFail();
        $this->actingAs($this->sekretaris)->post('/sekretaris/jurnal/pengganti', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'tugas_tambahan' => 'Kerjakan LKS', 'alasan' => 'Rapat dinas',
            'presensi' => [$ketua->id => ['status' => 'hadir']],
        ])->assertForbidden();
    }

    public function test_pengganti_mode_disiplin_terkunci_ke_jp_aktif(): void
    {
        // "next monday" DIPANGGIL SEKALI & disimpan -- manggil "next monday"
        // lagi belakangan SETELAH waktunya dibekukan ke hari Senin bakal
        // malah loncat ke Senin MINGGU DEPANNYA (perilaku relative date PHP),
        // bukan hari yang sama.
        $senin = Carbon::parse('next monday 08:00:00');
        $this->travelTo($senin);
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '09:00']);
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 2, 'mulai' => '09:40', 'selesai' => '10:20']);

        // Terkunci -- dropdown biasa nggak muncul, langsung ke jadwal yang lagi
        // berlangsung (Matematika, JP1-2). Teks penanda field terkunci
        // dipakai buat cek (bukan assertDontSee('Pilih jadwal') -- keterangan
        // jam di bawahnya juga punya kalimat "Pilih jadwal dulu..." yang
        // selalu tampil apa pun kondisinya).
        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal/pengganti')
            ->assertOk()->assertSee('Matematika')
            ->assertSee('Otomatis mengikuti jam pelajaran yang sedang berlangsung sekarang.');

        $ketua = Siswa::where('nis', '001')->firstOrFail();
        $this->actingAs($this->sekretaris)->post('/sekretaris/jurnal/pengganti', [
            'jadwal_id' => $this->jadwal->id,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'tugas_tambahan' => 'Kerjakan LKS', 'alasan' => 'Rapat dinas',
            'presensi' => [$ketua->id => ['status' => 'hadir']],
        ])->assertRedirect();

        $this->assertTrue(Jurnal::first()->tanggal->isSameDay($senin));
    }

    public function test_pengganti_mode_bebas_selamanya_bisa_pilih_tanggal_custom(): void
    {
        $this->travelTo(Carbon::parse('next tuesday 10:00:00'));
        PengaturanJurnal::ambil()->update(['mode' => 'bebas_selamanya']);

        // $this->jadwal hari-nya 'senin' -- tanggal custom 2 minggu lalu senin.
        $tglCustom = Carbon::parse('2 weeks ago monday')->toDateString();

        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal/pengganti?tanggal='.$tglCustom)
            ->assertOk()->assertSee('Bebas Isi Jurnal (Tanggal Pilihan Sendiri)')->assertSee('Matematika');

        $ketua = Siswa::where('nis', '001')->firstOrFail();
        $this->actingAs($this->sekretaris)->post('/sekretaris/jurnal/pengganti', [
            'jadwal_id' => $this->jadwal->id,
            'tanggal' => $tglCustom,
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
            'tugas_tambahan' => 'Kerjakan LKS', 'alasan' => 'Rapat dinas',
            'presensi' => [$ketua->id => ['status' => 'hadir']],
        ])->assertRedirect();

        $jurnal = Jurnal::where('jadwal_id', $this->jadwal->id)->firstOrFail();
        $this->assertSame($tglCustom, $jurnal->tanggal->toDateString());
    }

    public function test_pengganti_tidak_tampilkan_jadwal_yang_sudah_diisi(): void
    {
        $this->travelTo(Carbon::parse('next monday 12:00:00'));
        PengaturanJurnal::ambil()->update(['mode' => 'bebas_hari_ini']);

        $this->jurnalBaru();

        $this->actingAs($this->sekretaris)->get('/sekretaris/jurnal/pengganti')
            ->assertOk()
            ->assertSee('Semua jadwal sudah diisi')
            ->assertDontSee('Pilih jadwal');
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
