@props(['name' => null])

{{-- Dropdown filter ringkas (baris filter di atas daftar). --}}
<div class="relative min-w-0 flex-1">
    <select
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->class('h-10 w-full cursor-pointer appearance-none rounded-lg border border-surface-alt bg-card pl-3 pr-8 text-[13px] font-semibold text-muted outline-none focus:border-navy') }}
    >
        {{ $slot }}
    </select>
    <x-icon name="expand_more" :size="18" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-muted" />
</div>
