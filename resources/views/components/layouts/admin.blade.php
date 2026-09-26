@props([
    'title' => null,
    'heading' => null,
])

@php
    $user = auth()->user();
    // Satu sumber nav dipakai bareng sama halaman oversight (lihat config/navigation.php).
    $nav = config('navigation.admin');

    $notifikasiTerbaru = $user?->notifications()->latest()->limit(8)->get() ?? collect();
    $jumlahBelumDibaca = $user?->unreadNotifications->count() ?? 0;
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
                {{-- Format nama+role sama persis kayak shell non-admin (x-app-topbar) --
                     dulu di sini nama disembunyikan di HP (hidden sm:block) & badge-nya
                     hardcode "Admin", sekarang selalu kelihatan & pakai roleLabel(). --}}
                <span class="flex flex-col items-end leading-tight">
                    <span class="text-sm font-semibold text-ink">{{ $user?->name }}</span>
                    <span class="text-[11px] font-bold text-muted">{{ $user?->roleLabel() }}</span>
                </span>

                <button type="button" data-modal-open="modal-notifikasi" class="relative ml-1 flex h-9 w-9 items-center justify-center rounded-full bg-surface-alt text-muted transition-colors hover:text-navy" aria-label="Notifikasi">
                    <x-icon name="notifications" :size="20" />
                    @if ($jumlahBelumDibaca > 0)
                        <span class="absolute right-1 top-1 flex h-2.5 w-2.5 rounded-full bg-alpha ring-2 ring-card"></span>
                    @endif
                </button>

                {{-- Logout juga di header (bukan cuma di sidebar) -- di HP sidebar
                     ketutup hamburger, jadi keluar susah dicari kalau cuma di situ.
                     Di desktop disembunyikan karena sidebar sudah ada tombol keluar. --}}
                <span class="lg:hidden">
                    <x-logout-button variant="icon" />
                </span>
            </div>
        </header>

        {{-- errorBag khusus (lihat catatan yang sama di app-topbar.blade.php) --
             biar modal ini nggak ikut kebuka pas form LAIN di halaman admin
             manapun gagal validasi (mis. Buat Akun di Manajemen Akun). --}}
        <x-ui.modal id="modal-notifikasi" title="Notifikasi" size="lg" errorBag="tidak-dipakai">
            @if ($notifikasiTerbaru->isEmpty())
                <x-ui.empty icon="notifications" title="Belum ada notifikasi" desc="Pemberitahuan akan muncul di sini." />
            @else
                <div class="flex flex-col gap-1.5">
                    @foreach ($notifikasiTerbaru as $n)
                        <a href="{{ route('notifikasi.buka', $n->id) }}" @class(['flex items-start gap-2.5 rounded-xl px-3 py-2.5 transition-colors hover:bg-surface-alt', 'bg-surface-alt' => is_null($n->read_at)])>
                            @if (is_null($n->read_at))
                                <span class="mt-1.5 flex h-2 w-2 shrink-0 rounded-full bg-alpha"></span>
                            @else
                                <span class="mt-1.5 h-2 w-2 shrink-0"></span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-ink">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                                <span class="block truncate text-xs text-muted">{{ $n->data['body'] ?? '' }}</span>
                                <span class="block text-[11px] text-muted-2">{{ $n->created_at->diffForHumans() }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>

                @if ($jumlahBelumDibaca > 0)
                    <form method="POST" action="{{ route('notifikasi.tandai-semua-dibaca') }}" class="mt-3 border-t border-surface-alt pt-3">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" icon="done_all" class="w-full !h-10 !text-sm">Tandai Semua Dibaca</x-ui.button>
                    </form>
                @endif

                <a href="{{ route('notifikasi.index') }}" class="mt-2 block text-center text-sm font-semibold text-navy hover:underline">Lihat semua notifikasi</a>
            @endif
        </x-ui.modal>

        <main class="w-full flex-1 px-5 py-6 sm:px-6 lg:px-10 lg:py-8 2xl:px-16">
            @foreach (['success', 'error', 'info'] as $key)
                @if (session($key))
                    <x-alert :type="$key === 'error' ? 'error' : ($key === 'info' ? 'info' : 'success')" class="mb-4">{{ session($key) }}</x-alert>
                @endif
            @endforeach

            {{-- Bag "default" -- gagal validasi (mis. hari Waka yang sudah
                 terisi) SEBELUMNYA nggak kelihatan sama sekali di halaman
                 admin manapun: form-nya redirect back dengan $errors, tapi
                 nggak ada satupun yang nampilinnya (cuma flash session
                 di atas yang ke-render, itu beda mekanisme dari $errors
                 validasi) -- admin ngerasa submit-nya nggak ngefek tanpa
                 tau kenapa. Bag khusus (mis. errorBag="jp" di modal Jam
                 Pelajaran) SENGAJA nggak ikut di sini -- itu bag punya
                 modal sendiri, biar nggak dobel ditampilin. --}}
            @if ($errors->getBag('default')->any())
                <x-alert type="error" class="mb-4">{{ $errors->getBag('default')->first() }}</x-alert>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
