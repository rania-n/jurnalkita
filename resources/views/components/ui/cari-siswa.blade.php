@props([
    'label' => 'Siswa',
    'name' => 'siswa_id',
    'siswas' => [],
    'errorBag' => 'default',
    'required' => false,
])

@php
    // "-cari" SENGAJA ditambahin -- biar id kotak teks ini nggak sama persis
    // kayak $name (lihat catatan lebih detail di cari-pilihan.blade.php,
    // komponen kembarannya -- form.elements[name] bisa bentrok jadi
    // RadioNodeList kalau ada elemen lain yang id-nya sama kayak name itu).
    $id = $attributes->get('id', $name.'-cari');
    $terpilihId = old($name);
    $terpilih = collect($siswas)->firstWhere('id', (int) $terpilihId);
@endphp

{{-- Cari langsung ketik nama/NIS -- SENGAJA nggak lagi harus pilih kelas
     dulu (dulu 1 <select> raksasa isinya 2500+ siswa dikelompokkan per
     kelas, capek nyarinya). Daftar siswa di-embed sekali sebagai JSON,
     difilter di klien pas ngetik -- lihat initCariSiswa() di app.js. --}}
<div {{ $attributes->class('flex flex-col gap-1.5') }} data-cari-siswa data-list='@json(collect($siswas)->values())'>
    @if ($label)
        <x-ui.label :for="$id" :required="$required">{{ $label }}</x-ui.label>
    @endif

    <div class="relative">
        <div class="flex h-[52px] items-center gap-2 rounded-xl border border-surface-alt bg-card px-4 transition-colors focus-within:border-navy @error($name, $errorBag) !border-alpha @enderror">
            <x-icon name="search" :size="20" class="shrink-0 text-muted-2" />
            <input
                type="text"
                id="{{ $id }}"
                autocomplete="off"
                placeholder="Ketik nama atau NIS siswa..."
                value="{{ $terpilih ? $terpilih['nama'].' · '.$terpilih['nis'] : '' }}"
                data-cari-siswa-input
                class="w-full border-none bg-transparent text-[15px] text-ink outline-none placeholder:text-placeholder"
            >
            <button
                type="button"
                data-cari-siswa-clear
                @if (! $terpilih) hidden @endif
                class="flex shrink-0 items-center text-muted-2 hover:text-alpha"
                aria-label="Ganti siswa"
            >
                <x-icon name="close" :size="18" />
            </button>
        </div>

        <input type="hidden" name="{{ $name }}" value="{{ $terpilihId }}" data-cari-siswa-value>

        <div
            data-cari-siswa-hasil
            hidden
            class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-surface-alt bg-card py-1 shadow-lg"
        ></div>
    </div>

    @error($name, $errorBag)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @endError
</div>
