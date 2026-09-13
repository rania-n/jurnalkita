@props(['name', 'label', 'value' => null])

<label class="flex min-w-[9rem] flex-1 flex-col gap-1 sm:max-w-[11rem]">
    <span class="text-xs font-semibold text-muted-2">{{ $label }}</span>
    <input type="date" name="{{ $name }}" value="{{ $value ?? request()->query($name) }}"
        class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
</label>
