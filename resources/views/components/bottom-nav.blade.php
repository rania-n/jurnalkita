@props(['menu' => 'default'])

@php
    $items = collect(config("navigation.{$menu}", []))
        ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']))
        ->map(fn ($item) => array_merge($item, [
            'url' => route($item['route']),
            'active' => request()->routeIs($item['match'] ?? $item['route']),
        ]));

    // FAB ("Isi Jurnal") dikeluarkan dari daftar menu biasa & dapat SLOT
    // SENDIRI selebar dirinya di ujung kanan (bukan ikut jatah lebar flex-1
    // bareng menu lain, dan bukan juga cuma absolute nempel di atas menu
    // terakhir -- itu sempat bikin dia numpuk sama tombol menu paling kanan,
    // mis. Profil, karena keduanya sama-sama nempel ke tepi kanan). Menu
    // yang tersisa jadi rata bagi SISA lebar (bukan lebar penuh) di kiri
    // slot ini, jadi otomatis geser kiri & nggak ada yang numpuk.
    $itemFabRaw = $items->first(fn ($item) => $item['fab'] ?? false);
    $itemsMenu = $itemFabRaw ? $items->reject(fn ($item) => $item['fab'] ?? false)->values() : $items;
    $itemFab = $itemFabRaw && !request()->routeIs($itemFabRaw['route']) ? $itemFabRaw : null;
@endphp

@if ($items->isNotEmpty())
    <nav
        class="fixed inset-x-0 bottom-0 z-40 h-16 border-t border-surface-alt bg-card lg:hidden"
        style="padding-bottom: env(safe-area-inset-bottom);"
        aria-label="Navigasi utama"
    >
        <div class="relative mx-auto flex h-16 max-w-lg items-stretch">
            <ul class="flex flex-1 items-stretch justify-around">
                @foreach ($itemsMenu as $item)
                    @include('components._bottom-nav-item', ['item' => $item])
                @endforeach
            </ul>

            @if ($itemFab)
                {{-- Slot kosong selebar tombolnya -- jatah ruang doang biar menu di
                     kiri nggak numpuk ke sini, BUKAN tempat tombolnya nangkring
                     (tombolnya absolute, lihat di bawah). --}}
                <div class="w-24 shrink-0" aria-hidden="true"></div>

                <a
                    href="{{ $itemFab['url'] }}"
                    data-nav-fab
                    class="press absolute right-1 top-0 flex h-20 w-20 -translate-y-8 flex-col items-center justify-center gap-0.5 rounded-full bg-navy text-card shadow-lg shadow-navy/30 ring-4 ring-surface"
                >
                    <x-icon :name="$itemFab['icon']" :size="22" fill />
                    <span class="text-[10px] font-bold leading-none whitespace-nowrap">{{ $itemFab['label'] }}</span>
                </a>
            @endif
        </div>
    </nav>
@endif
