@props([
    'placeholder' => 'Cari...',
    'name' => 'q',
])

{{-- Putih + border (bukan abu-abu polos) biar kelihatan jelas ini kolom input,
     bukan sekadar dekorasi -- dan h-10 (bukan h-11) biar nggak kebesaran. --}}
<div class="flex h-10 items-center gap-2 rounded-lg border border-surface-alt bg-card px-3">
    <x-icon name="search" :size="16" class="shrink-0 text-muted" />
    <input
        type="search"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class('w-full border-none bg-transparent text-sm text-ink outline-none placeholder:text-muted') }}
    >
</div>
