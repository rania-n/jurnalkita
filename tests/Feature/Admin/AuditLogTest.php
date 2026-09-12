<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    public function test_halaman_render_dan_menampilkan_log(): void
    {
        $user = $this->admin();
        AuditLog::catat('Tambah Mapel', 'Mapel: Matematika');

        $this->actingAs($user)->get('/admin/audit-log')
            ->assertOk()->assertSee('Tambah Mapel')->assertSee('Mapel: Matematika');
    }

    public function test_kosong_menampilkan_pesan_kosong(): void
    {
        $this->actingAs($this->admin())->get('/admin/audit-log')
            ->assertOk()->assertSee('Tidak ada log yang cocok');
    }

    public function test_pencarian_berdasarkan_aksi_atau_deskripsi(): void
    {
        AuditLog::catat('Tambah Kelas', 'Kelas: X RPL 1');
        AuditLog::catat('Hapus Siswa', 'Siswa: Budi');

        $this->actingAs($this->admin())->get('/admin/audit-log?cari=Kelas')
            ->assertOk()->assertSee('Tambah Kelas')->assertDontSee('Hapus Siswa');
    }

    public function test_filter_berdasarkan_user(): void
    {
        $pembuat = User::factory()->role('guru')->create(['name' => 'Bu Guru']);
        $this->actingAs($pembuat);
        AuditLog::catat('Tambah Jurnal', 'Jurnal contoh');

        $lain = User::factory()->role('guru')->create(['name' => 'Guru Lain']);
        $this->actingAs($lain);
        AuditLog::catat('Ubah Jurnal', 'Jurnal lain');

        // 'Guru Lain' tetap muncul di dropdown filter user, jadi cek isi barisnya
        // lewat deskripsi log yang unik, bukan nama filternya.
        $this->actingAs($this->admin())->get("/admin/audit-log?user={$pembuat->id}")
            ->assertOk()->assertSee('Jurnal contoh')->assertDontSee('Jurnal lain');
    }

    public function test_bukan_admin_tidak_bisa_akses(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->get('/admin/audit-log')->assertRedirect(route('guru.dashboard'));
    }

    public function test_aksi_penting_beneran_tercatat_lewat_alur_asli(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/mapel', [
            'kode' => 'FIS', 'nama' => 'Fisika',
        ]);

        $this->assertDatabaseHas('audit_logs', ['aksi' => 'Tambah Mapel', 'user_id' => $admin->id]);

        $this->actingAs($admin)->get('/admin/audit-log')
            ->assertOk()->assertSee('Tambah Mapel')->assertSee('Fisika');
    }
}
