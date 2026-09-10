@props(['action'])

@php
    $hasFilter = collect(request()->except('page', 'hari'))->filter(fn ($v) => $v !== '' && $v !== null)->isNotEmpty();
@endphp

<form method="GET" action="{{ $action }}" class="mb-4 flex flex-wrap items-end gap-2">
    {{ $slot }}

    <button type="submit" class="press flex h-10 items-center gap-1.5 rounded-lg border border-surface-alt bg-card px-3 text-sm font-semibold text-ink hover:bg-surface">
        <x-icon name="search" :size="16" /> Cari
    </button>

    @if ($hasFilter)
        <a href="{{ $action }}" class="flex h-10 items-center gap-1 rounded-lg px-2 text-sm font-semibold text-muted hover:text-alpha">
            <x-icon name="close" :size="16" /> Reset
        </a>
    @endif
</form>
