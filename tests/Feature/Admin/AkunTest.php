<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AkunTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    private function akunPayload(array $override = []): array
    {
        return array_merge([
            'role' => 'guru',
            'sumber' => 'baru',
            'nama' => 'Pak Baru',
            'email' => 'baru@sekolah.test',
            'password' => 'rahasia-kuat-123',
            'password_confirmation' => 'rahasia-kuat-123',
        ], $override);
    }

    public function test_buat_akun_dari_data_guru_yang_ada(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);

        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload([
            'sumber' => "guru:{$guru->id}", 'nama' => 'Bu Sarah', 'email' => 'sarah@sekolah.test',
        ]))->assertRedirect();

        $user = User::where('email', 'sarah@sekolah.test')->first();
        $this->assertSame('guru', $user->role);
        $this->assertSame('approved', $user->status);
        $this->assertSame($user->id, $guru->fresh()->user_id);
    }

    public function test_password_wajib_dan_dikonfirmasi(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/akun', $this->akunPayload(['password' => 'x', 'password_confirmation' => 'y']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1); // hanya admin
    }

    public function test_password_yang_diketik_admin_dipakai(): void
    {
        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload());

        $user = User::where('email', 'baru@sekolah.test')->first();
        $this->assertTrue(\Hash::check('rahasia-kuat-123', $user->password));
    }

    public function test_buat_akun_siswa_baru_wajib_kelas_dan_nis(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/akun', $this->akunPayload(['role' => 'siswa', 'email' => 'x@s.test']))
            ->assertSessionHasErrors(['kelas_id', 'nis']);
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

    public function test_kirim_reset_mengirim_email(): void
    {
        Notification::fake();
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($this->admin())->post("/admin/akun/{$guru->id}/kirim-reset")->assertRedirect();

        Notification::assertSentTo($guru, ResetPassword::class);
    }

    public function test_email_duplikat_ditolak(): void
    {
        User::factory()->create(['email' => 'dobel@s.test']);

        $this->actingAs($this->admin())
            ->post('/admin/akun', $this->akunPayload(['role' => 'waka', 'email' => 'dobel@s.test']))
            ->assertSessionHasErrors('email');
    }

    public function test_hapus_akun_melepas_kaitan_ke_data_guru(): void
    {
        $guru = Guru::create(['nama' => 'Bu Sarah']);
        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload([
            'sumber' => "guru:{$guru->id}", 'nama' => 'Bu Sarah', 'email' => 'sarah@sekolah.test',
        ]));
        $user = User::where('name', 'Bu Sarah')->firstOrFail();

        $this->actingAs($this->admin())->delete("/admin/akun/{$user->id}")->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNull($guru->fresh()->user_id, 'guru harus lepas dari akun yang dihapus');
    }

    public function test_email_bisa_dipakai_lagi_setelah_akun_dihapus(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/akun', $this->akunPayload(['email' => 'pakai-ulang@sekolah.test']));
        $user = User::where('email', 'pakai-ulang@sekolah.test')->firstOrFail();

        $this->actingAs($admin)->delete("/admin/akun/{$user->id}");

        // Email lama harus bebas — tanpa "melepas" email, insert berikutnya kena
        // duplicate key karena users.email unik di level database.
        $this->actingAs($admin)->post('/admin/akun', $this->akunPayload([
            'nama' => 'Orang Baru', 'email' => 'pakai-ulang@sekolah.test',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'pakai-ulang@sekolah.test', 'name' => 'Orang Baru']);
    }

    public function test_admin_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete("/admin/akun/{$admin->id}")->assertForbidden();
        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }
}
