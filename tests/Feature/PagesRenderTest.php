<?php

namespace Tests\Feature;

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

class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_guru_render(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Bu Guru']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create(['kelas_id' => $kelas->id, 'nis' => '1', 'nama' => 'A', 'jenis_kelamin' => 'L', 'no_absen' => 1]);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id, 'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2]);

        foreach (['/guru', '/guru/jurnal', '/guru/jurnal/tambah', '/guru/piket', '/dispensasi', '/dispensasi-ajukan/baru', '/profil'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_halaman_sekretaris_render(): void
    {
        $user = User::factory()->role('siswa')->create();
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create(['user_id' => $user->id, 'kelas_id' => $kelas->id, 'nis' => '1', 'nama' => 'Ketua', 'jenis_kelamin' => 'L', 'jabatan' => 'pengurus']);

        foreach (['/sekretaris', '/sekretaris/jurnal', '/sekretaris/jurnal/pengganti', '/profil'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_halaman_waka_render(): void
    {
        $user = User::factory()->role('waka')->create();

        foreach (['/waka', '/dispensasi', '/profil'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_jurnal_flow_render_dengan_choice(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Guru']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create(['kelas_id' => $kelas->id, 'nis' => '1', 'nama' => 'A', 'jenis_kelamin' => 'L', 'no_absen' => 1]);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id, 'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'x',
            'status_verifikasi' => 'revisi', 'catatan_verifikasi' => 'Kurang lengkap',
        ]);
        $jurnal->absensis()->create(['siswa_id' => Siswa::first()->id, 'status' => 'hadir']);

        $this->actingAs($user)->get("/guru/jurnal/{$jurnal->id}")->assertOk()->assertSee('Kurang lengkap');
        $this->actingAs($user)->get("/guru/jurnal/{$jurnal->id}/presensi")->assertOk();
    }

    public function test_guru_bisa_revisi_jurnal_lalu_kembali_pending(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Guru']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create(['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id, 'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'lama',
            'status_verifikasi' => 'revisi', 'catatan_verifikasi' => 'Perbaiki materi', 'verifikator_id' => null,
        ]);

        $this->actingAs($user)->post("/guru/jurnal/{$jurnal->id}", [
            'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'materi baru yang lengkap',
        ])->assertRedirect();

        $jurnal->refresh();
        $this->assertSame('pending', $jurnal->status_verifikasi);
        $this->assertNull($jurnal->catatan_verifikasi);
        $this->assertSame('materi baru yang lengkap', $jurnal->materi);
    }
}
