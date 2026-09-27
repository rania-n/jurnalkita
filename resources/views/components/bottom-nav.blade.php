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
    //
    // Kalau lagi BERADA di halaman tujuan FAB ini sendiri (Isi Jurnal),
    // FAB-nya disembunyikan total (bukan cuma diem di situ) -- percuma ada
    // shortcut ke halaman yang lagi dibuka, dan di halaman itu ada sticky-bar
    // "Simpan Jurnal" nempel di atas nav yang bisa numpuk sama FAB kalau
    // dipaksa tetap tampil (lihat components/ui/sticky-bar.blade.php).
    $itemFabRaw = $items->first(fn ($item) => $item['fab'] ?? false);
    $itemsMenu = $itemFabRaw ? $items->reject(fn ($item) => $item['fab'] ?? false)->values() : $items;
    $itemFab = ($itemFabRaw && ! $itemFabRaw['active']) ? $itemFabRaw : null;
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
                     (tombolnya absolute, lihat di bawah). Disembunyikan bareng
                     tombolnya (lihat $itemFab di atas) pas lagi di halaman Isi
                     Jurnal sendiri, jadi menu lain otomatis kebagian lebar penuh. --}}
                <div class="w-24 shrink-0" aria-hidden="true"></div>

                {{-- Cuma naik SEDIKIT (-translate-y-3.5) dari batas atas nav,
                     BUKAN melayang tinggi di atas nav -- kalau kelewat tinggi,
                     dia numpuk sama tombol submit di sticky-bar yang nempel
                     PERSIS di atas nav. --}}
                <a
                    href="{{ $itemFab['url'] }}"
                    data-nav-fab
                    class="press absolute right-1 top-0 flex -translate-y-3.5 flex-col items-center gap-1"
                >
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-navy text-card shadow-lg shadow-navy/30 ring-4 ring-surface">
                        <x-icon :name="$itemFab['icon']" :size="22" fill />
                    </span>
                    <span class="text-[10px] font-bold leading-none text-ink whitespace-nowrap">{{ $itemFab['label'] }}</span>
                </a>
            @endif
        </div>
    </nav>
@endif
