<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    /**
     * Email, NIP, dan No. WhatsApp -- field akun yang boleh diubah mandiri
     * oleh guru/siswa/waka tanpa lewat Admin (nama, kelas, mapel, dll tetap
     * harus lewat Manajemen Akun, lihat alert di profil.blade.php). Admin
     * sendiri sudah punya jalur ubah profil sendiri lewat master.akun.update
     * (form terpisah di halaman ini, khusus admin). Digabung jadi SATU form/
     * SATU tombol Simpan (dulu 3 form+tombol terpisah per field) -- lebih
     * enak dipakai, dan validasinya tetap per-field lewat @error di blade.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $guru = $user->guru;

        $rules = [
            'email' => [
                'required', 'email', 'lowercase', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id)->withoutTrashed(),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ];

        if ($guru) {
            $rules['nip'] = [
                'nullable', 'string', 'max:30',
                Rule::unique('gurus', 'nip')->ignore($guru->id),
            ];
        }

        $data = $request->validate($rules);

        // Sengaja TIDAK mengosongkan email_verified_at pas email diganti --
        // sama kayak AkunController::update() (Admin ganti email user lain)
        // yang juga nggak menyentuh field itu. Rute /profil dan hampir semua
        // rute lain dijaga middleware 'verified'; kalau email_verified_at
        // ikut direset di sini, pengguna yang baru saja ganti emailnya sendiri
        // bakal langsung terkunci dari akunnya sendiri sampai sempat klik
        // tautan verifikasi -- risiko yang nggak sepadan buat aplikasi
        // internal sekolah begini (bukan layanan publik yang perlu bukti
        // kepemilikan email seketat itu).
        // ?? null buat jaga-jaga -- 'nullable' bikin Laravel nggak nyertain
        // key-nya sama sekali di $data kalau field itu nggak dikirim (BUKAN
        // ikut ada sebagai null), beda sama dikirim kosong. Form-nya emang
        // selalu ngirim semua field ini, tapi tanpa fallback ini kode bakal
        // fatal error kalau suatu saat ada yang manggil endpoint ini tanpa
        // salah satu field (mis. lewat API/Postman).
        $user->update(['email' => $data['email'], 'no_hp' => $data['no_hp'] ?? null]);

        if ($guru) {
            $guru->update(['nip' => $data['nip'] ?? null]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
