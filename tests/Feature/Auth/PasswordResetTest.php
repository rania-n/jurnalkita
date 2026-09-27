<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    /**
     * Regresi: layanan email pihak ketiga (SMTP/Resend/dst) yang gagal dulu
     * bikin halaman crash total (error mentah, kejadian beneran di produksi).
     * Sekarang harus ketangkep & tampil sebagai pesan ramah di form.
     */
    public function test_reset_password_link_gagal_kirim_tidak_crash(): void
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('Layanan email lagi down'));

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHasErrors('email');
    }

    /** Sama kayak di atas, tapi jalur "Reset Kata Sandi" dari halaman Profil (bukan Lupa Sandi login). */
    public function test_reset_link_dari_profil_gagal_kirim_tidak_crash(): void
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('Layanan email lagi down'));

        $user = User::factory()->role('guru')->create();

        $response = $this->actingAs($user)->post('/password/reset-link');

        $response->assertSessionHas('error');
    }
}
