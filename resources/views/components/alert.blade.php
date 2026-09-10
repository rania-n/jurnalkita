@props([
    'type' => 'success',
    'bleed' => false,
])

@php
    $styles = [
        'success' => 'bg-hadir-soft text-hadir',
        'error' => 'bg-alpha-soft text-alpha',
        'warning' => 'bg-sakit-soft text-sakit',
        'info' => 'bg-izin-soft text-izin',
    ][$type] ?? 'bg-surface-alt text-ink';

    $icon = ['success' => 'check_circle', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'][$type] ?? 'info';

    // bleed = banner full-width dengan garis aksen kiri (dipakai di layar registrasi)
    $shape = $bleed
        ? '-mx-6 border-l-4 border-current/60 px-6 py-3 lg:-mx-10 lg:px-10'
        : 'rounded-xl px-4 py-3';
@endphp

<div {{ $attributes->class(["flex items-start gap-2.5 text-[13px] font-medium leading-snug {$styles} {$shape}"]) }} role="alert">
    <x-icon :name="$icon" :size="18" fill class="mt-px shrink-0" />
    <span>{{ $slot }}</span>
</div>
