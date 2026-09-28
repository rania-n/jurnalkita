<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_bisa_ubah_email_sendiri(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'lama@jurnalkita.test']);
        Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru']);

        $this->actingAs($user)->post('/profil', ['email' => 'baru@jurnalkita.test'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('baru@jurnalkita.test', $user->fresh()->email);
    }

    public function test_email_ditolak_kalau_sudah_dipakai_akun_lain(): void
    {
        User::factory()->role('guru')->create(['email' => 'dipakai@jurnalkita.test']);
        $user = User::factory()->role('guru')->create(['email' => 'punyaku@jurnalkita.test']);

        $this->actingAs($user)->post('/profil', ['email' => 'dipakai@jurnalkita.test'])
            ->assertSessionHasErrors(['email']);

        $this->assertSame('punyaku@jurnalkita.test', $user->fresh()->email);
    }

    public function test_email_boleh_disimpan_ulang_tanpa_berubah(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'tetap@jurnalkita.test']);

        $this->actingAs($user)->post('/profil', ['email' => 'tetap@jurnalkita.test'])
            ->assertSessionHasNoErrors();

        $this->assertSame('tetap@jurnalkita.test', $user->fresh()->email);
    }

    public function test_email_wajib_format_valid(): void
    {
        $user = User::factory()->role('guru')->create();

        $this->actingAs($user)->post('/profil', ['email' => 'bukan-email'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_no_hp_ikut_tersimpan_bareng_email(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'guru@jurnalkita.test', 'no_hp' => null]);

        $this->actingAs($user)->post('/profil', ['email' => 'guru@jurnalkita.test', 'no_hp' => '081234567890'])
            ->assertSessionHasNoErrors();

        $this->assertSame('081234567890', $user->fresh()->no_hp);
    }

    public function test_guru_bisa_ubah_nip_sendiri(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'guru@jurnalkita.test']);
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru', 'nip' => '111']);

        $this->actingAs($user)->post('/profil', ['email' => 'guru@jurnalkita.test', 'nip' => '222333444'])
            ->assertSessionHasNoErrors();

        $this->assertSame('222333444', $guru->fresh()->nip);
    }

    public function test_nip_ditolak_kalau_sudah_dipakai_guru_lain(): void
    {
        Guru::create(['nama' => 'Guru Lain', 'nip' => '999888777']);
        $user = User::factory()->role('guru')->create(['email' => 'guru@jurnalkita.test']);
        Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru', 'nip' => '111']);

        $this->actingAs($user)->post('/profil', ['email' => 'guru@jurnalkita.test', 'nip' => '999888777'])
            ->assertSessionHasErrors(['nip']);
    }

    public function test_nip_boleh_dikosongkan(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'guru@jurnalkita.test']);
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru', 'nip' => '111']);

        $this->actingAs($user)->post('/profil', ['email' => 'guru@jurnalkita.test', 'nip' => ''])
            ->assertSessionHasNoErrors();

        $this->assertNull($guru->fresh()->nip);
    }

    public function test_kata_sandi_ikut_bisa_diganti_lewat_form_profil_gabungan(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'guru@jurnalkita.test']);

        $this->actingAs($user)->post('/profil', [
            'email' => 'guru@jurnalkita.test',
            'current_password' => 'password',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('sandi-baru-123', $user->fresh()->password));
    }

    public function test_kata_sandi_boleh_dikosongkan_kalau_cuma_mau_ubah_email(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'lama@jurnalkita.test']);
        $passwordSemula = $user->password;

        $this->actingAs($user)->post('/profil', ['email' => 'baru@jurnalkita.test'])
            ->assertSessionHasNoErrors();

        $this->assertSame('baru@jurnalkita.test', $user->fresh()->email);
        $this->assertSame($passwordSemula, $user->fresh()->password);
    }

    public function test_kata_sandi_lama_yang_salah_ditolak(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'guru@jurnalkita.test']);

        $this->actingAs($user)->post('/profil', [
            'email' => 'guru@jurnalkita.test',
            'current_password' => 'salah',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
        ])->assertSessionHasErrors(['current_password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_konfirmasi_kata_sandi_baru_harus_cocok(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'guru@jurnalkita.test']);

        $this->actingAs($user)->post('/profil', [
            'email' => 'guru@jurnalkita.test',
            'current_password' => 'password',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'tidak-cocok',
        ])->assertSessionHasErrors(['password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_siswa_tidak_punya_data_guru_bisa_ubah_profil_tanpa_nip(): void
    {
        $user = User::factory()->role('siswa')->create(['email' => 'siswa@jurnalkita.test']);

        $this->actingAs($user)->post('/profil', ['email' => 'siswa@jurnalkita.test', 'no_hp' => '081200000000'])
            ->assertSessionHasNoErrors();

        $this->assertSame('081200000000', $user->fresh()->no_hp);
    }
}
