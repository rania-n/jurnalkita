@props([
    'label' => null,
    'name' => null,
    'rows' => 3,
])

@php
    $id = $attributes->get('id', $name);

    // class / hidden / data-* -> wrapper. Sisanya -> <textarea>.
    $wrapKeys = collect($attributes->getAttributes())
        ->keys()
        ->filter(fn ($k) => $k === 'class' || $k === 'hidden' || str_starts_with($k, 'data-'))
        ->all();
    $wrap = $attributes->only($wrapKeys);
    $field = $attributes->except([...$wrapKeys, 'id']);
@endphp

<div {{ $wrap->class('flex flex-col gap-1.5') }}>
    @if ($label)
        <x-ui.label :for="$id">{{ $label }}</x-ui.label>
    @endif

    <textarea
        @if ($name) name="{{ $name }}" @endif
        id="{{ $id }}"
        rows="{{ $rows }}"
        {{ $field->class('w-full resize-y rounded-xl border border-surface-alt bg-card px-4 py-3 text-[15px] text-ink outline-none transition-colors placeholder:text-placeholder focus:border-navy') }}
    >{{ $slot }}</textarea>

    @error($name)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @enderror
</div>
