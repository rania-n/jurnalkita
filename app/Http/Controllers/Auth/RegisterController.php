<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Registrasi mandiri, dua jalur:
 *  - Guru mata pelajaran / staff piket  -> role 'guru'
 *  - Pengurus kelas (akun kelas)         -> role 'siswa', jabatan 'pengurus'
 *
 * Semua pendaftaran dibuat dengan status 'pending' dan menunggu persetujuan admin.
 */
class RegisterController extends Controller
{
    public function pilihPeran(): View
    {
        return view('auth.pilih_peran');
    }

    public function createGuru(): View
    {
        return view('auth.register_guru');
    }

    public function storeGuru(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'lowercase', 'max:255', 'unique:users,email'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'guru',
                'status' => 'pending',
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama'],
                'no_hp' => $data['telepon'] ?? null,
            ]);

            return $user;
        });

        event(new Registered($user));

        return redirect()->route('login')->with('info',
            'Pendaftaran terkirim. Akun akan aktif setelah disetujui admin sekolah.');
    }

    public function createKelas(): View
    {
        return view('auth.register_pengurus_kelas', [
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function storeKelas(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'nama' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'lowercase', 'max:255', 'unique:users,email'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'siswa',
                'status' => 'pending',
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $data['kelas_id'],
                'nis' => $data['nis'],
                'nama' => $data['nama'],
                'jenis_kelamin' => 'L',
                'jabatan' => 'pengurus',
            ]);

            return $user;
        });

        event(new Registered($user));

        return redirect()->route('login')->with('info',
            'Pendaftaran terkirim. Akun akan aktif setelah disetujui admin sekolah.');
    }
}
