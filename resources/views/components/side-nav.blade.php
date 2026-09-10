@props(['menu' => 'default'])

@php
    $items = collect(config("navigation.{$menu}", []))
        ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']))
        ->map(fn ($item) => array_merge($item, [
            'url' => route($item['route']),
            'active' => request()->routeIs($item['match'] ?? $item['route']),
        ]));
@endphp

<aside class="hidden shrink-0 border-r border-surface-alt bg-card lg:block lg:w-64">
    <div class="sticky top-0 flex h-screen flex-col px-4 py-6">
        <a href="{{ url('/') }}" class="flex items-center gap-2 px-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-navy text-card">
                <x-icon name="menu_book" :size="20" fill />
            </span>
            <span class="text-base font-extrabold text-ink">jurnalkita</span>
        </a>

        @if ($items->isNotEmpty())
            <nav class="mt-8 flex flex-col gap-1" aria-label="Navigasi utama">
                @foreach ($items as $item)
                    <a
                        href="{{ $item['url'] }}"
                        @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors',
                            'bg-surface text-navy' => $item['active'],
                            'text-muted hover:bg-surface hover:text-ink' => ! $item['active'],
                        ])
                        @if ($item['active']) aria-current="page" @endif
                    >
                        <x-icon :name="$item['icon']" :size="20" :fill="$item['active']" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        @endif
    </div>
</aside>
