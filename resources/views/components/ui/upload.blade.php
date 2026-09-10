@props([
    'label' => null,
    'name' => 'lampiran',
    'title' => 'Lampirkan Foto / File',
    'hint' => null,
    'accept' => 'image/*',
    'icon' => 'add_photo_alternate',
])

@php $id = $attributes->get('id', $name); @endphp

<div class="flex flex-col gap-1.5">
    @if ($label)
        <x-ui.label :for="$id">{{ $label }}</x-ui.label>
    @endif

    <label
        for="{{ $id }}"
        class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-[#B8C4D9] bg-card px-5 py-6 text-center transition-colors hover:border-navy"
    >
        <x-icon :name="$icon" :size="28" class="text-navy" />
        <span class="text-xs font-semibold text-navy">{{ $title }}</span>
        @if ($hint)
            <span class="text-[11px] text-muted-2">{{ $hint }}</span>
        @endif
        <input type="file" name="{{ $name }}" id="{{ $id }}" accept="{{ $accept }}" class="hidden" data-upload-input>
    </label>
</div>
