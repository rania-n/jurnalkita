@props(['action'])

@php
    // Aktif jika ada query filter selain 'page'
    $hasFilter = collect(request()->except('page'))->filter(fn ($v) => $v !== '' && $v !== null)->isNotEmpty();
@endphp

<form method="GET" action="{{ $action }}" class="mb-4 flex flex-wrap items-end gap-2">
    {{ $slot }}

    <button type="submit" class="press flex h-10 items-center gap-1.5 rounded-lg bg-navy px-4 text-sm font-semibold text-card hover:bg-navy-hover">
        <x-icon name="filter_alt" :size="16" /> Terapkan
    </button>

    @if ($hasFilter)
        <a href="{{ $action }}" class="flex h-10 items-center gap-1 rounded-lg px-3 text-sm font-semibold text-muted hover:text-ink">
            <x-icon name="close" :size="16" /> Reset
        </a>
    @endif
</form>
