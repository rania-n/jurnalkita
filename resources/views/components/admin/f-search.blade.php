@props(['name' => 'cari', 'placeholder' => 'Cari...'])

<label class="flex flex-col gap-1">
    <span class="text-xs font-semibold text-muted-2">Cari</span>
    <span class="flex h-10 w-56 items-center gap-2 rounded-lg border border-surface-alt bg-card px-3">
        <x-icon name="search" :size="16" class="shrink-0 text-muted" />
        <input type="search" name="{{ $name }}" value="{{ request($name) }}" placeholder="{{ $placeholder }}"
            class="w-full border-0 bg-transparent text-sm outline-none placeholder:text-placeholder">
    </span>
</label>
