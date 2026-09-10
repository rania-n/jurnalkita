@props([
    'name',
    'size' => 20,
    'fill' => false,
    'weight' => 400,
])

{{-- Ikon Material Symbols (Rounded). Nama ikon: https://fonts.google.com/icons --}}
<span
    {{ $attributes->class(['material-symbols-rounded select-none leading-none']) }}
    style="font-size: {{ $size }}px; font-variation-settings: 'FILL' {{ $fill ? 1 : 0 }}, 'wght' {{ $weight }}, 'GRAD' 0, 'opsz' {{ $size }};"
    aria-hidden="true"
>{{ $name }}</span>
