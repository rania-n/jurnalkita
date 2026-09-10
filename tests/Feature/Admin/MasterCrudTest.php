<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Mapel;
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
        $this->assertDatabaseHas('audit_logs', ['aksi' => 'guru.tambah']);
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
}
