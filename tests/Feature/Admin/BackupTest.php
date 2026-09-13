<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * download() beneran shell-out ke mysqldump -- di luar cakupan test suite ini
 * yang sengaja pakai SQLite in-memory (lihat phpunit.xml) buat cepat & terisolasi.
 * Jadi di sini cuma dites: halaman render + akses dibatasi admin. Alur unduh
 * sungguhan (mysqldump beneran jalan, file .sql valid) diverifikasi manual di
 * browser terhadap MySQL dev, bukan lewat automated test.
 */
class BackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_backup_render_untuk_admin(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->get('/admin/backup')
            ->assertOk()->assertSee('Unduh Backup');
    }

    public function test_bukan_admin_tidak_bisa_akses_halaman_backup(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->get('/admin/backup')->assertRedirect(route('guru.dashboard'));
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/backup')->assertRedirect(route('login'));
    }
}
