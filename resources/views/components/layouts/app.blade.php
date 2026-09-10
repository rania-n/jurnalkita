@props([
    'title' => null,
    'menu' => null,
    'width' => 'form',   // 'form' (kolom formulir) | 'wide' (daftar / menu)
])

@php
    // Menu navigasi menyesuaikan peran user (fallback: 'default').
    $role = auth()->user()->role ?? null;
    $menu ??= match ($role) {
        'admin' => 'admin',
        'guru' => 'guru',
        'siswa' => 'sekretaris',
        'waka' => 'waka',
        default => 'default',
    };

    // Lebar konten naik bertahap: HP penuh → tablet lebih lega → desktop dibatasi biar terbaca.
    $maxW = $width === 'wide'
        ? 'sm:max-w-xl md:max-w-3xl lg:max-w-5xl'
        : 'sm:max-w-lg md:max-w-2xl lg:max-w-3xl';
@endphp

<!DOCTYPE html>
<html lang="id" class="antialiased">
<x-layouts.head :title="$title" />

<body class="min-h-screen bg-surface text-ink">
    <div class="lg:flex lg:min-h-screen">
        <x-side-nav :menu="$menu" />

        <div class="flex w-full min-w-0 flex-col">
            <x-app-topbar :menu="$menu" />

            <main class="mx-auto w-full flex-1 px-5 pb-24 pt-6 sm:px-6 {{ $maxW }} lg:mx-0 lg:px-10 lg:pb-14 lg:pt-10">
                @foreach (['success' => 'success', 'error' => 'error', 'info' => 'info'] as $key => $type)
                    @if (session($key))
                        <x-alert :type="$type" class="mb-4">{{ session($key) }}</x-alert>
                    @endif
                @endforeach

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-bottom-nav :menu="$menu" />

    @stack('scripts')
</body>
</html>
