<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\JadwalWaka;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JadwalWakaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tanpa_jadwal_sama_sekali_semua_waka_dianggap_bertugas(): void
    {
        $waka = User::factory()->role('waka')->create();

        $this->assertTrue($waka->wakaBertugasHariIni());
    }

    public function test_waka_bertugas_cuma_hari_yang_cocok_jadwalnya(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $wakaSenin = User::factory()->role('waka')->create();
        JadwalWaka::create(['user_id' => $wakaSenin->id, 'hari' => 'senin']);

        $wakaRabu = User::factory()->role('waka')->create();
        JadwalWaka::create(['user_id' => $wakaRabu->id, 'hari' => 'rabu']);

        $this->assertTrue($wakaSenin->wakaBertugasHariIni());
        $this->assertFalse($wakaRabu->wakaBertugasHariIni());
    }

    public function test_wa_link_persetujuan_diarahkan_ke_waka_yang_bertugas_hari_ini(): void
    {
        $this->travelTo(Carbon::parse('next monday 08:00'));

        $piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $wakaSenin = User::factory()->role('waka')->create(['name' => 'Waka Senin', 'no_hp' => '081111111111']);
        JadwalWaka::create(['user_id' => $wakaSenin->id, 'hari' => 'senin']);

        $wakaRabu = User::factory()->role('waka')->create(['name' => 'Waka Rabu', 'no_hp' => '082222222222']);
        JadwalWaka::create(['user_id' => $wakaRabu->id, 'hari' => 'rabu']);

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
        $d = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba', 'status_piket' => 'approved',
        ]);

        $response = $this->actingAs($piket)->get("/dispensasi/{$d->id}/fragment");
        $response->assertOk()->assertSee('6281111111111', false)->assertDontSee('6282222222222', false);
    }

    public function test_wa_link_fallback_ke_waka_manapun_kalau_bukan_hari_kerja(): void
    {
        $this->travelTo(Carbon::parse('next saturday 08:00'));

        $piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $waka = User::factory()->role('waka')->create(['no_hp' => '081111111111']);
        JadwalWaka::create(['user_id' => $waka->id, 'hari' => 'senin']);

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
        $d = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba', 'status_piket' => 'approved',
        ]);

        // Sabtu -> nggak ada yang "bertugas" (hari sekolah cuma senin-jumat), tapi
        // link WA tetap kebuat, jangan sampai fitur mati total di akhir pekan.
        $this->actingAs($piket)->get("/dispensasi/{$d->id}/fragment")->assertOk()->assertSee('6281111111111', false);
    }

    public function test_admin_bisa_atur_jadwal_waka(): void
    {
        $admin = User::factory()->role('admin')->create();
        $waka = User::factory()->role('waka')->create();

        $this->actingAs($admin)->post('/admin/jadwal-waka', [
            'user_id' => $waka->id, 'hari' => 'senin',
        ])->assertRedirect();

        $this->assertDatabaseHas('jadwal_wakas', ['user_id' => $waka->id, 'hari' => 'senin']);
    }

    public function test_admin_bisa_tambah_lebih_dari_satu_waka_di_hari_yang_sama(): void
    {
        $admin = User::factory()->role('admin')->create();
        $waka1 = User::factory()->role('waka')->create();
        $waka2 = User::factory()->role('waka')->create();

        $this->actingAs($admin)->post('/admin/jadwal-waka', [
            'user_id' => $waka1->id, 'hari' => 'senin',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->post('/admin/jadwal-waka', [
            'user_id' => $waka2->id, 'hari' => 'senin',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, JadwalWaka::where('hari', 'senin')->count());
    }

    public function test_admin_bisa_ubah_jadwal_waka_yang_ada(): void
    {
        $admin = User::factory()->role('admin')->create();
        $waka1 = User::factory()->role('waka')->create();
        $waka2 = User::factory()->role('waka')->create();

        $j1 = JadwalWaka::create(['user_id' => $waka1->id, 'hari' => 'senin']);
        $j2 = JadwalWaka::create(['user_id' => $waka2->id, 'hari' => 'senin']);

        // Ubah hari j1 menjadi selasa
        $this->actingAs($admin)->post('/admin/jadwal-waka', [
            'id' => $j1->id,
            'user_id' => $waka1->id,
            'hari' => 'selasa',
        ])->assertSessionHasNoErrors();

        $this->assertSame('selasa', $j1->fresh()->hari);

        // Simpan j2 tanpa ubah apa pun (tetap senin) harusnya tidak bentrok dengan dirinya sendiri
        $this->actingAs($admin)->post('/admin/jadwal-waka', [
            'id' => $j2->id,
            'user_id' => $waka2->id,
            'hari' => 'senin',
        ])->assertSessionHasNoErrors();
    }

    public function test_waka_sama_tidak_boleh_dijadwalkan_dua_kali_di_hari_yang_sama(): void
    {
        $admin = User::factory()->role('admin')->create();
        $waka = User::factory()->role('waka')->create(['name' => 'Pak Waka']);

        JadwalWaka::create(['user_id' => $waka->id, 'hari' => 'senin']);

        $this->actingAs($admin)->post('/admin/jadwal-waka', [
            'user_id' => $waka->id, 'hari' => 'senin',
        ])->assertSessionHas('error');

        $this->assertSame(1, JadwalWaka::where('user_id', $waka->id)->where('hari', 'senin')->count());
    }

    public function test_bukan_admin_tidak_bisa_atur_jadwal_waka(): void
    {
        $guru = User::factory()->role('guru')->create();
        $waka = User::factory()->role('waka')->create();

        $this->actingAs($guru)->post('/admin/jadwal-waka', [
            'user_id' => $waka->id, 'hari' => 'senin',
        ])->assertRedirect(route('guru.dashboard'));
    }
}
