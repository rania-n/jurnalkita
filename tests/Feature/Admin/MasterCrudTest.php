<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    public function test_admin_can_create_update_delete_mapel(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/mapel', ['kode' => 'X1', 'nama' => 'Uji'])
            ->assertRedirect();
        $this->assertDatabaseHas('mapels', ['kode' => 'X1', 'nama' => 'Uji']);

        $mapel = Mapel::first();

        $this->actingAs($admin)->post('/admin/mapel', ['id' => $mapel->id, 'kode' => 'X1', 'nama' => 'Uji Baru'])
            ->assertRedirect();
        $this->assertSame('Uji Baru', $mapel->fresh()->nama);

        $this->actingAs($admin)->delete("/admin/mapel/{$mapel->id}")->assertRedirect();
        $this->assertSoftDeleted('mapels', ['id' => $mapel->id]);
    }

    public function test_kode_mapel_dibuat_otomatis_kalau_dikosongkan(): void
    {
        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Matematika'])->assertRedirect();
        $this->assertDatabaseHas('mapels', ['nama' => 'Matematika', 'kode' => 'MAT']);

        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Bahasa Indonesia'])->assertRedirect();
        $this->assertDatabaseHas('mapels', ['nama' => 'Bahasa Indonesia', 'kode' => 'BI']);
    }

    public function test_kode_mapel_otomatis_tetap_boleh_diketik_manual(): void
    {
        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Matematika', 'kode' => 'MTK'])->assertRedirect();
        $this->assertDatabaseHas('mapels', ['nama' => 'Matematika', 'kode' => 'MTK']);
    }

    public function test_kode_mapel_otomatis_tidak_bentrok_kalau_sudah_dipakai(): void
    {
        Mapel::create(['kode' => 'MAT', 'nama' => 'Matematika']);

        // "Matriks" -> 3 huruf depan "MAT" juga, sama kayak yang sudah ada -> harus digeser.
        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Matriks'])->assertRedirect();

        $this->assertDatabaseHas('mapels', ['nama' => 'Matriks', 'kode' => 'MAT2']);
    }

    public function test_validation_blocks_bad_input(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/kelas', ['tingkat' => 'ZZ', 'jurusan' => ''])
            ->assertSessionHasErrors(['tingkat', 'jurusan']);

        $this->assertDatabaseCount('kelas', 0);
    }

    public function test_kelas_name_is_generated(): void
    {
        $this->actingAs($this->admin())->post('/admin/kelas', [
            'tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('kelas', ['nama' => 'X RPL 2', 'jurusan' => 'RPL', 'nomor' => 2]);
    }

    public function test_detail_kelas_menampilkan_roster_dan_jadwal(): void
    {
        $wali = Guru::create(['nama' => 'Bu Wali']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $wali->id]);
        Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L', 'no_absen' => 1]);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $wali->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $response = $this->actingAs($this->admin())->get("/admin/kelas/{$kelas->id}");

        $response->assertOk()
            ->assertSee('Bu Wali')
            ->assertSee('Budi')
            ->assertSee('Matematika')
            ->assertSee('Senin');
    }

    public function test_tombol_detail_kelas_ada_di_daftar(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->actingAs($this->admin())->get('/admin/kelas')
            ->assertOk()->assertSee(route('master.kelas.show', $kelas), false);
    }

    public function test_guru_utama_and_tambahan_mapel(): void
    {
        $utama = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $tambahan = Mapel::create(['kode' => 'FIS', 'nama' => 'Fisika']);

        $this->actingAs($this->admin())->post('/admin/guru', [
            'nama' => 'Bu Test',
            'mapel_utama_id' => $utama->id,
            'mapel_tambahan' => [$tambahan->id, $utama->id],
        ])->assertRedirect();

        $guru = Guru::first();
        $this->assertSame($utama->id, $guru->mapel_utama_id);
        $this->assertEqualsCanonicalizing([$tambahan->id], $guru->mapels->pluck('id')->all());
        $this->assertDatabaseHas('audit_logs', ['aksi' => 'Tambah Guru']);
    }

    public function test_non_admin_cannot_write(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->post('/admin/mapel', ['kode' => 'Z', 'nama' => 'Z'])
            ->assertRedirect(route('guru.dashboard'));
        $this->assertDatabaseCount('mapels', 0);
    }

    public function test_jam_pelajaran_bulk_save_replaces_rows(): void
    {
        $this->actingAs($this->admin())->post('/admin/jam-pelajaran', [
            'kategori' => 'jumat',
            'mulai' => ['07:00', '07:40'],
            'selesai' => ['07:40', '08:20'],
        ])->assertRedirect();

        $this->assertDatabaseCount('jam_pelajarans', 2);
        $this->assertDatabaseHas('jam_pelajarans', ['kategori' => 'jumat', 'jam_ke' => 1]);
    }

    public function test_admin_bisa_tambah_kategori_jam_pelajaran_baru(): void
    {
        $this->actingAs($this->admin())->post('/admin/jam-pelajaran', [
            'kategori_baru' => 'Bulan Ramadhan',
            'mulai' => ['07:30'], 'selesai' => ['08:00'],
        ])->assertRedirect();

        $this->assertDatabaseHas('jam_pelajarans', ['kategori' => 'bulan_ramadhan', 'jam_ke' => 1]);

        $this->actingAs($this->admin())->get('/admin/jam-pelajaran?set=bulan_ramadhan')
            ->assertOk()->assertSee('Bulan Ramadhan');
    }

    public function test_admin_bisa_hapus_kategori_custom(): void
    {
        $this->actingAs($this->admin())->post('/admin/jam-pelajaran', [
            'kategori_baru' => 'Ujian',
            'mulai' => ['07:30'], 'selesai' => ['08:00'],
        ]);

        $this->actingAs($this->admin())->delete('/admin/jam-pelajaran/ujian')->assertRedirect();

        $this->assertSame(0, JamPelajaran::where('kategori', 'ujian')->count());
    }
}
