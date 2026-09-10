@props([
    'label',
    'icon',
    'variant' => 'neutral',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'neutral' => 'bg-surface-alt text-ink hover:bg-[#cbd5e1]',
        'info' => 'bg-izin-soft text-izin hover:bg-[#bae6fd]',
        'danger' => 'bg-alpha-soft text-alpha hover:bg-[#fecdd3]',
        'success' => 'bg-hadir-soft text-hadir hover:bg-[#bef3ab]',
    ];
    $classes = 'press flex items-center justify-between gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold transition-colors ' . ($variants[$variant] ?? $variants['neutral']);
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
