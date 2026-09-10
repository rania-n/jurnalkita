<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_role_dashboard(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_pending_account_cannot_login(): void
    {
        $user = User::factory()->pending()->create();

        $this->from('/login')->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_role_middleware_blocks_other_roles(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->get('/admin/kelas')->assertForbidden();
        $this->actingAs($guru)->get('/guru')->assertOk();
    }

    public function test_guru_registration_creates_pending_user_and_guru_record(): void
    {
        $this->post('/daftar/guru', [
            'nama' => 'Pak Test',
            'email' => 'paktest@example.com',
            'telepon' => '0812',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'paktest@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('guru', $user->role);
        $this->assertSame('pending', $user->status);
        $this->assertDatabaseHas('gurus', ['user_id' => $user->id, 'nama' => 'Pak Test']);
    }

    public function test_pengurus_kelas_registration_creates_siswa_pengurus(): void
    {
        $guru = Guru::create(['nama' => 'Wali']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $guru->id]);

        $this->post('/daftar/pengurus-kelas', [
            'kelas_id' => $kelas->id,
            'nama' => 'Ketua Kelas',
            'nis' => '2026999',
            'email' => 'ketua@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'ketua@example.com')->first();
        $this->assertSame('siswa', $user->role);
        $this->assertDatabaseHas('siswas', [
            'user_id' => $user->id, 'kelas_id' => $kelas->id, 'jabatan' => 'pengurus',
        ]);
    }
}
