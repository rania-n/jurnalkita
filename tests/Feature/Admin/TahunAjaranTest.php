<?php

namespace Tests\Feature\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TahunAjaranTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    private function buatKelas(string $tingkat, ?TahunAjaran $tahunAjaran, int $jumlahSiswaAktif = 2): Kelas
    {
        $kelas = Kelas::create([
            'nama' => "{$tingkat} RPL 1", 'tingkat' => $tingkat, 'jurusan' => 'RPL', 'nomor' => 1,
            'tahun_ajaran_id' => $tahunAjaran?->id,
        ]);

        for ($i = 1; $i <= $jumlahSiswaAktif; $i++) {
            Siswa::create([
                'kelas_id' => $kelas->id, 'nis' => "{$tingkat}{$i}", 'nama' => "Siswa {$tingkat} {$i}",
                'jenis_kelamin' => 'L',
            ]);
        }

        return $kelas;
    }

    public function test_kelas_x_naik_jadi_xi_siswa_ikut_pindah(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $kelasX = $this->buatKelas('X', $tahunLama, 3);

        $this->actingAs($this->admin())
            ->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2027/2028'])
            ->assertRedirect();

        $kelasXiBaru = Kelas::where('tingkat', 'XI')->where('nama', 'XI RPL 1')
            ->where('tahun_ajaran_id', '!=', $tahunLama->id)->first();

        $this->assertNotNull($kelasXiBaru, 'kelas XI baru harus terbuat');
        $this->assertSame(3, Siswa::where('kelas_id', $kelasXiBaru->id)->count());
        $this->assertSame(0, Siswa::where('kelas_id', $kelasX->id)->count(), 'siswa harus pindah, bukan digandakan');

        // Kelas lama tidak berubah — tetap arsip tingkat X.
        $this->assertSame('X', $kelasX->fresh()->tingkat);
    }

    public function test_kelas_xi_naik_jadi_xii(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $this->buatKelas('XI', $tahunLama, 2);

        $this->actingAs($this->admin())->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2027/2028']);

        $this->assertDatabaseHas('kelas', ['tingkat' => 'XII', 'nama' => 'XII RPL 1']);
    }

    public function test_kelas_xii_siswa_ditandai_lulus_tanpa_kelas_baru(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $kelasXii = $this->buatKelas('XII', $tahunLama, 4);

        $this->actingAs($this->admin())->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2027/2028']);

        $this->assertDatabaseMissing('kelas', ['tingkat' => 'XIII']);
        $this->assertSame(4, Siswa::where('kelas_id', $kelasXii->id)->where('status', 'lulus')->count());
        $this->assertSame(0, Siswa::where('kelas_id', $kelasXii->id)->where('status', 'aktif')->count());
    }

    public function test_siswa_yang_sudah_pindah_tidak_ikut_naik(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $kelasX = $this->buatKelas('X', $tahunLama, 0);
        $pindah = Siswa::create([
            'kelas_id' => $kelasX->id, 'nis' => 'P1', 'nama' => 'Siswa Pindah',
            'jenis_kelamin' => 'L', 'status' => 'pindah',
        ]);

        $this->actingAs($this->admin())->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2027/2028']);

        $this->assertSame($kelasX->id, $pindah->fresh()->kelas_id, 'siswa yang sudah pindah tidak boleh ikut dipindah kelas');
    }

    public function test_tahun_ajaran_baru_jadi_aktif_yang_lama_nonaktif(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $this->buatKelas('X', $tahunLama, 1);

        $this->actingAs($this->admin())->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2027/2028']);

        $this->assertFalse($tahunLama->fresh()->aktif);
        $this->assertSame('2027/2028', TahunAjaran::aktif()->nama);
    }

    public function test_kenaikan_kelas_tercatat_di_audit_log(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $this->buatKelas('X', $tahunLama, 1);

        $this->actingAs($this->admin())->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2027/2028']);

        $this->assertDatabaseHas('audit_logs', ['aksi' => 'Kenaikan Kelas']);
    }

    public function test_nama_tahun_ajaran_tidak_boleh_dobel(): void
    {
        TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);

        $this->actingAs($this->admin())
            ->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2026/2027'])
            ->assertSessionHasErrors('nama');
    }

    public function test_bukan_admin_tidak_bisa_naikkan_kelas(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)
            ->post('/admin/tahun-ajaran/naik-kelas', ['nama' => '2027/2028'])
            ->assertRedirect(route('guru.dashboard'));
    }

    public function test_halaman_tahun_ajaran_menampilkan_ringkasan(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $this->buatKelas('X', $tahunLama, 2);

        $this->actingAs($this->admin())->get('/admin/tahun-ajaran')
            ->assertOk()->assertSee('2026/2027');
    }
}
