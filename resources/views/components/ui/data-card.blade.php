@props([
    'title',
    'subtitle' => null,
    'href' => null,
])

@php
    $classes = 'flex items-center gap-3 rounded-xl border border-line bg-card p-3.5 shadow-[var(--shadow-soft)] transition-colors' . ($href ? ' hover:border-navy' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
@else
    <div {{ $attributes->class($classes) }}>
@endif

    @isset($leading)
        <div class="shrink-0">{{ $leading }}</div>
    @endisset

    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
        <span class="truncate text-sm font-bold text-ink">{{ $title }}</span>
        @if ($subtitle)
            <span class="truncate text-[11px] text-muted">{{ $subtitle }}</span>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-1">{{ $actions }}</div>
    @endisset

    @if ($href && ! isset($actions))
        <x-icon name="chevron_right" :size="20" class="shrink-0 text-muted" />
    @endif

@if ($href)
    </a>
@else
    </div>
@endif
