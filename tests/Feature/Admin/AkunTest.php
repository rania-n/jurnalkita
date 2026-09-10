<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkunTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    public function test_buat_akun_dari_data_guru_yang_ada(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);

        $res = $this->actingAs($this->admin())->post('/admin/akun', [
            'role' => 'guru',
            'sumber' => "guru:{$guru->id}",
            'nama' => 'Bu Sarah',
            'email' => 'sarah@sekolah.test',
        ])->assertRedirect();

        $res->assertSessionHas('success', fn ($m) => str_contains($m, 'Password sementara:'));

        $user = User::where('email', 'sarah@sekolah.test')->first();
        $this->assertSame('guru', $user->role);
        $this->assertSame('approved', $user->status);
        $this->assertSame($user->id, $guru->fresh()->user_id);
    }

    public function test_buat_akun_guru_baru_sekaligus_data(): void
    {
        $this->actingAs($this->admin())->post('/admin/akun', [
            'role' => 'guru', 'sumber' => 'baru',
            'nama' => 'Pak Baru', 'email' => 'baru@sekolah.test', 'nip' => '199001',
        ])->assertRedirect();

        $user = User::where('email', 'baru@sekolah.test')->first();
        $this->assertDatabaseHas('gurus', ['user_id' => $user->id, 'nama' => 'Pak Baru', 'nip' => '199001']);
    }

    public function test_buat_akun_siswa_baru_wajib_kelas_dan_nis(): void
    {
        $this->actingAs($this->admin())->post('/admin/akun', [
            'role' => 'siswa', 'sumber' => 'baru', 'nama' => 'X', 'email' => 'x@s.test',
        ])->assertSessionHasErrors(['kelas_id', 'nis']);
    }

    public function test_approve_dan_reject_pendaftaran(): void
    {
        $admin = $this->admin();
        $p1 = User::factory()->role('guru')->pending()->create();
        $p2 = User::factory()->role('guru')->pending()->create();

        $this->actingAs($admin)->post("/admin/akun/{$p1->id}/setujui")->assertRedirect();
        $this->assertSame('approved', $p1->fresh()->status);

        $this->actingAs($admin)->post("/admin/akun/{$p2->id}/tolak")->assertRedirect();
        $this->assertSame('rejected', $p2->fresh()->status);
    }

    public function test_reset_sandi_mengubah_password(): void
    {
        $guru = User::factory()->role('guru')->create();
        $lama = $guru->password;

        $this->actingAs($this->admin())->post("/admin/akun/{$guru->id}/reset-sandi")->assertRedirect();

        $this->assertNotSame($lama, $guru->fresh()->password);
    }

    public function test_email_duplikat_ditolak(): void
    {
        User::factory()->create(['email' => 'dobel@s.test']);

        $this->actingAs($this->admin())->post('/admin/akun', [
            'role' => 'waka', 'sumber' => 'baru', 'nama' => 'W', 'email' => 'dobel@s.test',
        ])->assertSessionHasErrors('email');
    }
}
