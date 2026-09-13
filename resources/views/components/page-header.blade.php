@props([
    'title',
    'subtitle' => null,
    'back' => null,
])

<div {{ $attributes->class('mb-5') }}>
    @if ($back)
        <a
            href="{{ $back }}"
            class="press mb-4 inline-flex items-center gap-1.5 rounded-lg bg-surface-alt py-2 pl-2 pr-3 text-sm font-semibold text-ink hover:bg-[#cbd5e1]"
        >
            <x-icon name="arrow_back" :size="18" />
            <span>Kembali</span>
        </a>
    @endif

    {{-- Judul panjang + tombol aksi gampang sesak berdempetan di layar sempit --
         susun ke bawah (judul dulu, tombol di bawahnya) di mobile, baru sejajar
         mulai `sm:`. --}}
    <header class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-start">
        <div class="flex flex-1 flex-col gap-0.5">
            <h1 class="text-[22px] font-bold leading-tight text-ink lg:text-[26px]">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-sm leading-snug text-muted lg:text-[15px]">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($slot->isNotEmpty())
            <div class="flex shrink-0 flex-wrap gap-2">{{ $slot }}</div>
        @endif
    </header>
</div>
