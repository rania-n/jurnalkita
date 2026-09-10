<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
            'role' => ['required', 'in:guru,siswa,waka'],
            'sumber' => ['nullable', 'string'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', Rule::unique('users', 'email')->withoutTrashed()],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
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
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);

        if ($data['role'] === 'guru') {
            $guru = $sumberId ? Guru::findOrFail($sumberId) : new Guru([
                'nama' => $data['nama'], 'nip' => $data['nip'] ?? null,
            ]);
            $guru->user_id = $user->id;
            $guru->save();
        } elseif ($data['role'] === 'siswa') {
            if (! $sumberId) {
                $request->validate([
                    'kelas_id' => ['required', 'exists:kelas,id'],
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

        AuditLog::catat('akun.buat', "Buat akun {$data['role']}: {$data['email']}", $user);

        return back()->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    public function approve(User $user): RedirectResponse
    {
        $user->update(['status' => 'approved']);
        AuditLog::catat('akun.setujui', "Setujui akun: {$user->email}", $user);

        return back()->with('success', "Akun {$user->name} disetujui.");
    }

    public function reject(User $user): RedirectResponse
    {
        $user->update(['status' => 'rejected']);
        AuditLog::catat('akun.tolak', "Tolak akun: {$user->email}", $user);

        return back()->with('success', "Akun {$user->name} ditolak.");
    }

    /** Kirim email tautan reset password ke user (admin tidak menyentuh password). */
    public function sendResetLink(User $user): RedirectResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        AuditLog::catat('akun.kirim_reset', "Kirim tautan reset sandi: {$user->email}", $user);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            $status === Password::RESET_LINK_SENT
                ? "Tautan reset sandi dikirim ke {$user->email}."
                : 'Gagal mengirim tautan reset. Cek konfigurasi email.'
        );
    }
}
