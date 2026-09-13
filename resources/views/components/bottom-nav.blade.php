@props(['menu' => 'default'])

@php
    $items = collect(config("navigation.{$menu}", []))
        ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']))
        ->map(fn ($item) => array_merge($item, [
            'url' => route($item['route']),
            'active' => request()->routeIs($item['match'] ?? $item['route']),
        ]));

    // Item 'fab' (mis. "Isi Jurnal") dipisah dari daftar biasa -- dirender sebagai
    // tombol bulat melayang di tengah, bukan ikut baris menu rata seperti yang lain.
    $fab = $items->firstWhere('fab', true);
    $items = $items->reject(fn ($item) => $item['fab'] ?? false);
@endphp

@if ($items->isNotEmpty())
    <nav
        class="fixed inset-x-0 bottom-0 z-40 h-16 border-t border-surface-alt bg-card lg:hidden"
        style="padding-bottom: env(safe-area-inset-bottom);"
        aria-label="Navigasi utama"
    >
        <ul class="mx-auto flex h-16 max-w-lg items-stretch justify-around">
            @foreach ($items as $item)
                <li class="flex-1">
                    <a
                        href="{{ $item['url'] }}"
                        @class([
                            'flex h-full flex-col items-center justify-center gap-0.5 text-[10px] font-semibold transition-colors',
                            'text-navy' => $item['active'],
                            'text-muted-2' => ! $item['active'],
                        ])
                        @if ($item['active']) aria-current="page" @endif
                    >
                        <x-icon :name="$item['icon']" :size="24" :fill="$item['active']" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($fab)
            {{-- Satu bentuk pil melayang (ikon + label NYATU dalam satu latar
                 navy), bukan lingkaran + teks terpisah -- biar kelihatan satu
                 tombol utuh, bukan ikon dengan label yang "kelempar" di luar. --}}
            <a
                href="{{ $fab['url'] }}"
                class="press absolute left-1/2 top-0 flex -translate-x-1/2 -translate-y-3.5 flex-col items-center gap-0.5 rounded-2xl bg-navy px-4 py-2 text-card shadow-lg shadow-navy/30 ring-4 ring-surface"
            >
                <x-icon :name="$fab['icon']" :size="22" fill />
                <span class="text-[10px] font-bold leading-none whitespace-nowrap">{{ $fab['label'] }}</span>
            </a>
        @endif
    </nav>
@endif
