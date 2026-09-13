<?php

namespace Tests\Feature\Guru;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** K4: halaman Detail Siswa (riwayat kehadiran) diakses dari klik nama siswa di presensi. */
class SiswaDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_bisa_lihat_detail_siswa_yang_diajarnya(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L', 'no_absen' => 1]);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
        ]);
        Absensi::create(['jurnal_id' => $jurnal->id, 'siswa_id' => $siswa->id, 'status' => 'sakit']);

        $this->actingAs($user)->get("/guru/siswa/{$siswa->id}")
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('Matematika');
    }

    public function test_guru_tidak_bisa_lihat_siswa_yang_bukan_diajarnya(): void
    {
        $user = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru']);

        $kelasLain = Kelas::create(['nama' => 'XI TKJ 1', 'tingkat' => 'XI', 'jurusan' => 'TKJ']);
        $siswaLain = Siswa::create(['kelas_id' => $kelasLain->id, 'nis' => '002', 'nama' => 'Siti', 'jenis_kelamin' => 'P', 'no_absen' => 1]);

        $this->actingAs($user)->get("/guru/siswa/{$siswaLain->id}")->assertForbidden();
    }
}
