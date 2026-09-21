@props(['name', 'label', 'value' => null])

{{-- min-w kecil (bukan 9rem kayak dulu) -- 2 field tanggal (Dari/Sampai)
     bersebelahan gampang meluber ke kanan (horizontal scroll) di HP sempit
     (~320-375px), 9rem x 2 + gap aja udah lebih lebar dari layarnya. --}}
<label class="flex min-w-[6.5rem] flex-1 flex-col gap-1">
    <span class="text-xs font-semibold text-muted-2">{{ $label }}</span>
    <input type="date" name="{{ $name }}" value="{{ $value ?? request()->query($name) }}"
        {{ $attributes }}
        class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
</label>
