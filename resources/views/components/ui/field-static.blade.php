@props([
    'label' => null,
    'icon' => null,
    // 'default' (putih+border) | 'muted' (abu-abu, buat field yang DIKUNCI dari
    // input form -- biar beda kesannya dari field view-only biasa yang emang
    // dari sononya cuma buat dilihat, bukan "sebenarnya bisa diisi tapi dikunci").
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

{{-- Nilai read-only bergaya seperti input (dipakai di layar detail). --}}
<div {{ $wrap->class('flex flex-col gap-1.5') }}>
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    <div {{ $box->class([
        'flex min-h-[52px] items-center gap-2 rounded-xl px-4 text-[15px] text-ink',
        'border border-surface-alt bg-card' => $tone === 'default',
        'bg-surface-alt' => $tone === 'muted',
    ]) }}>
        @if ($icon)
            <x-icon :name="$icon" :size="20" class="shrink-0 text-muted-2" />
        @endif
        <span class="flex-1">{{ $slot }}</span>
    </div>
</div>
