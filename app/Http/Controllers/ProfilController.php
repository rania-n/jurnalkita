<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    /**
     * No. WhatsApp sendiri -- SATU-SATUNYA field akun yang boleh diubah
     * mandiri oleh guru/siswa/waka/satpam tanpa lewat Admin (nama, email, dll
     * tetap harus lewat Manajemen Akun, lihat alert di profil.blade.php).
     * Admin sendiri sudah punya jalur ubah profil sendiri lewat
     * master.akun.update (form terpisah di halaman ini, khusus admin).
     */
    public function updateNoHp(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('ubahNoHp', [
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->update(['no_hp' => $data['no_hp']]);

        return back()->with('success', 'No. WhatsApp berhasil diperbarui.');
    }
}
