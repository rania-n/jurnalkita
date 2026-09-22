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

    private Guru $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->piket = User::factory()->role('guru')->create();
        $this->guru = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin']);

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
        $this->actingAs($this->waka)->get("/dispensasi/{$d->id}/fragment")->assertOk()->assertSee('Setujui');
        $this->actingAs($this->waka)->get('/waka')->assertOk()->assertSee('Antrean Dispensasi');
    }

    public function test_ajukan_langsung_lolos_tahap_piket(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'Lomba LKS tingkat kabupaten',
        ])->assertRedirect();

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
            ->assertRedirect("/dispensasi?lihat={$d->id}");

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

    public function test_semua_guru_piket_bisa_lihat_dispensasi_guru_piket_lain(): void
    {
        // Pengajuan oleh piket lain -- dispensasi bukan "milik" guru yang mengajukan,
        // tapi ranahnya guru PIKET + Waka (bukan buat semua guru).
        $piketLain = User::factory()->role('guru')->create();
        $guruLain = Guru::create(['user_id' => $piketLain->id, 'nama' => 'Piket Lain']);
        JadwalPiket::create(['guru_id' => $guruLain->id, 'hari' => 'selasa']);
        $milikOrang = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $piketLain->id,
            'tanggal' => today(), 'alasan' => 'X', 'status_piket' => 'approved',
        ]);

        $this->actingAs($this->piket)->get("/dispensasi/{$milikOrang->id}/fragment")->assertOk();
        $this->actingAs($this->piket)->get('/dispensasi')->assertOk()->assertSee('Budi');
    }

    public function test_guru_yang_bukan_piket_tidak_relevan_tidak_bisa_lihat_dispensasi(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'X', 'status_piket' => 'approved',
        ]);

        $guruBukanPiket = User::factory()->role('guru')->create();
        $this->actingAs($guruBukanPiket)->get("/dispensasi/{$d->id}/fragment")->assertForbidden();
        $this->actingAs($guruBukanPiket)->get('/dispensasi')->assertForbidden();
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

    public function test_pengaju_bisa_membatalkan_selama_waka_belum_memutuskan(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'salah pilih siswa',
        ]);
        $d = Dispensasi::firstOrFail();

        $this->actingAs($this->piket)->delete("/dispensasi/{$d->id}")
            ->assertRedirect('/dispensasi');

        $this->assertSoftDeleted('dispensasis', ['id' => $d->id]);
    }

    public function test_tidak_bisa_dibatalkan_setelah_waka_memutuskan(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'Lomba',
        ]);
        $d = Dispensasi::firstOrFail();

        $this->actingAs($this->waka)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved']);

        $this->actingAs($this->piket)->delete("/dispensasi/{$d->id}")->assertForbidden();
        $this->assertNotSoftDeleted('dispensasis', ['id' => $d->id]);
    }

    public function test_piket_lain_tidak_bisa_membatalkan_pengajuan_orang(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'Lomba',
        ]);
        $d = Dispensasi::firstOrFail();

        $piketLain = User::factory()->role('guru')->create();
        $guruLain = Guru::create(['user_id' => $piketLain->id, 'nama' => 'Piket Lain']);
        JadwalPiket::create(['guru_id' => $guruLain->id, 'hari' => 'selasa']);

        $this->actingAs($piketLain)->delete("/dispensasi/{$d->id}")->assertForbidden();
        $this->assertNotSoftDeleted('dispensasis', ['id' => $d->id]);
    }

    public function test_setelah_ajukan_langsung_diarahkan_buka_wa_ke_waka(): void
    {
        $this->waka->update(['no_hp' => '081234567890']);

        $response = $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'alasan' => 'Lomba',
        ]);
        $d = Dispensasi::firstOrFail();
        $response->assertRedirect("/dispensasi?kirim_wa={$d->id}&lihat={$d->id}");

        // Riwayat-nya langsung nampilkan tautan wa.me yang di-klik otomatis via JS,
        // bukan nunggu tap tombol -- dan bukan halaman detail/form (biar nggak
        // nyangkut di situ kalau kirim WA-nya dibatalkan). Popup detailnya juga
        // langsung kebuka otomatis (data-auto-open-dispensasi, sama pola kayak
        // abis Waka mutusin), biar piket langsung lihat ringkasannya.
        $this->followRedirects($response)->assertSee('wa.me', false)
            ->assertSee('data-auto-open-dispensasi', false);
    }

    public function test_jam_mulai_saja_boleh_tanpa_jam_selesai(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'jam_ke_mulai' => 4,
            'alasan' => 'Ambil rapor lomba',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('dispensasis', ['jam_ke_mulai' => 4, 'jam_ke_selesai' => null]);
    }

    public function test_jam_selesai_tanpa_jam_mulai_ditolak(): void
    {
        $this->actingAs($this->piket)->post('/dispensasi', [
            'siswa_id' => $this->siswa->id,
            'tanggal' => today()->toDateString(),
            'jam_ke_selesai' => 4,
            'alasan' => 'x',
        ])->assertSessionHasErrors('jam_ke_mulai');
    }

    public function test_dispensasi_jam_mulai_saja_menerapkan_ke_absensi_sampai_akhir_hari(): void
    {
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $kelas = $this->siswa->kelas;
        $jadwalPagi = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $this->guru->id,
            'ruang' => 'R1', 'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jadwalSore = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $jadwalPagi->guru_id,
            'ruang' => 'R1', 'hari' => 'senin', 'jam_ke_mulai' => 5, 'jam_ke_selesai' => 6,
        ]);
        $jurnalPagi = Jurnal::create([
            'jadwal_id' => $jadwalPagi->id, 'guru_id' => $jadwalPagi->guru_id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $jurnalSore = Jurnal::create([
            'jadwal_id' => $jadwalSore->id, 'guru_id' => $jadwalSore->guru_id, 'tanggal' => today(),
            'jam_ke_mulai' => 5, 'jam_ke_selesai' => 6, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        $absensiPagi = Absensi::create(['jurnal_id' => $jurnalPagi->id, 'siswa_id' => $this->siswa->id, 'status' => 'hadir']);
        $absensiSore = Absensi::create(['jurnal_id' => $jurnalSore->id, 'siswa_id' => $this->siswa->id, 'status' => 'hadir']);

        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'jam_ke_mulai' => 4, 'alasan' => 'Pulang lebih awal',
            'status_piket' => 'approved',
        ]);
        $this->actingAs($this->waka)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved']);

        // Jam 1-2 (sebelum jam mulai dispensasi) tetap hadir, jam 5-6 (setelah) jadi dispensasi.
        $this->assertSame('hadir', $absensiPagi->fresh()->status);
        $this->assertSame('dispensasi', $absensiSore->fresh()->status);
    }

    public function test_dispensasi_beberapa_hari_menerapkan_ke_absensi_tiap_hari(): void
    {
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $kelas = $this->siswa->kelas;

        $jadwal = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $this->guru->id,
            'ruang' => 'R1', 'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $absensiHari = collect(range(0, 2))->map(function ($i) use ($jadwal) {
            $jurnal = Jurnal::create([
                'jadwal_id' => $jadwal->id, 'guru_id' => $jadwal->guru_id, 'tanggal' => today()->addDays($i),
                'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
            ]);

            return Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $this->siswa->id, 'status' => 'hadir']);
        });

        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'tanggal_selesai' => today()->addDays(2),
            'alasan' => 'Sakit 3 hari', 'status_piket' => 'approved',
        ]);
        $this->actingAs($this->waka)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved']);

        $absensiHari->each(fn (Absensi $a) => $this->assertSame('dispensasi', $a->fresh()->status));
    }

    public function test_dispensasi_disetujui_yang_lewat_tanggal_dikategorikan_kadaluarsa(): void
    {
        $aktif = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba', 'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ]);
        $lewat = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today()->subDays(3), 'alasan' => 'Sakit minggu lalu',
            'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ]);

        $this->assertFalse($aktif->sudahKadaluarsa());
        $this->assertTrue($lewat->sudahKadaluarsa());

        $this->actingAs($this->waka)->get('/dispensasi?tab=disetujui')
            ->assertOk()->assertSee('Lomba')->assertDontSee('Sakit minggu lalu');

        $this->actingAs($this->waka)->get('/dispensasi?tab=kadaluarsa')
            ->assertOk()->assertSee('Sakit minggu lalu')->assertDontSee('Lomba');
    }

    public function test_dispensasi_multihari_baru_kadaluarsa_setelah_tanggal_selesai_lewat(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today()->subDay(), 'tanggal_selesai' => today()->addDay(),
            'alasan' => 'Sakit 3 hari', 'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ]);

        $this->assertFalse($d->sudahKadaluarsa());
    }

    /**
     * Dispensasi PENDING (Waka belum mutusin) yang tanggal MULAI-nya udah kejalan
     * (hari ini atau sebelumnya) harus otomatis kebatal -- beda dari sudahKadaluarsa()
     * yang khusus buat yang udah disetujui.
     */
    public function test_dispensasi_pending_yang_tanggalnya_udah_lewat_otomatis_batal(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today()->subDay(), 'alasan' => 'Lomba', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();
        $d->refresh(); // status_waka default 'pending' dari DB, belum kebawa ke object in-memory abis create()

        $this->assertTrue($d->sudahLewatBatasKeputusan());
        $this->assertTrue($d->batalkanKalauKadaluarsa());

        $d->refresh();
        $this->assertSame('rejected', $d->status_waka);
        $this->assertSame('rejected', $d->status_akhir);
        $this->assertStringContainsString('Otomatis dibatalkan', $d->catatan_waka);
    }

    /**
     * Kasus paling umum: piket ajukan dispensasi buat HARI INI JUGA (siswa lagi
     * di depan gerbang mau izin langsung) -- ini TIDAK boleh dianggap "lewat
     * batas" walau tanggalnya sama persis kayak hari ini, karena mustahil
     * diputuskan sebelum harinya sendiri dimulai.
     */
    public function test_dispensasi_pending_untuk_hari_ini_belum_dianggap_lewat_batas(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'Izin mendadak', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();

        $this->assertFalse($d->sudahLewatBatasKeputusan());

        $this->actingAs($this->waka)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved'])
            ->assertRedirect()->assertSessionMissing('error');
        $this->assertSame('approved', $d->fresh()->status_akhir);
    }

    public function test_dispensasi_pending_untuk_besok_belum_dianggap_lewat_batas(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today()->addDay(), 'alasan' => 'Lomba besok', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();

        $this->assertFalse($d->sudahLewatBatasKeputusan());
        $this->assertFalse($d->batalkanKalauKadaluarsa());
        $this->assertSame('pending', $d->fresh()->status_waka);
    }

    public function test_buka_halaman_riwayat_dispensasi_otomatis_menyapu_yang_kadaluarsa(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today()->subDays(2), 'alasan' => 'Lomba kemarin lusa', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();

        $this->actingAs($this->waka)->get('/dispensasi')->assertOk();

        $this->assertSame('rejected', $d->fresh()->status_akhir);
    }

    public function test_waka_tidak_bisa_approve_dispensasi_yang_udah_kadaluarsa(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today()->subDay(), 'alasan' => 'Telat diproses', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();

        $this->actingAs($this->waka)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved'])
            ->assertRedirect()->assertSessionHas('error');

        $this->assertSame('rejected', $d->fresh()->status_akhir);
    }

    public function test_detail_dispensasi_kadaluarsa_tidak_nampilin_tombol_setuju(): void
    {
        $d = Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today()->subDay(), 'alasan' => 'Telat diproses', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();

        $this->actingAs($this->waka)->get("/dispensasi/{$d->id}/fragment")
            ->assertOk()->assertDontSee('Setujui');
    }

    /** Endpoint polling buat banner "ada data baru" (initAutoRefresh() di app.js) -- lihat App\Support\Versi. */
    public function test_endpoint_versi_berubah_setelah_ada_dispensasi_baru(): void
    {
        $versiAwal = $this->actingAs($this->waka)->get('/dispensasi/versi')->assertOk()->json('versi');

        Dispensasi::create([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'Baru', 'status_piket' => 'approved',
        ]);

        $versiBaru = $this->get('/dispensasi/versi')->assertOk()->json('versi');
        $this->assertNotSame($versiAwal, $versiBaru);
    }
}
