{{-- Penjelasan urutan warna badge di <x-ui.rekap-chip-card> -- cukup sekali di
     atas daftar, bukan diulang di tiap kartu. --}}
<div {{ $attributes->class('mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-muted-2') }}>
    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-hadir"></span>Hadir</span>
    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-sakit"></span>Sakit</span>
    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-izin"></span>Izin</span>
    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-alpha"></span>Alpha</span>
    <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-dispen"></span>Dispensasi</span>
</div>
