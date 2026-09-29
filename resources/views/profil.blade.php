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
    @if ($admin)
        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-2 lg:gap-6">
            <div class="flex flex-col rounded-2xl border border-surface-alt bg-card p-5 sm:p-6">
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
            </div>

            <div class="flex flex-col gap-4 rounded-2xl border border-surface-alt bg-card p-5 sm:p-6">
                {{-- 'password-updated' cuma dipicu jalur Admin di sini -- non-admin
                     ganti sandinya lewat form-profil gabungan & pakai flash
                     'success' biasa (lihat kartu tunggal di bawah). --}}
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
    @else
        {{-- Non-admin: SATU kartu buat semuanya (info akun + ganti kata sandi) --
             dulu 2 kartu terpisah kesan-kesannya kayak 2 hal yang beda, padahal
             sekarang sama-sama disimpan lewat 1 form + 1 tombol Simpan yang sama. --}}
        <div class="rounded-2xl border border-surface-alt bg-card p-5 sm:p-6">
            @if (session('status') === 'reset-link-sent')
                <x-alert type="success" class="mb-4">
                    Tautan reset kata sandi sudah dikirim ke email Anda. Buka email lalu ikuti tautannya.
                </x-alert>
            @endif

            {{-- Field-nya disabled sampai tombol Edit ditekan (lihat script di
                 bawah); begitu ada error validasi, langsung dibuka dalam mode
                 edit (lihat $editMode) supaya pesan errornya tetap bisa dibaca &
                 diperbaiki, bukan malah ketutup field yang disabled lagi. --}}
            @php $editMode = $errors->any(); @endphp
            <form id="form-profil" method="POST" action="{{ route('profil.update') }}" class="flex flex-col gap-5">
                @csrf

                <p class="text-sm font-bold text-ink">Data Akun</p>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-ui.input label="Email" icon="mail" type="email" name="email" value="{{ old('email', $user->email) }}" required class="sm:col-span-2" data-field-profil :disabled="! $editMode" />

                    @if ($guru)
                        <x-ui.input label="NIP" icon="badge" name="nip" value="{{ old('nip', $guru->nip) }}" data-field-profil :disabled="! $editMode" />
                        <x-ui.field-static label="Mata Pelajaran Utama" icon="menu_book" tone="muted">{{ $guru->mapelUtama->nama ?? '—' }}</x-ui.field-static>
                        @if ($guru->mapels->isNotEmpty())
                            <x-ui.field-static label="Mapel Tambahan" icon="library_books" tone="muted" class="sm:col-span-2">{{ $guru->mapels->pluck('nama')->join(', ') }}</x-ui.field-static>
                        @endif
                        @if ($guru->kelasWali->isNotEmpty())
                            <x-ui.field-static label="Wali Kelas" icon="groups" tone="muted" class="sm:col-span-2">{{ $guru->kelasWali->pluck('nama')->join(', ') }}</x-ui.field-static>
                        @endif
                    @elseif ($siswa)
                        {{-- Kelas+NIS = 2 field 1-kolom, pas genap, dipasangin bareng. --}}
                        <x-ui.field-static label="Kelas" icon="school" tone="muted">{{ $siswa->kelas->nama ?? '—' }}</x-ui.field-static>
                        <x-ui.field-static label="NIS" icon="badge" tone="muted">{{ $siswa->nis }}</x-ui.field-static>
                        <x-ui.field-static label="No. Absen" icon="tag" tone="muted">{{ $siswa->no_absen ?: '—' }}</x-ui.field-static>
                        <x-ui.field-static label="Jabatan" icon="workspace_premium" tone="muted">{{ ucfirst($siswa->jabatan) }}</x-ui.field-static>
                    @endif

                    {{-- SATU sumber buat semua peran: users.no_hp -- boleh diubah
                         sendiri sama pemilik akunnya (sama kayak Email & NIP di
                         atas), guru sering butuh update sendiri (ganti nomor)
                         tanpa nunggu Admin. --}}
                    <x-ui.input label="No. WhatsApp" icon="call" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="sm:col-span-2" data-field-profil :disabled="! $editMode" />
                </div>

                <div class="border-t border-surface-alt pt-5">
                    <p class="text-sm font-bold text-ink">Ganti Kata Sandi</p>
                    <p class="mt-0.5 text-xs text-muted-2">Kosongkan bagian ini kalau tidak ingin mengganti kata sandi.</p>

                    {{-- Semua full-width, satu kolom -- sengaja TIDAK dipasangin
                         2-kolom kayak NIP/Mapel di atas, biar urutan atas-bawah
                         (sandi lama -> sandi baru -> ulangi) kebaca jelas sebagai
                         satu alur, bukan kelompok field yang harus "dicocokkan". --}}
                    <div class="mt-3 flex flex-col gap-3">
                        <x-ui.input type="password" icon="lock" name="current_password" form="form-profil" placeholder="Kata sandi saat ini" autocomplete="current-password" data-field-profil :disabled="! $editMode" />
                        <x-ui.input type="password" icon="lock" name="password" form="form-profil" placeholder="Kata sandi baru" autocomplete="new-password" hint="Minimal 8 karakter." data-field-profil :disabled="! $editMode" />
                        <x-ui.input type="password" icon="lock" name="password_confirmation" form="form-profil" placeholder="Tulis ulang kata sandi baru" autocomplete="new-password" data-field-profil :disabled="! $editMode" />
                    </div>
                </div>
            </form>

            {{-- Reset Kata Sandi (kiri) & Edit/Simpan (kanan) satu baris --
                 tombol Edit/Simpan pakai atribut form="form-profil" biar tetap
                 nyambung ke form di atas walau taruhnya di luar tag <form>
                 (nggak boleh ada <form> di dalam <form> lain di HTML). --}}
            <div class="mt-5 flex items-center justify-between border-t border-surface-alt pt-5">
                <form method="POST" action="{{ route('password.reset-link') }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" icon="lock_reset">Reset Kata Sandi</x-ui.button>
                </form>

                <div class="flex gap-2">
                    <x-ui.button type="button" id="tombol-edit-profil" variant="secondary" icon="edit" form="form-profil" :hidden="$editMode">Edit</x-ui.button>
                    <x-ui.button type="submit" id="tombol-simpan-profil" icon="save" form="form-profil" :hidden="! $editMode">Simpan</x-ui.button>
                </div>
            </div>

            <x-alert type="info" class="mt-5">
                Perubahan nama, kelas, atau mata pelajaran dilakukan oleh Admin.
                <x-ui.admin-contact /> jika ada yang perlu diperbaiki.
            </x-alert>
        </div>

        @push('scripts')
            <script>
                document.getElementById('tombol-edit-profil')?.addEventListener('click', function () {
                    document.querySelectorAll('[data-field-profil]').forEach((wrapper) => {
                        wrapper.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = false);
                    });
                    this.hidden = true;
                    document.getElementById('tombol-simpan-profil').hidden = false;
                });
            </script>
        @endpush
    @endif
</x-dynamic-component>
