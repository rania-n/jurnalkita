<?php

namespace Tests\Feature;

use App\Http\Controllers\SuratDispensasiController;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Support\QrDispensasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratDispensasiTest extends TestCase
{
    use RefreshDatabase;

    private User $piket;

    private User $waka;

    private Dispensasi $disetujui;

    private Dispensasi $menunggu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $this->piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $this->waka = User::factory()->role('waka')->create(['no_hp' => '081234567890']);

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);

        $this->disetujui = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $this->piket->id, 'piket_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba', 'no_hp' => '081211112222',
            'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved', 'waka_id' => $this->waka->id,
        ]);

        $this->menunggu = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $this->piket->id, 'piket_id' => $this->piket->id,
            'tanggal' => today(), 'alasan' => 'Sakit', 'status_piket' => 'approved',
        ]);
    }

    public function test_surat_bisa_dibuka_lewat_tautan_bertanda_tangan_tanpa_login(): void
    {
        $tautan = SuratDispensasiController::tautanSurat($this->disetujui);

        $this->get($tautan)->assertOk()->assertSee('Budi')->assertSee('Disetujui');
    }

    public function test_surat_tanpa_tanda_tangan_ditolak(): void
    {
        $this->get("/surat/dispensasi/{$this->disetujui->id}")->assertForbidden();
    }

    public function test_surat_yang_sudah_lewat_tanggalnya_tidak_tampilkan_qr(): void
    {
        $this->disetujui->update(['tanggal' => today()->subDays(5)]);
        $tautan = SuratDispensasiController::tautanSurat($this->disetujui);

        $this->get($tautan)->assertOk()->assertDontSee('qrserver.com', false)->assertSee('sudah lewat');
    }

    public function test_surat_dispensasi_beberapa_hari_tampilkan_rentang_tanggal(): void
    {
        $this->disetujui->update(['tanggal_selesai' => $this->disetujui->tanggal->copy()->addDays(2)]);
        $tautan = SuratDispensasiController::tautanSurat($this->disetujui);

        $this->get($tautan)->assertOk()->assertSee('qrserver.com', false)
            ->assertSee($this->disetujui->tanggal->translatedFormat('d M Y'))
            ->assertSee($this->disetujui->tanggal_selesai->translatedFormat('d M Y'));
    }

    public function test_surat_menampilkan_qr_hanya_kalau_disetujui(): void
    {
        $tautanDisetujui = SuratDispensasiController::tautanSurat($this->disetujui);
        $tautanMenunggu = SuratDispensasiController::tautanSurat($this->menunggu);

        $this->get($tautanDisetujui)->assertOk()->assertSee('qrserver.com', false);
        $this->get($tautanMenunggu)->assertOk()->assertDontSee('qrserver.com', false)
            ->assertSee('menunggu persetujuan');
    }

    public function test_piket_pengaju_bisa_lihat_surat_tanpa_tanda_tangan(): void
    {
        $this->actingAs($this->piket)->get("/surat/dispensasi/{$this->disetujui->id}")->assertOk();
    }

    public function test_guru_lain_tidak_bisa_lihat_surat_tanpa_tanda_tangan(): void
    {
        $lain = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $lain->id, 'nama' => 'Guru Lain']);

        $this->actingAs($lain)->get("/surat/dispensasi/{$this->disetujui->id}")->assertForbidden();
    }

    public function test_persetujuan_wa_bisa_dilakukan_tanpa_login(): void
    {
        $tautan = SuratDispensasiController::tautanPersetujuan($this->menunggu, $this->waka);

        $this->get($tautan)->assertOk()->assertSee('Setujui');

        $this->post($tautan, ['keputusan' => 'approved'])
            ->assertOk()->assertSee('Dispensasi Disetujui');

        $this->menunggu->refresh();
        $this->assertSame('approved', $this->menunggu->status_akhir);
        $this->assertSame($this->waka->id, $this->menunggu->waka_id);
    }

    public function test_persetujuan_wa_tanpa_tanda_tangan_ditolak(): void
    {
        $this->post("/dispensasi/{$this->menunggu->id}/persetujuan", ['keputusan' => 'approved'])
            ->assertForbidden();

        $this->assertSame('pending', $this->menunggu->fresh()->status_waka);
    }

    public function test_persetujuan_wa_tidak_bisa_dobel(): void
    {
        $tautan = SuratDispensasiController::tautanPersetujuan($this->menunggu, $this->waka);
        $this->post($tautan, ['keputusan' => 'approved']);

        $this->post($tautan, ['keputusan' => 'rejected'])->assertStatus(409);
        $this->assertSame('approved', $this->menunggu->fresh()->status_waka);
    }

    public function test_persetujuan_wa_untuk_dispensasi_kadaluarsa_otomatis_batal_dan_ditolak(): void
    {
        $lewat = Dispensasi::create([
            'siswa_id' => $this->menunggu->siswa_id, 'diajukan_oleh_id' => $this->piket->id, 'piket_id' => $this->piket->id,
            'tanggal' => today()->subDays(2), 'alasan' => 'Telat diproses', 'status_piket' => 'approved',
        ]);
        $tautan = SuratDispensasiController::tautanPersetujuan($lewat, $this->waka);

        // Halaman persetujuan tetap kebuka (link 48 jam masih valid), tapi
        // dispensasinya sendiri udah kesapu jadi rejected pas halaman ini diload.
        $this->get($tautan)->assertOk()->assertSee('kadaluarsa')->assertDontSee('Setujui');
        $this->assertSame('rejected', $lewat->fresh()->status_waka);

        $this->post($tautan, ['keputusan' => 'approved'])->assertStatus(409);
        $this->assertSame('rejected', $lewat->fresh()->status_waka);
    }

    public function test_qr_dispensasi_token_valid_1_jendela_ke_belakang_tapi_tidak_2(): void
    {
        $sekarang = intdiv(time(), 10);
        $tokenLama = QrDispensasi::token(99, $sekarang - 1);
        $tokenKedaluwarsa = QrDispensasi::token(99, $sekarang - 2);

        $this->assertTrue(QrDispensasi::valid(99, $tokenLama));
        $this->assertFalse(QrDispensasi::valid(99, $tokenKedaluwarsa));
    }
}
