@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'icon' => null,
    'hint' => null,
    'errorBag' => 'default',
])

@php
    $id = $attributes->get('id', $name);

    // class / hidden / data-* -> wrapper. Sisanya -> <input>.
    $wrapKeys = collect($attributes->getAttributes())
        ->keys()
        ->filter(fn ($k) => $k === 'class' || $k === 'hidden' || str_starts_with($k, 'data-'))
        ->all();
    $wrap = $attributes->only($wrapKeys);
    $input = $attributes->except([...$wrapKeys, 'id']);
@endphp

<div {{ $wrap->class('flex flex-col gap-1.5') }}>
    @if ($label)
        <x-ui.label :for="$id" :required="$attributes->has('required')">{{ $label }}</x-ui.label>
    @endif

    {{-- has-[:disabled] -- kotaknya ikut jadi abu-abu pas input-nya disabled,
         biar keliatan JELAS beda sama field yang lagi bisa diisi (bg-card,
         putih). Dipakai khususnya di halaman Profil: sebelum tombol Edit
         ditekan, field yang BISA diedit ini kelihatan sama abu-abunya kayak
         field info yang emang selalu dikunci (x-ui.field-static tone=muted)
         -- begitu Edit ditekan, field yang beneran bisa diisi berubah putih,
         yang cuma info tetap abu-abu. --}}
    <div class="flex h-[52px] items-center gap-2 rounded-xl border border-surface-alt bg-card px-4 transition-colors focus-within:border-navy has-[:disabled]:bg-surface-alt has-[:readonly]:bg-surface-alt @error($name, $errorBag) !border-alpha @enderror">
        @if ($icon)
            <x-icon :name="$icon" :size="20" class="shrink-0 text-muted-2" />
        @endif

        <input
            type="{{ $type }}"
            @if ($name) name="{{ $name }}" @endif
            id="{{ $id }}"
            {{ $input->class('w-full border-none bg-transparent text-[15px] text-ink outline-none placeholder:text-placeholder disabled:cursor-not-allowed disabled:text-muted-2 read-only:cursor-not-allowed read-only:text-muted-2') }}
        >

        @if($type === 'password')
            <button type="button" data-toggle-password="#{{ $id }}" class="flex shrink-0 items-center text-muted-2 hover:text-ink focus:outline-none transition-colors" tabindex="-1" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        @endif

        {{ $slot }}
    </div>

    @error($name, $errorBag)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @else
        @if ($hint)
            <p class="text-xs text-muted-2">{{ $hint }}</p>
        @endif
    @enderror
</div>
