<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_semua_halaman_admin_render_200(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $paths = [
            '/admin', '/admin/akun', '/admin/akun?tab=pending',
            '/admin/guru', '/admin/kelas', '/admin/siswa', '/admin/mapel',
            '/admin/jadwal-pelajaran', '/admin/jadwal-piket', '/admin/jadwal-piket?hari=semua',
            '/admin/jam-pelajaran',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }
}
