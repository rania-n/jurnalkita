@props([
    'title',
    'subtitle' => null,
    'back' => null,
])

{{-- Judul halaman SENGAJA nggak diulang di sini -- topbar admin (header
     sticky, selalu kelihatan biar nggak ketutup pas scroll) udah nampilin
     nama halaman yang sama persis lewat prop "heading"/"title" ke
     x-layouts.admin. Dulu di sini juga ada <h2> isinya sama, keliatan
     dobel nggak guna. Subtitle tetap ada -- itu info TAMBAHAN yang topbar
     nggak punya (mis. jumlah data). --}}
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div class="flex flex-col items-start gap-1.5">
        @if ($back)
            <a href="{{ $back }}"
               class="press inline-flex w-fit items-center gap-1.5 rounded-lg bg-surface-alt px-2.5 py-1 text-xs font-semibold text-ink transition-colors hover:bg-[#cbd5e1]">
                <x-icon name="arrow_back" :size="14" /> Kembali
            </a>
        @endif
        @if ($subtitle)
            <p class="text-sm text-muted">{{ $subtitle }}</p>
        @endif
    </div>

    {{-- flex-col di HP biar tombol yang lebih dari satu numpuk rapi & stretch
         penuh (tombolnya sendiri butuh class w-full sm:w-auto, lihat halaman
         yang makai), bukan berdempetan/wrap acak kayak sebelumnya. --}}
    @isset($action)
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">{{ $action }}</div>
    @endisset
</div>
