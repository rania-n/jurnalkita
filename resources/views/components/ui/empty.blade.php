@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'desc' => null,
])

<div {{ $attributes->class('flex flex-col items-center gap-2 rounded-2xl border border-dashed border-surface-alt bg-card px-6 py-10 text-center') }}>
    <x-icon :name="$icon" :size="32" class="text-muted-2" />
    <p class="text-sm font-semibold text-ink">{{ $title }}</p>
    @if ($desc)
        <p class="text-xs text-muted-2">{{ $desc }}</p>
    @endif
    {{ $slot }}
</div>
