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
                @php $isFab = $item['fab'] ?? false; @endphp
                <li @class(['flex-1', 'relative' => $isFab])>
                    @if ($isFab)
                        {{-- Tombol pil melayang tetap jadi item flex di URUTAN ASLINYA
                             (punya slot flex-1 sendiri), bukan absolute ke tengah SELURUH
                             nav -- kalau ke tengah nav, posisinya ikut geser numpuk ke menu
                             tetangga tiap kali jumlah menu lain berubah ganjil/genap
                             (pernah kejadian: nambah menu "Jadwal" bikin FAB numpuk ke situ). --}}
                        <a
                            href="{{ $item['url'] }}"
                            class="press absolute left-1/2 top-0 flex -translate-x-1/2 -translate-y-3.5 flex-col items-center gap-0.5 rounded-2xl bg-navy px-4 py-2 text-card shadow-lg shadow-navy/30 ring-4 ring-surface"
                        >
                            <x-icon :name="$item['icon']" :size="22" fill />
                            <span class="text-[10px] font-bold leading-none whitespace-nowrap">{{ $item['label'] }}</span>
                        </a>
                    @else
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
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
@endif
