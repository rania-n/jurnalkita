@props([
    'title' => null,
    'heading' => null,
])

@php
    $user = auth()->user();
    $nav = [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'admin.dashboard'],
        ['label' => 'Data Guru', 'icon' => 'groups', 'route' => 'master.guru.index'],
        ['label' => 'Data Kelas', 'icon' => 'meeting_room', 'route' => 'master.kelas.index'],
        ['label' => 'Data Siswa', 'icon' => 'school', 'route' => 'master.siswa.index'],
        ['label' => 'Mata Pelajaran', 'icon' => 'menu_book', 'route' => 'master.mapel.index'],
        ['label' => 'Jadwal Pelajaran', 'icon' => 'calendar_month', 'route' => 'master.jadwal-pelajaran.index'],
        ['label' => 'Jam Pelajaran', 'icon' => 'schedule', 'route' => 'master.jam-pelajaran.index'],
        ['label' => 'Jadwal Piket', 'icon' => 'event_available', 'route' => 'master.jadwal-piket.index'],
    ];
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
                @php $active = \Illuminate\Support\Facades\Route::has($item['route']) && request()->routeIs($item['route'] . '*'); @endphp
                <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors',
                       'bg-surface text-navy' => $active,
                       'text-muted hover:bg-surface hover:text-ink' => ! $active,
                   ])>
                    <x-icon :name="$item['icon']" :size="20" :fill="$active" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-surface-alt p-3">
            <x-logout-button variant="nav" />
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex w-full flex-col lg:pl-60">
        <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-surface-alt bg-card/95 px-5 py-3 backdrop-blur lg:px-8">
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

        <main class="mx-auto w-full max-w-5xl flex-1 px-5 py-6 lg:px-8 lg:py-8">
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
