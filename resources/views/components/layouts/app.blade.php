@props([
    'title' => null,
    'menu' => null,
    'width' => null,   // dipertahankan utk kompatibilitas; konten kini selalu mengikuti lebar wadah
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
@endphp

<!DOCTYPE html>
<html lang="id" class="antialiased">
<x-layouts.head :title="$title" />

<body class="min-h-screen bg-surface text-ink">
    <div class="lg:flex lg:min-h-screen">
        <x-side-nav :menu="$menu" />

        <div class="flex w-full min-w-0 flex-col">
            <x-app-topbar :menu="$menu" />

            {{-- Konten mengalir memenuhi lebar wadah (setelah sidebar di desktop),
                 dengan gutter yang konsisten. Dibatasi hanya di layar sangat besar. --}}
            <main class="mx-auto w-full max-w-[1600px] flex-1 px-4 pb-24 pt-6 sm:px-6 lg:px-10 lg:pb-16 lg:pt-10">
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
