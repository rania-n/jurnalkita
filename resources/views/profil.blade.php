@php
    $user = auth()->user();
    $guru = $user->guru;
    $siswa = $user->siswa;

    $nama = $guru->nama ?? $siswa->nama ?? $user->name;
    // Halaman ini dipakai SEMUA peran -- admin pakai shell admin biar konsisten
    // sama sidebar & topbar-nya (lihat catatan yang sama di dispensasi/index dkk).
    $admin = $user->role === 'admin';

    // Sama kayak "Hubungi Admin" di topbar (components/app-topbar.blade.php) --
    // dulu di sini cuma disebut lewat KALIMAT ("Hubungi Admin kalau ada yang
    // perlu diperbaiki") tanpa tautan beneran, padahal maksudnya ngajak
    // pengguna buat action. Null kalau nggak ada akun admin dengan No. WA
    // terdaftar -- kalimatnya tetap muncul, cuma tanpa tautan WA.
    $adminTujuan = ! $admin ? \App\Models\User::where('role', 'admin')->whereNotNull('no_hp')->first() : null;
    $waLinkAdmin = $adminTujuan ? \App\Support\WaLink::url($adminTujuan->no_hp, "Halo Admin jurnalkita, saya {$nama} ({$user->roleLabel()}), ada data akun yang perlu diperbaiki.") : null;
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Profil" heading="Profil">
    @if ($admin)
        <x-admin.page title="Profil" subtitle="Data akun Anda" />
    @else
        {{-- Judul size="sm" -- dikecilin (bukan dihilangin) biar halaman tetap
             ada kop (khusus tampilan non-admin; sidebar Admin beda pola,
             nggak disentuh). --}}
        <x-page-header title="Profil" subtitle="Data akun Anda" size="sm" />
    @endif

    {{-- Avatar + nama + role SENGAJA nggak diulang di sini -- topbar (admin
         maupun non-admin) udah selalu nampilin itu di atas, di halaman
         manapun. Ulang lagi di badan halaman Profil cuma dobel info yang
         sama persis. --}}
    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-2 lg:gap-6">
        <div @class(['flex flex-col', 'rounded-2xl border border-surface-alt bg-card p-5 sm:p-6' => $admin])>
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
                            <x-ui.input label="Email" icon="mail" type="email" name="email" value="{{ old('email', $user->email) }}" required errorBag="ubahAkun" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-ui.input label="No. WhatsApp" icon="call" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" errorBag="ubahAkun" />
                        </div>
                    </div>
                    
                    <div class="mt-1 flex justify-end">
                        <x-ui.button type="submit" variant="primary">Simpan Profil</x-ui.button>
                    </div>
                </form>
            @else
                @if (session('success'))
                    <x-alert type="success" class="mt-4">
                        {{ session('success') }}
                    </x-alert>
                @endif

                {{-- Dulu Email/NIP/No. WhatsApp 3 form+tombol Simpan terpisah (submit
                     sendiri-sendiri per field) -- diringkas jadi SATU form dengan SATU
                     tombol Simpan, biar nggak keliatan berantakan. Field-nya disabled
                     sampai tombol Edit ditekan (lihat script di bawah); begitu ada
                     error validasi, langsung dibuka dalam mode edit (lihat $editMode)
                     supaya pesan errornya tetap bisa dibaca & diperbaiki, bukan malah
                     ketutup field yang disabled lagi. --}}
                @php $editMode = $errors->any(); @endphp
                <form method="POST" action="{{ route('profil.update') }}" class="mt-4 flex flex-col gap-3">
                    @csrf

                    <div class="flex items-center justify-end gap-2">
                        <x-ui.button type="button" id="tombol-edit-profil" variant="secondary" icon="edit" :hidden="$editMode">Edit</x-ui.button>
                        <x-ui.button type="submit" id="tombol-simpan-profil" variant="primary" icon="save" :hidden="! $editMode">Simpan</x-ui.button>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <x-ui.input label="Email" icon="mail" type="email" name="email" value="{{ old('email', $user->email) }}" required class="sm:col-span-2" data-field-profil :disabled="! $editMode" />

                        @if ($guru)
                            <x-ui.input label="NIP" icon="badge" name="nip" value="{{ old('nip', $guru->nip) }}" data-field-profil :disabled="! $editMode" />
                            <x-ui.field-static label="Mata Pelajaran Utama" icon="menu_book">{{ $guru->mapelUtama->nama ?? '—' }}</x-ui.field-static>
                            @if ($guru->mapels->isNotEmpty())
                                <x-ui.field-static label="Mapel Tambahan" icon="library_books" class="sm:col-span-2">{{ $guru->mapels->pluck('nama')->join(', ') }}</x-ui.field-static>
                            @endif
                            @if ($guru->kelasWali->isNotEmpty())
                                <x-ui.field-static label="Wali Kelas" icon="groups" class="sm:col-span-2">{{ $guru->kelasWali->pluck('nama')->join(', ') }}</x-ui.field-static>
                            @endif
                        @elseif ($siswa)
                            {{-- Kelas+NIS = 2 field 1-kolom, pas genap, dipasangin bareng. --}}
                            <x-ui.field-static label="Kelas" icon="school">{{ $siswa->kelas->nama ?? '—' }}</x-ui.field-static>
                            <x-ui.field-static label="NIS" icon="badge">{{ $siswa->nis }}</x-ui.field-static>
                            <x-ui.field-static label="No. Absen" icon="tag">{{ $siswa->no_absen ?: '—' }}</x-ui.field-static>
                            <x-ui.field-static label="Jabatan" icon="workspace_premium">{{ ucfirst($siswa->jabatan) }}</x-ui.field-static>
                        @endif

                        {{-- SATU sumber buat semua peran: users.no_hp -- boleh diubah
                             sendiri sama pemilik akunnya (sama kayak Email & NIP di
                             atas), guru sering butuh update sendiri (ganti nomor)
                             tanpa nunggu Admin. --}}
                        <x-ui.input label="No. WhatsApp" icon="call" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="sm:col-span-2" data-field-profil :disabled="! $editMode" />
                    </div>
                </form>

                <x-alert type="info" class="mt-6">
                    Perubahan nama, kelas, atau mata pelajaran dilakukan oleh Admin.
                    @if ($waLinkAdmin)
                        <a href="{{ $waLinkAdmin }}" target="_blank" rel="noopener" class="font-bold underline">Hubungi Admin melalui WhatsApp</a>
                        jika ada yang perlu diperbaiki.
                    @else
                        Hubungi Admin jika ada yang perlu diperbaiki.
                    @endif
                </x-alert>

                @push('scripts')
                    <script>
                        document.getElementById('tombol-edit-profil')?.addEventListener('click', function () {
                            document.querySelectorAll('[data-field-profil]').forEach((el) => el.disabled = false);
                            this.hidden = true;
                            document.getElementById('tombol-simpan-profil').hidden = false;
                        });
                    </script>
                @endpush
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

                @if (session('error'))
                    <x-alert type="error" class="mb-3">
                        {{ session('error') }}
                    </x-alert>
                @endif

                <form id="form-password-update" method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-3">
                    @csrf
                    @method('PUT')

                    <x-ui.input type="password" name="current_password" id="current_password" placeholder="Kata sandi saat ini" required autocomplete="current-password" errorBag="updatePassword" />
                    
                    <x-ui.input type="password" name="password" id="password" placeholder="Kata sandi baru" required autocomplete="new-password" errorBag="updatePassword" hint="Minimal 8 karakter." />
                    
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
        </div>
    </div>
</x-dynamic-component>
