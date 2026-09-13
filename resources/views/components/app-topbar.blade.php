@php
    $user = auth()->user();
    $nama = $user?->name ?? 'Pengguna';

    // Label chip role -- beberapa peran nyesuain konteks HARI INI, bukan cuma nilai
    // role di database (guru piket hari ini, waka yang lagi gilirannya, pengurus
    // kelas nampilin kelasnya). Chip ini satu-satunya identitas yang kelihatan di
    // mobile (nama & sapaan di bawah sengaja disembunyikan di layar sempit), jadi
    // taruh info yang paling penting di sini, bukan cuma di sapaan desktop.
    $roleLabel = match ($user?->role) {
        'admin' => 'Admin',
        'guru' => $user->piketHariIni() ? 'Guru Piket' : 'Guru',
        'siswa' => $user->kelasSekretaris() ? 'Pengurus '.$user->kelasSekretaris()->nama : 'Pengurus Kelas',
        'waka' => 'Waka Kesiswaan',
        'satpam' => 'Satpam',
        default => '',
    };

    $inisial = collect(explode(' ', $nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');

    // Hubungi Admin -- dulu cuma nongol di dasbor Guru, sekarang ditaruh di topbar
    // biar bisa dijangkau dari halaman MANA PUN, bukan cuma pas kebetulan lagi di
    // beranda. Guru rata-rata bapak/ibu yang kurang teknologi, jadi jalur bantuan
    // harus selalu kelihatan, bukan disembunyikan.
    $admin = $user?->role === 'guru' ? \App\Models\User::where('role', 'admin')->whereNotNull('no_hp')->first() : null;
    $waLinkAdmin = $admin ? \App\Support\WaLink::url($admin->no_hp, "Halo Admin jurnalkita, saya {$nama}, mau tanya soal akun/jadwal.") : null;

    $jumlahBelumDibaca = $user?->unreadNotifications->count() ?? 0;
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
        @if ($roleLabel)
            <span class="hidden rounded-md bg-surface-alt px-2 py-1 text-[11px] font-bold text-ink sm:inline-block">{{ $roleLabel }}</span>
        @endif

        @if ($waLinkAdmin)
            <a href="{{ $waLinkAdmin }}" target="_blank" rel="noopener"
               class="flex h-9 w-9 items-center justify-center rounded-full bg-surface-alt text-muted transition-colors hover:text-navy"
               aria-label="Hubungi Admin">
                <x-icon name="support_agent" :size="20" />
            </a>
        @endif

        <a href="{{ route('notifikasi.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full bg-surface-alt text-muted transition-colors hover:text-navy" aria-label="Notifikasi">
            <x-icon name="notifications" :size="20" />
            @if ($jumlahBelumDibaca > 0)
                <span class="absolute right-1 top-1 flex h-2.5 w-2.5 rounded-full bg-alpha ring-2 ring-card"></span>
            @endif
        </a>

        <a href="{{ route('profil') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-navy text-xs font-bold text-card" aria-label="Profil &amp; keluar">
            {{ $inisial }}
        </a>
    </div>
</header>
