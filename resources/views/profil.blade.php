@php
    $user = auth()->user();
    $guru = $user->guru;
    $siswa = $user->siswa;

    $roleLabel = [
        'admin' => 'Administrator',
        'guru' => 'Guru',
        'siswa' => 'Pengurus Kelas',
        'waka' => 'Waka Kesiswaan',
        'satpam' => 'Satpam',
    ][$user->role] ?? 'Pengguna';

    $nama = $guru->nama ?? $siswa->nama ?? $user->name;
    $inisial = collect(explode(' ', $nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    // Halaman ini dipakai SEMUA peran -- admin pakai shell admin biar konsisten
    // sama sidebar & topbar-nya (lihat catatan yang sama di dispensasi/index dkk).
    $admin = $user->role === 'admin';
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Profil" heading="Profil">
    @if ($admin)
        <x-admin.page title="Profil" subtitle="Data akun Anda" />
    @else
        <x-page-header title="Profil" subtitle="Data akun Anda" />
    @endif

    <div @class(['max-w-2xl', 'rounded-2xl border border-surface-alt bg-card p-5 sm:p-6' => $admin])>
        <div class="flex items-center gap-4 py-2">
            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-navy text-xl font-bold text-card">
                {{ $inisial }}
            </span>
            <div>
                <p class="text-lg font-bold text-ink">{{ $nama }}</p>
                <p class="text-sm text-muted">{{ $roleLabel }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-ui.field-static label="Email" icon="mail" class="sm:col-span-2">{{ $user->email }}</x-ui.field-static>

            {{-- No. WhatsApp SATU sumber buat semua peran: users.no_hp (diisi admin
                 lewat Manajemen Akun) -- bukan dari tabel gurus/siswas. --}}
            <x-ui.field-static label="No. WhatsApp" icon="call">{{ $user->no_hp ?: '—' }}</x-ui.field-static>

            @if ($guru)
                <x-ui.field-static label="NIP" icon="badge">{{ $guru->nip ?: '—' }}</x-ui.field-static>
                <x-ui.field-static label="Mata Pelajaran Utama" icon="menu_book">{{ $guru->mapelUtama->nama ?? '—' }}</x-ui.field-static>
                @if ($guru->mapels->isNotEmpty())
                    <x-ui.field-static label="Mapel Tambahan" icon="library_books">{{ $guru->mapels->pluck('nama')->join(', ') }}</x-ui.field-static>
                @endif
                @if ($guru->kelasWali->isNotEmpty())
                    <x-ui.field-static label="Wali Kelas" icon="groups" class="sm:col-span-2">{{ $guru->kelasWali->pluck('nama')->join(', ') }}</x-ui.field-static>
                @endif
            @elseif ($siswa)
                <x-ui.field-static label="Kelas" icon="school">{{ $siswa->kelas->nama ?? '—' }}</x-ui.field-static>
                <x-ui.field-static label="NIS" icon="badge">{{ $siswa->nis }}</x-ui.field-static>
                <x-ui.field-static label="No. Absen" icon="tag">{{ $siswa->no_absen ?: '—' }}</x-ui.field-static>
                <x-ui.field-static label="Jabatan" icon="workspace_premium">{{ ucfirst($siswa->jabatan) }}</x-ui.field-static>
            @endif
        </div>

        <x-alert type="info" class="mt-6">
            Perubahan data akun (nama, email, dll) dilakukan oleh Admin. Anda hanya bisa
            mengubah <strong>kata sandi</strong> sendiri lewat form di bawah.
        </x-alert>

        @if (session('status') === 'password-updated')
            <x-alert type="success" class="mt-4">Kata sandi berhasil diubah.</x-alert>
        @endif

        <div class="mt-6 border-t border-surface-alt pt-6">
            <h2 class="mb-3 text-sm font-bold text-ink">Ubah Kata Sandi</h2>

            <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-3">
                @csrf
                @method('PUT')

                <x-ui.input label="Kata Sandi Saat Ini" name="current_password" type="password" icon="lock" :error-bag="'updatePassword'" />
                <x-ui.input label="Kata Sandi Baru" name="password" type="password" icon="lock_reset" :error-bag="'updatePassword'" />
                <x-ui.input label="Konfirmasi Kata Sandi Baru" name="password_confirmation" type="password" icon="lock_reset" :error-bag="'updatePassword'" />

                <x-ui.button type="submit" icon="save" class="mt-1 sm:self-start">Simpan Kata Sandi</x-ui.button>
            </form>
        </div>

        <div class="mt-6">
            <x-logout-button variant="full" />
        </div>
    </div>
</x-dynamic-component>
