<?php

namespace Tests\Feature\Guru;

use App\Models\Absensi;
use App\Models\CatatanTerlambat;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaliKelasTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_kelas_bisa_lihat_rekap_kelasnya(): void
    {
        $wali = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $wali->id, 'nama' => 'Wali Kelas']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $guru->id]);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'ruang' => 'R1', 'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $siswa->id, 'status' => 'sakit']);

        $this->assertTrue($wali->isWali());

        $this->actingAs($wali)->get('/guru/wali-kelas')->assertOk()
            ->assertSee('Budi')->assertSee('text-sakit">1</span>', false);
    }

    public function test_guru_biasa_bukan_wali_tidak_bisa_akses(): void
    {
        $guru = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guru->id, 'nama' => 'Guru Biasa']);

        $this->actingAs($guru)->get('/guru/wali-kelas')->assertForbidden();
    }

    public function test_wali_kelas_lain_tidak_bisa_lihat_rekap_kelas_orang(): void
    {
        $waliA = User::factory()->role('guru')->create();
        $guruA = Guru::create(['user_id' => $waliA->id, 'nama' => 'Wali A']);
        $kelasA = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $guruA->id]);

        $waliB = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $waliB->id, 'nama' => 'Wali B']);

        $this->actingAs($waliB)->get("/guru/wali-kelas/{$kelasA->id}")->assertForbidden();
    }

    public function test_wali_dua_kelas_lihat_daftar_pilihan_dulu(): void
    {
        $wali = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $wali->id, 'nama' => 'Wali Dua Kelas']);
        Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $guru->id]);
        Kelas::create(['nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL', 'wali_id' => $guru->id]);

        $this->actingAs($wali)->get('/guru/wali-kelas')->assertOk()
            ->assertSee('X RPL 1')->assertSee('XI RPL 1');
    }

    public function test_rekap_wali_kelas_ikut_hitung_catatan_terlambat(): void
    {
        $wali = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $wali->id, 'nama' => 'Wali Kelas']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $guru->id]);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);

        $pencatat = User::factory()->role('admin')->create();
        CatatanTerlambat::create([
            'siswa_id' => $siswa->id, 'tanggal' => today(), 'jam_datang' => '07:15', 'dicatat_oleh_id' => $pencatat->id,
        ]);

        $this->actingAs($wali)->get('/guru/wali-kelas')
            ->assertOk()->assertSee('text-navy">1</span>', false);
    }
}
