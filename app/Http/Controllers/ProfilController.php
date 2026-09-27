<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    /**
     * No. WhatsApp, Email, dan NIP -- field akun yang boleh diubah mandiri
     * oleh guru/siswa/waka tanpa lewat Admin (nama, kelas, mapel, dll tetap
     * harus lewat Manajemen Akun, lihat alert di profil.blade.php). Admin
     * sendiri sudah punya jalur ubah profil sendiri lewat master.akun.update
     * (form terpisah di halaman ini, khusus admin).
     */
    public function updateNoHp(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('ubahNoHp', [
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->update(['no_hp' => $data['no_hp']]);

        return back()->with('success', 'No. WhatsApp berhasil diperbarui.');
    }

    /**
     * Sengaja TIDAK mengosongkan email_verified_at pas email diganti --
     * sama kayak AkunController::update() (Admin ganti email user lain)
     * yang juga nggak menyentuh field itu. Rute /profil dan hampir semua
     * rute lain dijaga middleware 'verified'; kalau email_verified_at
     * ikut direset di sini, pengguna yang baru saja ganti emailnya sendiri
     * bakal langsung terkunci dari akunnya sendiri sampai sempat klik
     * tautan verifikasi -- risiko yang nggak sepadan buat aplikasi
     * internal sekolah begini (bukan layanan publik yang perlu bukti
     * kepemilikan email seketat itu).
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validateWithBag('ubahEmail', [
            'email' => [
                'required', 'email', 'lowercase', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id)->withoutTrashed(),
            ],
        ]);

        $user->update(['email' => $data['email']]);

        return back()->with('success', 'Email berhasil diperbarui.');
    }

    /**
     * NIP milik record Guru (bukan users.nip -- itu kolom lama, belum
     * dipakai lagi di alur ini), jadi cuma relevan buat akun yang punya
     * data Guru terhubung. Unik lintas guru (kecuali kosong) -- jangan
     * sampai 2 guru kebagian NIP yang sama gara-gara salah ketik.
     */
    public function updateNip(Request $request): RedirectResponse
    {
        $guru = $request->user()->guru;
        abort_unless($guru, 404);

        $data = $request->validateWithBag('ubahNip', [
            'nip' => [
                'nullable', 'string', 'max:30',
                Rule::unique('gurus', 'nip')->ignore($guru->id),
            ],
        ]);

        $guru->update(['nip' => $data['nip']]);

        return back()->with('success', 'NIP berhasil diperbarui.');
    }
}
