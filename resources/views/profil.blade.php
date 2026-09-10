@php
    $user = auth()->user();
    $guru = $user->guru;
    $siswa = $user->siswa;

    $roleLabel = [
        'admin' => 'Administrator',
        'guru' => 'Guru',
        'siswa' => 'Pengurus Kelas',
        'waka' => 'Waka Kesiswaan',
    ][$user->role] ?? 'Pengguna';

    $nama = $guru->nama ?? $siswa->nama ?? $user->name;
    $inisial = collect(explode(' ', $nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<x-layouts.app title="Profil">
    <x-page-header title="Profil" subtitle="Data akun Anda" />

    <div class="max-w-2xl">
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

            @if ($guru)
                <x-ui.field-static label="NIP" icon="badge">{{ $guru->nip ?: '—' }}</x-ui.field-static>
                <x-ui.field-static label="No. WhatsApp" icon="call">{{ $guru->no_hp ?: '—' }}</x-ui.field-static>
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
            Perubahan data akun dilakukan oleh Admin. Untuk ganti kata sandi, gunakan
            <strong>Lupa kata sandi</strong> di halaman masuk.
        </x-alert>

        <div class="mt-6">
            <x-logout-button variant="full" />
        </div>
    </div>
</x-layouts.app>
