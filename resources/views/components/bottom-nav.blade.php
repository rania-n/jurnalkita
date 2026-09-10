@props(['menu' => 'default'])

@php
    $items = collect(config("navigation.{$menu}", []))
        ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']))
        ->map(fn ($item) => array_merge($item, [
            'url' => route($item['route']),
            'active' => request()->routeIs($item['match'] ?? $item['route']),
        ]));
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
    </nav>
@endif
