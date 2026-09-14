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

    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-2 lg:gap-6">
        <div @class(['flex flex-col', 'rounded-2xl border border-surface-alt bg-card p-5 sm:p-6' => $admin])>
            <div class="flex items-center gap-4 py-2">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-navy text-xl font-bold text-card">
                    {{ $inisial }}
                </span>
                <div>
                    <p class="text-lg font-bold text-ink">{{ $nama }}</p>
                    <p class="text-sm text-muted">{{ $roleLabel }}</p>
                </div>
            </div>

            @if ($admin)
                @if (session('success'))
                    <x-alert type="success" class="mt-4">
                        {{ session('success') }}
                    </x-alert>
                @endif
                <form action="{{ route('master.akun.update') }}" method="POST" class="mt-4 flex flex-col gap-3">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id }}">
                    <input type="hidden" name="nama" value="{{ $nama }}">

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-ui.input label="Email" icon="mail" type="email" name="email" value="{{ old('email', $user->email) }}" required />
                        </div>
                        <div class="sm:col-span-2">
                            <x-ui.input label="No. WhatsApp" icon="call" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" />
                        </div>
                    </div>
                    
                    <div class="mt-1 flex justify-end">
                        <x-ui.button type="submit" variant="primary">Simpan Profil</x-ui.button>
                    </div>
                </form>
            @else
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
                    Perubahan data akun (nama, email, dll) dilakukan oleh Admin. Hubungi Admin
                    kalau ada yang perlu diperbaiki.
                </x-alert>
            @endif
        </div>

        <div @class(['flex flex-col gap-4', 'rounded-2xl border border-surface-alt bg-card p-5 sm:p-6' => $admin])>
            <div @class(['rounded-2xl border border-surface-alt bg-card p-5' => ! $admin])>
                @if (session('status') === 'password-updated')
                    <x-alert type="success" class="mb-3">
                        Kata sandi berhasil diperbarui.
                    </x-alert>
                @endif

                @if (session('status') === 'reset-link-sent')
                    <x-alert type="success" class="mb-3">
                        Tautan reset kata sandi sudah dikirim ke email Anda. Buka email lalu ikuti tautannya.
                    </x-alert>
                @endif

                <form id="form-password-update" method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-3">
                    @csrf
                    @method('PUT')

                    <x-ui.input type="password" name="current_password" id="current_password" placeholder="Kata sandi saat ini" required autocomplete="current-password" errorBag="updatePassword" />
                    
                    <x-ui.input type="password" name="password" id="password" placeholder="Kata sandi baru" required autocomplete="new-password" errorBag="updatePassword" />
                    
                    <x-ui.input type="password" name="password_confirmation" id="password_confirmation" placeholder="Tulis ulang kata sandi baru" required autocomplete="new-password" errorBag="updatePassword" />
                </form>

                <div class="mt-5 flex items-center justify-between">
                    <form method="POST" action="{{ route('password.reset-link') }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" icon="lock_reset">Reset Kata Sandi</x-ui.button>
                    </form>
                    
                    <x-ui.button type="submit" form="form-password-update" variant="primary">Simpan</x-ui.button>
                </div>
            </div>

            <div class="lg:hidden">
                <x-logout-button variant="full" />
            </div>
        </div>
    </div>
</x-dynamic-component>
