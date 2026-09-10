@props([
    'icon',
    'variant' => 'neutral',
    'href' => null,
    'type' => 'button',
    'label' => null,
])

@php
    $variants = [
        'neutral' => 'bg-surface-alt text-ink hover:bg-[#cbd5e1]',
        'info' => 'bg-izin-soft text-izin hover:bg-[#bae6fd]',
        'danger' => 'bg-alpha-soft text-alpha hover:bg-[#fecdd3]',
    ];
    $classes = 'flex h-7 w-7 items-center justify-center rounded-lg transition-colors ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }} @if ($label) aria-label="{{ $label }}" @endif>
        <x-icon :name="$icon" :size="14" />
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }} @if ($label) aria-label="{{ $label }}" @endif>
        <x-icon :name="$icon" :size="14" />
    </button>
@endif
