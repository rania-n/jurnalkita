@props([
    'label' => null,
    'name' => 'lampiran',
    'title' => 'Lampirkan Foto / File',
    'hint' => null,
    'accept' => 'image/*',
    'icon' => 'add_photo_alternate',
    'capture' => null,   // 'environment' (kamera belakang) | 'user' (depan) | null
    'required' => false,
    'errorBag' => 'default',
])

@php $id = $attributes->get('id', $name); @endphp

<div {{ $attributes->only('class')->class('flex flex-col gap-1.5') }}>
    @if ($label)
        <x-ui.label :for="$id">{{ $label }}</x-ui.label>
    @endif

    <label
        for="{{ $id }}"
        @class([
            'flex min-h-40 w-full cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed bg-card px-5 py-6 text-center transition-colors hover:border-navy',
            'border-[#B8C4D9]' => ! $errors->has($name, $errorBag),
            '!border-alpha' => $errors->has($name, $errorBag),
        ])
    >
        <x-icon :name="$icon" :size="28" class="text-navy" />
        <span class="text-xs font-semibold text-navy">{{ $title }}</span>
        @if ($hint)
            <span class="text-[11px] text-muted-2">{{ $hint }}</span>
        @endif
        {{-- sr-only (BUKAN hidden/display:none) -- biar tetap ikut validasi
             wajib-isi bawaan browser. Klik label tetap buka file-picker
             seperti biasa, "capture" yang bikin browser mobile langsung
             mbuka kamera (bukan menu pilih file/galeri dulu). --}}
        <input
            type="file"
            name="{{ $name }}"
            id="{{ $id }}"
            accept="{{ $accept }}"
            @if ($capture) capture="{{ $capture }}" @endif
            @if ($required) required @endif
            class="sr-only"
            data-upload-input
        >
    </label>

    @error($name, $errorBag)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @enderror
</div>
