@props([
    'name',
    'options' => [],   // ['hadir' => 'Hadir', 'sakit' => 'Sakit', ...]
    'value' => null,
    'tones' => [],      // opsional: ['hadir' => 'hadir', 'alpha' => 'alpha', ...]
    'size' => 'md',     // md | sm
    'label' => null,
])

{{--
    Pilihan "tap" (satu dari beberapa). Radio asli + label — TANPA JavaScript.
    Warna terpilih mengikuti $tones (default: navy).

    <x-ui.choice name="status" :options="['hadir'=>'Hadir','izin'=>'Izin']"
        :tones="['hadir'=>'hadir','izin'=>'izin']" :value="old('status','hadir')" />
--}}

@php
    $toneClass = [
        'navy' => 'peer-checked:border-navy peer-checked:bg-navy peer-checked:text-card',
        'hadir' => 'peer-checked:border-hadir peer-checked:bg-hadir-soft peer-checked:text-hadir',
        'sakit' => 'peer-checked:border-sakit peer-checked:bg-sakit-soft peer-checked:text-sakit',
        'izin' => 'peer-checked:border-izin peer-checked:bg-izin-soft peer-checked:text-izin',
        'alpha' => 'peer-checked:border-alpha peer-checked:bg-alpha-soft peer-checked:text-alpha',
        'dispen' => 'peer-checked:border-dispen peer-checked:bg-dispen-soft peer-checked:text-dispen',
    ];
    $pad = $size === 'sm' ? 'px-2.5 py-1.5 text-xs' : 'px-3.5 py-2.5 text-[13px]';
@endphp

<div {{ $attributes->only('class')->class('flex flex-col gap-1.5') }}>
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    <div class="flex flex-wrap gap-1.5" role="radiogroup" @if ($label) aria-label="{{ $label }}" @endif>
        @foreach ($options as $optValue => $optLabel)
            <label class="grow basis-20 cursor-pointer select-none">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optValue }}"
                    @checked((string) $value === (string) $optValue)
                    class="peer sr-only"
                >
                <span class="flex w-full items-center justify-center rounded-lg border border-surface-alt bg-card text-center font-semibold text-muted-2 transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-navy/40 {{ $pad }} {{ $toneClass[$tones[$optValue] ?? 'navy'] }}">
                    {{ $optLabel }}
                </span>
            </label>
        @endforeach
    </div>
</div>
