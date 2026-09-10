@props([
    'href' => '#',
    'icon' => 'add',
])

{{-- Batang "Tambah ..." lebar penuh (navy, teks kiri + ikon + kanan). --}}
<a
    href="{{ $href }}"
    {{ $attributes->class('press flex items-center justify-between gap-2 rounded-xl bg-navy px-4 py-3 text-sm font-bold text-card transition-colors hover:bg-navy-hover') }}
>
    <span>{{ $slot }}</span>
    <x-icon :name="$icon" :size="20" />
</a>
