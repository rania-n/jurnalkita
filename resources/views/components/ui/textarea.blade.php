@props([
    'label' => null,
    'name' => null,
    'rows' => 3,
])

@php $id = $attributes->get('id', $name); @endphp

<div class="flex flex-col gap-1.5">
    @if ($label)
        <x-ui.label :for="$id">{{ $label }}</x-ui.label>
    @endif

    <textarea
        @if ($name) name="{{ $name }}" @endif
        id="{{ $id }}"
        rows="{{ $rows }}"
        {{ $attributes->except('id')->class('w-full resize-none rounded-xl border border-surface-alt bg-card px-4 py-3 text-[15px] text-ink outline-none transition-colors placeholder:text-placeholder focus:border-navy') }}
    >{{ $slot }}</textarea>

    @error($name)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @enderror
</div>
