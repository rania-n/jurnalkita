@props([
    'label',
    'value' => 0,
    'tone' => 'hadir',   // hadir | sakit | izin | alpha | dispen
])

@php
    $tones = [
        'hadir' => 'bg-hadir-soft text-hadir',
        'sakit' => 'bg-sakit-soft text-sakit',
        'izin' => 'bg-izin-soft text-izin',
        'alpha' => 'bg-alpha-soft text-alpha',
        'dispen' => 'bg-dispen-soft text-dispen',
    ];
    $cls = $tones[$tone] ?? $tones['hadir'];
@endphp

<div {{ $attributes->class("flex flex-1 flex-col items-center gap-0.5 rounded-lg px-1.5 py-2 {$cls}") }}>
    <span class="text-[10px] font-semibold text-muted">{{ $label }}</span>
    <span class="text-base font-bold">{{ $value }}</span>
</div>
