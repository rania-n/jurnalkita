@props([
    'title' => null,
    'heading' => null,
])

@php
    $user = auth()->user();
    // Satu sumber nav dipakai bareng sama halaman oversight (lihat config/navigation.php).
    $nav = config('navigation.admin');
@endphp

<!DOCTYPE html>
<html lang="id" class="antialiased">
<x-layouts.head :title="$title ? $title . ' · Admin' : 'Admin'" />

<body class="min-h-screen bg-surface text-ink">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 hidden w-60 shrink-0 flex-col border-r border-surface-alt bg-card lg:flex">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-5 py-4">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-navy text-card">
                <x-icon name="menu_book" :size="20" fill />
            </span>
            <span class="text-base font-extrabold text-ink">jurnalkita</span>
        </a>

        <nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto px-3 py-2">
            @foreach ($nav as $item)
                @php
                    $isGroup = isset($item['items']);
                    $groupActive = $isGroup && collect($item['items'])->contains(
                        fn ($sub) => \Illuminate\Support\Facades\Route::has($sub['route']) && request()->routeIs($sub['match'] ?? $sub['route'])
                    );
                @endphp

                @if ($isGroup)
                    <details class="group" data-nav-group="{{ $item['group'] }}" @if ($groupActive) open @endif>
                        <summary
                            @class([
                                'flex cursor-pointer list-none items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors [&::-webkit-details-marker]:hidden',
                                'text-navy' => $groupActive,
                                'text-muted hover:bg-surface hover:text-ink' => ! $groupActive,
                            ])>
                            <x-icon :name="$item['icon']" :size="20" :fill="$groupActive" />
                            <span class="flex-1">{{ $item['group'] }}</span>
                            <x-icon name="expand_more" :size="18" class="transition-transform group-open:rotate-180" />
                        </summary>

                        <div class="ml-3.5 flex flex-col gap-0.5 border-l border-surface-alt py-0.5 pl-3.5">
                            @foreach ($item['items'] as $sub)
                                @php $active = \Illuminate\Support\Facades\Route::has($sub['route']) && request()->routeIs($sub['match'] ?? $sub['route']); @endphp
                                <a href="{{ \Illuminate\Support\Facades\Route::has($sub['route']) ? route($sub['route']) : '#' }}"
                                   @class([
                                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                                       'bg-surface text-navy' => $active,
                                       'text-muted hover:bg-surface hover:text-ink' => ! $active,
                                   ])>
                                    <x-icon :name="$sub['icon']" :size="18" :fill="$active" />
                                    <span>{{ $sub['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @else
                    @php $active = \Illuminate\Support\Facades\Route::has($item['route']) && request()->routeIs($item['match'] ?? $item['route']); @endphp
                    <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                       @class([
                           'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors',
                           'bg-surface text-navy' => $active,
                           'text-muted hover:bg-surface hover:text-ink' => ! $active,
                       ])>
                        <x-icon :name="$item['icon']" :size="20" :fill="$active" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-surface-alt p-3">
            <x-logout-button variant="nav" />
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex w-full flex-col lg:pl-60">
        <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-surface-alt bg-card/95 px-5 py-3 backdrop-blur sm:px-6 lg:px-10 2xl:px-16">
            <div class="flex items-center gap-3">
                <button type="button" class="lg:hidden" onclick="document.getElementById('admin-sidebar').classList.toggle('hidden')" aria-label="Menu">
                    <x-icon name="menu" :size="24" class="text-ink" />
                </button>
                <h1 class="text-lg font-bold text-ink">{{ $heading ?? $title }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="hidden text-sm text-muted sm:block">{{ $user?->name }}</span>
                <span class="rounded-md bg-surface-alt px-2 py-1 text-[11px] font-bold text-ink">Admin</span>
            </div>
        </header>

        <main class="w-full flex-1 px-5 py-6 sm:px-6 lg:px-10 lg:py-8 2xl:px-16">
            @foreach (['success', 'error', 'info'] as $key)
                @if (session($key))
                    <x-alert :type="$key === 'error' ? 'error' : ($key === 'info' ? 'info' : 'success')" class="mb-4">{{ session($key) }}</x-alert>
                @endif
            @endforeach

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
