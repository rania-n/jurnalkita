<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    public function test_kelas_cuma_boleh_1_pengurus(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Ketua Lama',
            'jenis_kelamin' => 'L', 'jabatan' => 'pengurus',
        ]);
        $calon = Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '002', 'nama' => 'Calon Ketua Baru',
            'jenis_kelamin' => 'L', 'jabatan' => 'anggota',
        ]);

        $this->actingAs($this->admin())->post('/admin/siswa', [
            'id' => $calon->id, 'kelas_id' => $kelas->id, 'nis' => '002', 'nama' => 'Calon Ketua Baru',
            'jenis_kelamin' => 'L', 'jabatan' => 'pengurus',
        ])->assertSessionHasErrors('jabatan');

        $this->assertSame('anggota', $calon->fresh()->jabatan);
    }

    public function test_ganti_pengurus_kelas_boleh_kalau_yang_lama_sudah_diturunkan_dulu(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $lama = Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Ketua Lama',
            'jenis_kelamin' => 'L', 'jabatan' => 'anggota', // sudah diturunkan duluan
        ]);
        $baru = Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '002', 'nama' => 'Ketua Baru',
            'jenis_kelamin' => 'L', 'jabatan' => 'anggota',
        ]);

        $this->actingAs($this->admin())->post('/admin/siswa', [
            'id' => $baru->id, 'kelas_id' => $kelas->id, 'nis' => '002', 'nama' => 'Ketua Baru',
            'jenis_kelamin' => 'L', 'jabatan' => 'pengurus',
        ])->assertSessionHasNoErrors();

        $this->assertSame('pengurus', $baru->fresh()->jabatan);
    }

    public function test_kelas_lain_boleh_punya_pengurus_sendiri(): void
    {
        $kelasA = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $kelasB = Kelas::create(['nama' => 'X RPL 2', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create(['kelas_id' => $kelasA->id, 'nis' => '001', 'nama' => 'Ketua A', 'jenis_kelamin' => 'L', 'jabatan' => 'pengurus']);

        $this->actingAs($this->admin())->post('/admin/siswa', [
            'kelas_id' => $kelasB->id, 'nis' => '002', 'nama' => 'Ketua B',
            'jenis_kelamin' => 'L', 'jabatan' => 'pengurus',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('siswas', ['nama' => 'Ketua B', 'jabatan' => 'pengurus']);
    }

    public function test_admin_bisa_simpan_dan_ubah_no_hp_siswa_dari_data_siswa(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->actingAs($this->admin())->post('/admin/siswa', [
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'jabatan' => 'anggota', 'no_hp' => '081211112222',
        ])->assertSessionHasNoErrors();

        $siswa = Siswa::where('nis', '001')->firstOrFail();
        $this->assertSame('081211112222', $siswa->no_hp);

        $this->actingAs($this->admin())->post('/admin/siswa', [
            'id' => $siswa->id, 'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'jabatan' => 'anggota', 'no_hp' => '081299998888',
        ])->assertSessionHasNoErrors();

        $this->assertSame('081299998888', $siswa->fresh()->no_hp);
    }

    public function test_kelas_bisa_dibuat_diubah_dihapus_lewat_admin(): void
    {
        $admin = $this->admin();
        $guru = Guru::create(['nama' => 'Wali Test']);

        $this->actingAs($admin)->post('/admin/kelas', ['tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 7])
            ->assertSessionHasNoErrors();
        $kelas = Kelas::where('nama', 'X RPL 7')->firstOrFail();

        $this->actingAs($admin)->post('/admin/kelas', [
            'id' => $kelas->id, 'tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 7, 'wali_id' => $guru->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($guru->id, $kelas->fresh()->wali_id);

        $this->actingAs($admin)->delete("/admin/kelas/{$kelas->id}")->assertRedirect();
        $this->assertSoftDeleted('kelas', ['id' => $kelas->id]);
    }
}
