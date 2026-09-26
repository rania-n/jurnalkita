<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
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
            'no_hp' => '081234567890', // wajib khusus role guru
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
            ->assertSessionHasErrors('password', null, 'buatAkun');

        $this->assertDatabaseCount('users', 1); // hanya admin
    }

    public function test_password_yang_diketik_admin_dipakai(): void
    {
        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload());

        $user = User::where('email', 'baru@sekolah.test')->first();
        $this->assertTrue(\Hash::check('rahasia-kuat-123', $user->password));
    }

    /** Peran satpam sudah dihapus dari sistem -- role ini tidak boleh bisa dibuat lagi. */
    public function test_buat_akun_dengan_role_satpam_ditolak(): void
    {
        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload([
            'role' => 'satpam', 'sumber' => 'baru', 'nama' => 'Pak Satpam',
            'email' => 'satpam@sekolah.test', 'no_hp' => '081234567890',
        ]))->assertSessionHasErrorsIn('buatAkun', ['role']);

        $this->assertDatabaseMissing('users', ['email' => 'satpam@sekolah.test']);
    }

    public function test_buat_akun_waka_dengan_nip(): void
    {
        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload([
            'role' => 'waka', 'sumber' => 'baru', 'nama' => 'Bu Waka',
            'email' => 'waka-nip@sekolah.test', 'nip' => '198501012020',
        ]))->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'waka-nip@sekolah.test', 'role' => 'waka', 'nip' => '198501012020']);
    }

    public function test_buat_akun_guru_baru_bisa_isi_no_hp(): void
    {
        // no_hp cuma disimpan di users.no_hp (satu sumber) -- TIDAK ikut ditulis
        // ke gurus.no_hp, biar nggak ada 2 tempat nomor WA yang bisa beda.
        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload([
            'role' => 'guru', 'sumber' => 'baru', 'nama' => 'Pak Guru',
            'email' => 'guru-hp@sekolah.test', 'no_hp' => '081211112222',
        ]))->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'guru-hp@sekolah.test', 'no_hp' => '081211112222']);
        $this->assertDatabaseHas('gurus', ['nama' => 'Pak Guru', 'no_hp' => null]);
    }

    public function test_buat_akun_pengurus_kelas_bisa_isi_no_hp(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->actingAs($this->admin())->post('/admin/akun', $this->akunPayload([
            'role' => 'siswa', 'sumber' => 'baru', 'nama' => 'Ketua Kelas',
            'email' => 'ketua-hp@sekolah.test', 'kelas_id' => $kelas->id, 'nis' => '001', 'no_hp' => '081233334444',
        ]))->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'ketua-hp@sekolah.test', 'no_hp' => '081233334444']);
        $this->assertDatabaseHas('siswas', ['nama' => 'Ketua Kelas', 'no_hp' => null]);
    }

    public function test_halaman_manajemen_akun_tidak_menampilkan_akun_pending(): void
    {
        $ditolak = User::factory()->role('guru')->create(['name' => 'Ditolak Test', 'status' => 'rejected']);
        $pending = User::factory()->role('guru')->pending()->create(['name' => 'Pending Test']);

        $response = $this->actingAs($this->admin())->get('/admin/akun');

        $response->assertOk()->assertSee('Ditolak Test')->assertDontSee('Pending Test');
    }

    public function test_halaman_persetujuan_akun_terpisah_cuma_isi_pending(): void
    {
        $ditolak = User::factory()->role('guru')->create(['name' => 'Ditolak Test', 'status' => 'rejected']);
        $pending = User::factory()->role('guru')->pending()->create(['name' => 'Pending Test']);

        $response = $this->actingAs($this->admin())->get('/admin/akun-persetujuan');

        $response->assertOk()->assertSee('Pending Test')->assertDontSee('Ditolak Test');
    }

    public function test_setujui_tolak_akun_tetap_jalan_dari_halaman_persetujuan(): void
    {
        $pending = User::factory()->role('guru')->pending()->create();

        $this->actingAs($this->admin())->post("/admin/akun/{$pending->id}/setujui")->assertRedirect();
        $this->assertSame('approved', $pending->fresh()->status);
    }

    public function test_admin_bisa_ubah_akun_yang_sudah_ada_termasuk_password(): void
    {
        $guru = User::factory()->role('guru')->create(['email' => 'lama@s.test']);

        $this->actingAs($this->admin())->post('/admin/akun-ubah', [
            'id' => $guru->id, 'nama' => 'Nama Baru', 'email' => 'baru@s.test',
            'no_hp' => '081200001111', 'password' => 'sandi-baru-123', 'password_confirmation' => 'sandi-baru-123',
        ])->assertRedirect();

        $guru->refresh();
        $this->assertSame('Nama Baru', $guru->name);
        $this->assertSame('baru@s.test', $guru->email);
        $this->assertSame('081200001111', $guru->no_hp);
        $this->assertTrue(\Hash::check('sandi-baru-123', $guru->password));
    }

    public function test_ubah_akun_tanpa_isi_password_tidak_mengubah_password_lama(): void
    {
        $guru = User::factory()->role('guru')->create();
        $passwordLama = $guru->password;

        $this->actingAs($this->admin())->post('/admin/akun-ubah', [
            'id' => $guru->id, 'nama' => $guru->name, 'email' => $guru->email,
        ])->assertRedirect();

        $this->assertSame($passwordLama, $guru->fresh()->password);
    }

    public function test_buat_akun_siswa_baru_wajib_kelas_dan_nis(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/akun', $this->akunPayload(['role' => 'siswa', 'email' => 'x@s.test']))
            ->assertSessionHasErrors(['kelas_id', 'nis'], null, 'buatAkun');
    }

    public function test_kelas_yang_sudah_punya_pengurus_tidak_bisa_dibuatkan_akun_pengurus_lagi(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Ketua Lama',
            'jenis_kelamin' => 'L', 'jabatan' => 'pengurus',
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/akun', $this->akunPayload([
                'role' => 'siswa', 'email' => 'ketua-baru@s.test', 'kelas_id' => $kelas->id, 'nis' => '002',
            ]))
            ->assertSessionHasErrors('kelas_id', null, 'buatAkun');
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
            ->assertSessionHasErrors('email', null, 'buatAkun');
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
