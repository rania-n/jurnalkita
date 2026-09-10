@props([
    'label' => null,
    'name' => null,
])

@php $id = $attributes->get('id', $name); @endphp

<div class="flex flex-col gap-1.5">
    @if ($label)
        <x-ui.label :for="$id">{{ $label }}</x-ui.label>
    @endif

    <div class="relative">
        <select
            @if ($name) name="{{ $name }}" @endif
            id="{{ $id }}"
            {{ $attributes->except('id')->class('h-[52px] w-full cursor-pointer appearance-none rounded-xl border border-surface-alt bg-card px-4 pr-11 text-[15px] text-ink outline-none transition-colors focus:border-navy') }}
        >
            {{ $slot }}
        </select>
        <x-icon name="expand_more" :size="22" class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-muted" />
    </div>

    @error($name)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @enderror
</div>
