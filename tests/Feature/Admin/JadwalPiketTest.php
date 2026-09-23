<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JadwalPiketTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Guru $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->role('admin')->create();
        $this->guru = Guru::create(['nama' => 'Pak Budi']);
    }

    /** Payload buat TAMBAH (generate banyak baris) -- 2026-09-21 itu Senin. */
    private function payloadTambah(array $override = []): array
    {
        return array_merge([
            'guru_ids' => [$this->guru->id], 'tanggal' => '2026-09-21',
            'ulang_setiap_minggu' => 2, 'jumlah_kali' => 1,
            'mulai' => '07:00', 'selesai' => '11:00',
        ], $override);
    }

    /** Payload buat UBAH (1 baris doang, nggak ada opsi ulang). */
    private function payloadUbah(int $id, array $override = []): array
    {
        return array_merge([
            'id' => $id, 'guru_id' => $this->guru->id, 'tanggal' => '2026-09-21',
            'mulai' => '07:00', 'selesai' => '11:00',
        ], $override);
    }

    public function test_admin_bisa_tambah_jadwal_piket(): void
    {
        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah())
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_pikets', ['guru_id' => $this->guru->id, 'hari' => 'senin', 'tanggal' => '2026-09-21']);
    }

    /** jumlah_kali > 1 -- generate beberapa baris sekaligus, berjarak "ulang_setiap_minggu" dari tanggal awal. */
    public function test_tambah_jadwal_piket_generate_banyak_baris_sekaligus(): void
    {
        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah(['jumlah_kali' => 3, 'ulang_setiap_minggu' => 2]))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame(3, JadwalPiket::count());
        $this->assertDatabaseHas('jadwal_pikets', ['tanggal' => '2026-09-21']);
        $this->assertDatabaseHas('jadwal_pikets', ['tanggal' => '2026-10-05']);
        $this->assertDatabaseHas('jadwal_pikets', ['tanggal' => '2026-10-19']);
    }

    public function test_tanggal_akhir_pekan_ditolak(): void
    {
        // 2026-09-19 itu Sabtu.
        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah(['tanggal' => '2026-09-19']))
            ->assertSessionHas('error');

        $this->assertSame(0, JadwalPiket::count());
    }

    /** sesi_piket cuma shortcut JS di form -- bukan field yang divalidasi/disimpan, jadi kalau ikut kekirim harus tetap sukses & nggak nyimpen apa-apa aneh. */
    public function test_field_sesi_piket_diabaikan_server_tidak_bikin_error(): void
    {
        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah(['sesi_piket' => 'pagi']))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_pikets', ['guru_id' => $this->guru->id]);
    }

    public function test_jadwal_piket_dobel_persis_sama_ditolak(): void
    {
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'tanggal' => '2026-09-21', 'mulai' => '07:00', 'selesai' => '11:00']);

        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah())
            ->assertSessionHas('error');

        $this->assertSame(1, JadwalPiket::count());
    }

    /** Kalau SALAH SATU tanggal dari batch yang mau digenerate bentrok, semuanya ditolak (bukan sebagian nyimpen sebagian nggak). */
    public function test_batch_ditolak_semua_kalau_ada_satu_tanggal_yang_bentrok(): void
    {
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'tanggal' => '2026-10-05', 'mulai' => '07:00', 'selesai' => '11:00']);

        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah(['jumlah_kali' => 3, 'ulang_setiap_minggu' => 2]))
            ->assertSessionHas('error');

        $this->assertSame(1, JadwalPiket::count());
    }

    /** Guru boleh piket 2 sesi di hari yang sama (pagi & siang) -- jamnya beda, bukan dobel. */
    public function test_guru_boleh_piket_dua_sesi_beda_jam_hari_yang_sama(): void
    {
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'tanggal' => '2026-09-21', 'mulai' => '07:00', 'selesai' => '11:00']);

        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah(['mulai' => '11:00', 'selesai' => '15:00']))
            ->assertSessionMissing('error');

        $this->assertSame(2, JadwalPiket::count());
    }

    public function test_ubah_jadwal_piket_tidak_bentrok_sama_dirinya_sendiri(): void
    {
        $piket = JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'tanggal' => '2026-09-21', 'mulai' => '07:00', 'selesai' => '11:00', 'keterangan' => 'Lama']);

        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadUbah($piket->id, ['keterangan' => 'Baru']))
            ->assertSessionMissing('error');

        $this->assertSame('Baru', $piket->fresh()->keterangan);
    }

    /** Ubah 1 baris nggak boleh diam-diam nggandain baris baru walau field ulang_setiap_minggu/jumlah_kali ikut kekirim (mis. sisa dari form yang salah dicopy). */
    public function test_ubah_jadwal_piket_mengabaikan_field_ulang_walau_ikut_kekirim(): void
    {
        $piket = JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'tanggal' => '2026-09-21', 'mulai' => '07:00', 'selesai' => '11:00']);

        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadUbah($piket->id, [
            'ulang_setiap_minggu' => 2, 'jumlah_kali' => 5,
        ]))->assertSessionMissing('error');

        $this->assertSame(1, JadwalPiket::count());
    }

    public function test_guru_lain_boleh_punya_jadwal_piket_sama_persis(): void
    {
        $guruLain = Guru::create(['nama' => 'Bu Sinta']);
        JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'senin', 'tanggal' => '2026-09-21', 'mulai' => '07:00', 'selesai' => '11:00']);

        $this->actingAs($this->admin)->post('/admin/jadwal-piket', array_merge($this->payloadTambah(), ['guru_ids' => [$guruLain->id]]))
            ->assertSessionMissing('error');

        $this->assertSame(2, JadwalPiket::count());
    }

    /** Fitur baru: bisa centang lebih dari 1 guru sekaligus, masing-masing dapet baris sendiri buat semua tanggal target. */
    public function test_tambah_jadwal_piket_beberapa_guru_sekaligus(): void
    {
        $guruLain = Guru::create(['nama' => 'Bu Sinta']);

        $this->actingAs($this->admin)->post('/admin/jadwal-piket', $this->payloadTambah([
            'guru_ids' => [$this->guru->id, $guruLain->id], 'jumlah_kali' => 2,
        ]))->assertRedirect()->assertSessionHas('success');

        $this->assertSame(4, JadwalPiket::count());
        $this->assertDatabaseHas('jadwal_pikets', ['guru_id' => $this->guru->id, 'tanggal' => '2026-09-21']);
        $this->assertDatabaseHas('jadwal_pikets', ['guru_id' => $guruLain->id, 'tanggal' => '2026-09-21']);
    }

    /*
    |--------------------------------------------------------------------
    | berlakuPada() -- piket per tanggal spesifik (ulang tiap 2 minggu,
    | BUKAN tiap minggu kayak dulu) vs baris lama (tanggal kosong, masih
    | dianggap berulang tiap minggu di hari yang sama).
    |--------------------------------------------------------------------
    */

    public function test_baris_tanggal_spesifik_cuma_berlaku_pas_tanggal_itu_persis(): void
    {
        $piket = JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'selasa', 'tanggal' => '2026-09-22']);

        $this->assertTrue($piket->berlakuPada(Carbon::parse('2026-09-22')));
        // 2 minggu berikutnya (tanggal beda) -- TIDAK otomatis berlaku lagi,
        // beda dari mode lama yang berulang tiap minggu.
        $this->assertFalse($piket->berlakuPada(Carbon::parse('2026-09-29')));
        $this->assertFalse($piket->berlakuPada(Carbon::parse('2026-10-06')));
    }

    public function test_baris_lama_tanpa_tanggal_tetap_berulang_tiap_minggu(): void
    {
        $piket = JadwalPiket::create(['guru_id' => $this->guru->id, 'hari' => 'selasa']);

        $this->assertTrue($piket->berlakuPada(Carbon::parse('2026-09-22'))); // Selasa
        $this->assertTrue($piket->berlakuPada(Carbon::parse('2026-09-29'))); // Selasa minggu depan
        $this->assertFalse($piket->berlakuPada(Carbon::parse('2026-09-23'))); // Rabu
    }

    public function test_piket_hari_ini_pakai_tanggal_spesifik_bukan_berulang_mingguan(): void
    {
        $this->travelTo(Carbon::parse('2026-09-22 08:00')); // Selasa

        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Pak Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'selasa', 'tanggal' => '2026-09-22']);

        $this->assertTrue($user->piketHariIni());

        $this->travelTo(Carbon::parse('2026-09-29 08:00')); // Selasa 2 minggu berikutnya
        $this->assertFalse($user->piketHariIni());
    }
}
