@props([
    'label' => null,
    'name',
    'options' => [],
    'value' => null,
])

{{--
    Segmented control (pilih satu). JS di resources/js/app.js.
    <x-ui.segmented name="status" :options="['hadir' => 'Hadir', 'izin' => 'Izin']" value="hadir" />
--}}
<div class="flex flex-col gap-1.5">
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    <div
        data-segmented
        class="flex w-full items-stretch gap-1 rounded-xl bg-card p-1 shadow-[var(--shadow-card)]"
        role="group"
        @if ($label) aria-label="{{ $label }}" @endif
    >
        @foreach ($options as $optValue => $optLabel)
            <button
                type="button"
                data-segment
                value="{{ $optValue }}"
                aria-pressed="{{ $optValue == $value ? 'true' : 'false' }}"
                class="flex-1 whitespace-nowrap rounded-lg px-2 py-2.5 text-center text-[13px] font-semibold text-muted-2 transition-colors aria-pressed:bg-navy aria-pressed:text-card"
            >{{ $optLabel }}</button>
        @endforeach
    </div>

    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-segment-value>
</div>
