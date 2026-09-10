@props([
    'label' => null,
    'icon' => null,
])

{{-- Nilai read-only bergaya seperti input (dipakai di layar detail). --}}
<div class="flex flex-col gap-1.5">
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    <div {{ $attributes->class('flex min-h-[52px] items-center gap-2 rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink') }}>
        @if ($icon)
            <x-icon :name="$icon" :size="20" class="shrink-0 text-muted-2" />
        @endif
        <span class="flex-1">{{ $slot }}</span>
    </div>
</div>
