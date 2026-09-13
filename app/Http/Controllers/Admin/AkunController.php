<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AkunController extends Controller
{
    /**
     * Buat akun login.
     * - "sumber" = "guru:5" / "siswa:12" -> hubungkan ke record yang sudah ada.
     * - "sumber" = "baru" -> buat record baru + akun (waka: akun saja).
     */
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:guru,siswa,waka,satpam'],
            'sumber' => ['nullable', 'string'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', Rule::unique('users', 'email')->withoutTrashed()],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30'],
            'nis' => ['nullable', 'string', 'max:20'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
        ]);

        [, $sumberId] = array_pad(explode(':', $data['sumber'] ?? 'baru'), 2, null);

        $user = User::create([
            'name' => $data['nama'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'no_hp' => $data['no_hp'] ?? null,
            // NIP di sini cuma dipakai buat waka/satpam -- guru punya NIP sendiri di
            // tabel gurus (diisi di bawah), siswa punya NIS.
            'nip' => in_array($data['role'], ['waka', 'satpam'], true) ? ($data['nip'] ?? null) : null,
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        // no_hp cuma disimpan di users.no_hp (satu sumber, dipakai fitur WA) --
        // TIDAK ikut ditulis ke gurus.no_hp/siswas.no_hp, biar nggak ada 2 tempat
        // yang bisa beda nilai buat "nomor WA yang sama".
        if ($data['role'] === 'guru') {
            $guru = $sumberId ? Guru::findOrFail($sumberId) : new Guru([
                'nama' => $data['nama'], 'nip' => $data['nip'] ?? null,
            ]);
            $guru->user_id = $user->id;
            $guru->save();
        } elseif ($data['role'] === 'siswa') {
            if (! $sumberId) {
                $request->validate([
                    'kelas_id' => [
                        'required', 'exists:kelas,id',
                        // Data baru dari sini otomatis jadi pengurus kelas (akun kelas) --
                        // tapi cuma boleh 1 pengurus per kelas.
                        function ($attribute, $value, $fail) {
                            if (Siswa::where('kelas_id', $value)->where('jabatan', 'pengurus')->exists()) {
                                $fail('Kelas ini sudah punya pengurus kelas. Ubah pengurus lama jadi "Anggota" dulu lewat menu Data Siswa.');
                            }
                        },
                    ],
                    'nis' => ['required', 'string', 'max:20'],
                ]);
            }
            $siswa = $sumberId ? Siswa::findOrFail($sumberId) : new Siswa([
                'nama' => $data['nama'],
                'nis' => $data['nis'],
                'kelas_id' => $data['kelas_id'],
                'jenis_kelamin' => $data['jenis_kelamin'] ?? 'L',
                'jabatan' => 'pengurus',
            ]);
            $siswa->user_id = $user->id;
            $siswa->save();
        }

        AuditLog::catat('Buat Akun', "Buat akun {$data['role']}: {$data['email']}", $user);

        return back()->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    /**
     * Ubah akun yang sudah ada -- nama, email, no. HP/NIP, dan (opsional) password
     * langsung. Admin bisa apa saja, jadi nggak perlu muter lewat email "kirim reset"
     * cuma buat ganti password sendiri; "kirim reset" tetap ada di bawah buat kasus
     * pemilik akun mau ganti sendiri tanpa admin tahu passwordnya.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['required', 'exists:users,id'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', Rule::unique('users', 'email')->ignore($request->integer('id'))->withoutTrashed()],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::findOrFail($data['id']);

        $user->update([
            'name' => $data['nama'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?? null,
            'nip' => in_array($user->role, ['waka', 'satpam'], true) ? ($data['nip'] ?? null) : $user->nip,
            ...(filled($data['password'] ?? null) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        AuditLog::catat('Ubah Akun', "Ubah akun: {$user->email}", $user);

        return back()->with('success', "Akun {$user->name} diperbarui.");
    }

    public function approve(User $user): RedirectResponse
    {
        $user->update(['status' => 'approved']);
        AuditLog::catat('Setujui Akun', "Setujui akun: {$user->email}", $user);

        return back()->with('success', "Akun {$user->name} disetujui.");
    }

    public function reject(User $user): RedirectResponse
    {
        $user->update(['status' => 'rejected']);
        AuditLog::catat('Tolak Akun', "Tolak akun: {$user->email}", $user);

        return back()->with('success', "Akun {$user->name} ditolak.");
    }

    /**
     * Hapus akun (soft delete — baris tetap tersimpan untuk jejak audit).
     *
     * Kolom users.email unik di level database, jadi baris yang terhapus tetap
     * "memegang" email aslinya. Email diberi awalan dulu supaya bisa dipakai lagi.
     */
    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $email = $user->email;
        $nama = $user->name;

        DB::transaction(function () use ($user, $email) {
            // Lepas kaitan ke data guru/siswa supaya bisa dibuatkan akun baru.
            $user->guru?->update(['user_id' => null]);
            $user->siswa?->update(['user_id' => null]);

            $user->update(['email' => Str::limit("dihapus-{$user->id}-{$email}", 255, '')]);
            $user->delete();
        });

        AuditLog::catat('Hapus Akun', "Hapus akun: {$email}", $user);

        return back()->with('success', "Akun {$nama} dihapus. Email {$email} bisa dipakai lagi.");
    }

    /** Kirim email tautan reset password ke user (admin tidak menyentuh password). */
    public function sendResetLink(User $user): RedirectResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        AuditLog::catat('Kirim Reset Password', "Kirim tautan reset sandi: {$user->email}", $user);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            $status === Password::RESET_LINK_SENT
                ? "Tautan reset sandi dikirim ke {$user->email}."
                : 'Gagal mengirim tautan reset. Cek konfigurasi email.'
        );
    }
}
