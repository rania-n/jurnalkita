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

    <header class="flex items-start gap-3">
        <div class="flex flex-1 flex-col gap-0.5">
            <h1 class="text-[22px] font-bold leading-tight text-ink lg:text-[26px]">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-sm leading-snug text-muted lg:text-[15px]">{{ $subtitle }}</p>
            @endif
        </div>

        {{ $slot }}
    </header>
</div>
