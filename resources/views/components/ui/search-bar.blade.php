@props([
    'placeholder' => 'Cari...',
    'name' => 'q',
])

<div class="flex h-11 items-center gap-2 rounded-xl bg-surface-alt px-4">
    <x-icon name="search" :size="18" class="shrink-0 text-muted" />
    <input
        type="search"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class('w-full border-none bg-transparent text-sm text-ink outline-none placeholder:text-muted') }}
    >
</div>
