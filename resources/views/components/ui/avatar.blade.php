@props([
    'label' => '',
    'gender' => null,   // 'L' / 'P' (male / female) untuk warna
])

@php
    $tint = match (strtoupper((string) $gender)) {
        'L', 'M' => 'bg-izin-soft text-izin',
        'P', 'F' => 'bg-[#FCE7F3] text-[#BE185D]',
        default => 'bg-surface-alt text-muted',
    };
@endphp

<span {{ $attributes->class("flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold {$tint}") }}>
    {{ $label }}
</span>
