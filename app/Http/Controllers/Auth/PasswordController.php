<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\PasswordDiubah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::catat('Ganti Password', "Pengguna {$request->user()->email} mengganti password secara mandiri", $request->user());

        $adminUsers = User::where('role', 'admin')->get();
        Notification::send($adminUsers, new PasswordDiubah($request->user(), $request->user()));

        return back()->with('status', 'password-updated');
    }

    /**
     * Kirim link reset sandi ke email sendiri (dipakai dari halaman Profil).
     * Beda dari update() di atas -- ini nggak butuh tahu sandi lama, jadi tetap
     * bisa dipakai walau guru sudah lupa sandinya sendiri.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        // Layanan email pihak ketiga bisa gagal (kuota habis, domain belum
        // diverifikasi, dll) -- tangkap di sini biar user cuma lihat pesan
        // ramah, bukan halaman error mentah kayak yang sempat kejadian.
        try {
            PasswordBroker::sendResetLink(['email' => $request->user()->email]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengirim email reset sandi. Coba lagi nanti atau hubungi admin.');
        }

        return back()->with('status', 'reset-link-sent');
    }
}
