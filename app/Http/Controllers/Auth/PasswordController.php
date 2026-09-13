<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        return back()->with('status', 'password-updated');
    }

    /**
     * Kirim link reset sandi ke email sendiri (dipakai dari halaman Profil).
     * Beda dari update() di atas -- ini nggak butuh tahu sandi lama, jadi tetap
     * bisa dipakai walau guru sudah lupa sandinya sendiri.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        PasswordBroker::sendResetLink(['email' => $request->user()->email]);

        return back()->with('status', 'reset-link-sent');
    }
}
