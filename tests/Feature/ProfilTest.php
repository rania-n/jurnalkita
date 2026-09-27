<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_bisa_ubah_email_sendiri(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'lama@jurnalkita.test']);
        Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru']);

        $this->actingAs($user)->post('/profil/email', ['email' => 'baru@jurnalkita.test'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('baru@jurnalkita.test', $user->fresh()->email);
    }

    public function test_email_ditolak_kalau_sudah_dipakai_akun_lain(): void
    {
        User::factory()->role('guru')->create(['email' => 'dipakai@jurnalkita.test']);
        $user = User::factory()->role('guru')->create(['email' => 'punyaku@jurnalkita.test']);

        $this->actingAs($user)->post('/profil/email', ['email' => 'dipakai@jurnalkita.test'])
            ->assertSessionHasErrorsIn('ubahEmail', ['email']);

        $this->assertSame('punyaku@jurnalkita.test', $user->fresh()->email);
    }

    public function test_email_boleh_disimpan_ulang_tanpa_berubah(): void
    {
        $user = User::factory()->role('guru')->create(['email' => 'tetap@jurnalkita.test']);

        $this->actingAs($user)->post('/profil/email', ['email' => 'tetap@jurnalkita.test'])
            ->assertSessionHasNoErrors();

        $this->assertSame('tetap@jurnalkita.test', $user->fresh()->email);
    }

    public function test_email_wajib_format_valid(): void
    {
        $user = User::factory()->role('guru')->create();

        $this->actingAs($user)->post('/profil/email', ['email' => 'bukan-email'])
            ->assertSessionHasErrorsIn('ubahEmail', ['email']);
    }

    public function test_guru_bisa_ubah_nip_sendiri(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru', 'nip' => '111']);

        $this->actingAs($user)->post('/profil/nip', ['nip' => '222333444'])
            ->assertSessionHasNoErrors();

        $this->assertSame('222333444', $guru->fresh()->nip);
    }

    public function test_nip_ditolak_kalau_sudah_dipakai_guru_lain(): void
    {
        Guru::create(['nama' => 'Guru Lain', 'nip' => '999888777']);
        $user = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru', 'nip' => '111']);

        $this->actingAs($user)->post('/profil/nip', ['nip' => '999888777'])
            ->assertSessionHasErrorsIn('ubahNip', ['nip']);
    }

    public function test_nip_boleh_dikosongkan(): void
    {
        $user = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $user->id, 'nama' => 'Pak Guru', 'nip' => '111']);

        $this->actingAs($user)->post('/profil/nip', ['nip' => ''])
            ->assertSessionHasNoErrors();

        $this->assertNull($guru->fresh()->nip);
    }

    public function test_siswa_tidak_bisa_akses_ubah_nip_karena_tidak_punya_data_guru(): void
    {
        $user = User::factory()->role('siswa')->create();

        $this->actingAs($user)->post('/profil/nip', ['nip' => '123'])
            ->assertNotFound();
    }
}
