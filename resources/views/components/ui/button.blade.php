@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconAfter' => null,
    'block' => false,
])

@php
    $base = 'press inline-flex h-12 items-center justify-center gap-2 rounded-xl px-5 text-base font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-60';
    $variants = [
        'primary' => 'bg-navy text-card hover:bg-navy-hover',
        'secondary' => 'bg-surface-alt text-ink hover:bg-[#cbd5e1]',
        'success' => 'border border-hadir/25 bg-hadir-soft text-hadir hover:bg-[#bef3ab]',
        'ghost' => 'text-navy hover:bg-surface',
        'danger' => 'border border-alpha/25 bg-alpha-soft text-alpha hover:bg-[#fecdd3]',
    ];
    $classes = trim($base . ' ' . ($variants[$variant] ?? $variants['primary']) . ($block ? ' w-full' : ''));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" :size="20" /> @endif
        {{ $slot }}
        @if ($iconAfter) <x-icon :name="$iconAfter" :size="20" /> @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-icon :name="$icon" :size="20" /> @endif
        {{ $slot }}
        @if ($iconAfter) <x-icon :name="$iconAfter" :size="20" /> @endif
    </button>
@endif
