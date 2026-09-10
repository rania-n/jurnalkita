@props([
    'tabs' => [],       // ['value' => 'label', ...]  atau  [['label'=>, 'url'=>, 'active'=>], ...]
    'active' => null,   // value tab aktif (mode sederhana)
    'name' => null,     // kalau diisi -> render <input hidden> + mode tombol (JS segmented)
])

@php
    $items = collect($tabs)->map(function ($v, $k) use ($active) {
        if (is_array($v)) {
            return ['label' => $v['label'], 'url' => $v['url'] ?? null, 'value' => $v['value'] ?? $k, 'active' => $v['active'] ?? false];
        }
        return ['label' => $v, 'url' => null, 'value' => $k, 'active' => $k === $active];
    })->values();

    $itemClass = 'flex-1 whitespace-nowrap rounded-lg px-2 py-2 text-center text-[13px] font-semibold transition-colors';
@endphp

<div
    @if ($name) data-segmented @endif
    {{ $attributes->class('flex w-full items-stretch gap-1 rounded-xl bg-card p-1 shadow-[var(--shadow-card)]') }}
    role="tablist"
>
    @foreach ($items as $item)
        @if ($item['url'])
            <a
                href="{{ $item['url'] }}"
                @class([$itemClass, 'bg-navy text-card' => $item['active'], 'text-muted-2 hover:text-ink' => ! $item['active']])
                @if ($item['active']) aria-current="page" @endif
            >{{ $item['label'] }}</a>
        @else
            <button
                type="button"
                data-segment
                value="{{ $item['value'] }}"
                aria-pressed="{{ $item['active'] ? 'true' : 'false' }}"
                class="{{ $itemClass }} text-muted-2 aria-pressed:bg-navy aria-pressed:text-card"
            >{{ $item['label'] }}</button>
        @endif
    @endforeach

    @if ($name)
        <input type="hidden" name="{{ $name }}" value="{{ $active }}" data-segment-value>
    @endif
</div>
