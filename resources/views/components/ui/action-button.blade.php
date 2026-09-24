@props([
    'label',
    'icon',
    'variant' => 'neutral',
    'href' => null,
    'type' => 'button',
])

@php
    // Border tipis di danger/success disamain sama x-ui.button -- background-nya
    // "soft" (pucat), tanpa garis tepi kelihatan nyampur sama kartu putih di
    // sekitarnya, apalagi berdempetan sama tombol lain yang PAKAI border.
    $variants = [
        'neutral' => 'bg-surface-alt text-ink hover:bg-[#cbd5e1]',
        'info' => 'bg-izin-soft text-izin hover:bg-[#bae6fd]',
        'danger' => 'border border-alpha/25 bg-alpha-soft text-alpha hover:bg-[#fecdd3]',
        'success' => 'border border-hadir/25 bg-hadir-soft text-hadir hover:bg-[#bef3ab]',
    ];
    $classes = 'press flex items-center justify-between gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold transition-colors disabled:cursor-not-allowed disabled:opacity-60 ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        <span>{{ $label }}</span>
        <x-icon :name="$icon" :size="14" />
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        <span>{{ $label }}</span>
        <x-icon :name="$icon" :size="14" />
    </button>
@endif
