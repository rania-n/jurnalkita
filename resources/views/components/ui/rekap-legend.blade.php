{{-- Penjelasan urutan warna badge di <x-ui.rekap-chip-card> -- cukup sekali di
     atas daftar, bukan diulang di tiap kartu. Urutannya HARUS sama persis
     kayak badge di x-ui.rekap-chip-card & kolom tabel (keduanya ambil dari
     RekapKehadiran::ringkasan()) -- kalau beda, warna buletnya jadi nggak
     nyambung sama keterangannya. --}}
<div {{ $attributes->class('mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-muted-2') }}>
    @foreach (\App\Support\RekapKehadiran::ringkasan() as $nada)
        <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-{{ $nada[1] }}"></span>{{ $nada[0] }}</span>
    @endforeach
</div>
