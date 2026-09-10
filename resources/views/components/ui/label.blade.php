@props(['for' => null])

<label @if ($for) for="{{ $for }}" @endif {{ $attributes->class('text-sm font-semibold text-ink') }}>
    {{ $slot }}
</label>
