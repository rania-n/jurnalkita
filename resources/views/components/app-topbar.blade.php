@props(['menu' => 'default'])

@php
    // TODO(auth): ambil dari auth()->user() setelah modul Auth selesai.
    $user = auth()->user();
    $nama = $user->name ?? 'Winartin, S.Pd';
    $role = $user->role ?? 'guru';
    $roleLabel = [
        'admin' => 'Admin',
        'guru' => 'Guru',
        'siswa' => 'Pengurus Kelas',
        'waka' => 'Waka Kesiswaan',
    ][$role] ?? ucfirst($role);
    $inisial = collect(explode(' ', $nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-surface-alt bg-card/95 px-5 py-2.5 backdrop-blur sm:px-6 lg:px-10">
    <a href="{{ url('/') }}" class="flex items-center gap-2 lg:hidden">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-navy text-card">
            <x-icon name="menu_book" :size="18" fill />
        </span>
        <span class="text-sm font-extrabold text-ink">jurnalkita</span>
    </a>

    <p class="hidden text-sm text-muted lg:block">
        Halo, <span class="font-semibold text-ink">{{ $nama }}</span>
    </p>

    <div class="flex items-center gap-2">
        <span class="rounded-md bg-surface-alt px-2 py-1 text-[11px] font-bold text-ink">{{ $roleLabel }}</span>
        <a href="{{ route('profil') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-navy text-xs font-bold text-card" aria-label="Profil">
            {{ $inisial }}
        </a>
    </div>
</header>
