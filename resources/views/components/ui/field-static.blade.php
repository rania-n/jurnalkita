@props([
    'label' => null,
    'icon' => null,
    'tone' => 'default',
])

@php
    // class / hidden / data-* -> wrapper. Sisanya -> kotak nilai.
    $wrapKeys = collect($attributes->getAttributes())
        ->keys()
        ->filter(fn ($k) => $k === 'class' || $k === 'hidden' || str_starts_with($k, 'data-'))
        ->all();
    $wrap = $attributes->only($wrapKeys);
    $box = $attributes->except($wrapKeys);
@endphp

{{-- Informasi biasa tampil sebagai teks. Tone muted dipakai ketika informasi
     berdampingan dengan input nonaktif agar tampilan satu kelompok konsisten. --}}
<div {{ $wrap->class('flex flex-col gap-1.5') }}>
    @if ($label)
        <p class="text-xs font-semibold text-muted-2">{{ $label }}</p>
    @endif

    <div {{ $box->class([
        'flex min-w-0 items-start gap-2 text-[15px] text-ink',
        'min-h-[52px] rounded-xl border border-surface-alt bg-surface-alt px-4 py-3' => $tone === 'muted',
    ]) }}>
        @if ($icon)
            <x-icon :name="$icon" :size="20" class="shrink-0 text-muted-2" />
        @endif
        <span class="min-w-0 flex-1 whitespace-pre-line break-words">{{ $slot }}</span>
    </div>
</div>
