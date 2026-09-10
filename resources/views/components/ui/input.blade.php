@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'icon' => null,
    'hint' => null,
])

@php $id = $attributes->get('id', $name); @endphp

<div class="flex flex-col gap-1.5">
    @if ($label)
        <x-ui.label :for="$id">{{ $label }}</x-ui.label>
    @endif

    <div class="flex h-[52px] items-center gap-2 rounded-xl border border-surface-alt bg-card px-4 transition-colors focus-within:border-navy @error($name) !border-alpha @enderror">
        @if ($icon)
            <x-icon :name="$icon" :size="20" class="shrink-0 text-muted-2" />
        @endif

        <input
            type="{{ $type }}"
            @if ($name) name="{{ $name }}" @endif
            id="{{ $id }}"
            {{ $attributes->except('id')->class('w-full border-none bg-transparent text-[15px] text-ink outline-none placeholder:text-placeholder') }}
        >

        {{ $slot }}
    </div>

    @error($name)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @else
        @if ($hint)
            <p class="text-xs text-muted-2">{{ $hint }}</p>
        @endif
    @enderror
</div>
