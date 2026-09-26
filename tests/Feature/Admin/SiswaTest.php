<?php

namespace Tests\Feature\Admin;

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

    public function test_data_siswa_tidak_punya_field_no_hp_itu_cuma_lewat_manajemen_akun(): void
    {
        // no_hp sengaja TIDAK bisa diisi/diubah lewat Data Siswa -- satu-satunya
        // jalan masuk nomor WA adalah Manajemen Akun (lihat AkunTest), biar nggak
        // ada 2 tempat isi nomor yang bisa beda nilainya.
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->actingAs($this->admin())->post('/admin/siswa', [
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'jabatan' => 'anggota', 'no_hp' => '081211112222',
        ])->assertSessionHasNoErrors();

        $siswa = Siswa::where('nis', '001')->firstOrFail();
        $this->assertNull($siswa->no_hp, 'no_hp yang dikirim lewat Data Siswa harus diabaikan');
    }

    public function test_kelas_bisa_dibuat_diubah_dihapus_lewat_admin(): void
    {
        $admin = $this->admin();
        $guru = Guru::create(['nama' => 'Wali Test']);
        $guruLain = Guru::create(['nama' => 'Wali Lain']);

        $this->actingAs($admin)->post('/admin/kelas', [
            'tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 7, 'wali_id' => $guru->id, 'status' => 'aktif',
        ])->assertSessionHasNoErrors();
        $kelas = Kelas::where('nama', 'X RPL 7')->firstOrFail();

        $this->actingAs($admin)->post('/admin/kelas', [
            'id' => $kelas->id, 'tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 7, 'wali_id' => $guruLain->id, 'status' => 'aktif',
        ])->assertSessionHasNoErrors();
        $this->assertSame($guruLain->id, $kelas->fresh()->wali_id);

        $this->actingAs($admin)->delete("/admin/kelas/{$kelas->id}")->assertRedirect();
        $this->assertSoftDeleted('kelas', ['id' => $kelas->id]);
    }

    public function test_admin_bisa_lihat_detail_siswa_mana_saja(): void
    {
        $admin = $this->admin();
        $guru = Guru::create(['nama' => 'Pak Guru']);
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

        // Admin nggak dibatasi ngajar/nggak kayak guru -- bisa buka siswa kelas manapun.
        $this->actingAs($admin)->get("/admin/siswa/{$siswa->id}")
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('Matematika');
    }

    public function test_pkl_bisa_diubah_satuan_lewat_form_ubah_siswa(): void
    {
        $kelas = Kelas::create(['nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL']);
        $siswa = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
        $this->assertFalse($siswa->fresh()->isPkl());

        $this->actingAs($this->admin())->post('/admin/siswa', [
            'id' => $siswa->id, 'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'jabatan' => 'anggota', 'pkl' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($siswa->fresh()->isPkl());
    }

    public function test_pkl_bisa_diubah_massal_buat_sebagian_siswa_di_kelas(): void
    {
        // Skenario asli: kelas XI PKL-nya cuma sebagian siswa (beda dari
        // kelas XII yang semua siswanya PKL lewat status kelas).
        $kelas = Kelas::create(['nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL']);
        $pkl1 = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
        $pkl2 = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '002', 'nama' => 'Citra', 'jenis_kelamin' => 'P']);
        $tetapSekolah = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '003', 'nama' => 'Dedi', 'jenis_kelamin' => 'L']);

        $this->actingAs($this->admin())->patch('/admin/siswa/pkl-massal', [
            'siswa_ids' => [$pkl1->id, $pkl2->id], 'pkl' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($pkl1->fresh()->isPkl());
        $this->assertTrue($pkl2->fresh()->isPkl());
        $this->assertFalse($tetapSekolah->fresh()->isPkl());
    }

    public function test_pkl_massal_siswa_yang_sudah_lulus_atau_pindah_ditolak(): void
    {
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'tingkat' => 'XII', 'jurusan' => 'RPL']);
        $sudahLulus = Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L', 'status' => 'lulus']);

        $this->actingAs($this->admin())->patch('/admin/siswa/pkl-massal', [
            'siswa_ids' => [$sudahLulus->id], 'pkl' => '1',
        ])->assertSessionHas('error');

        $this->assertFalse($sudahLulus->fresh()->isPkl());
    }
}
