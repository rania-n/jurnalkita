@props([
    'label' => null,
    'name',
    'options' => [],   // Collection/array of ['id' => ..., 'nama' => ...]
    'value' => null,   // id yang udah kepilih (mis. old($name) atau nilai lama pas Ubah)
    'placeholder' => 'Ketik untuk cari...',
    'tambahLabel' => null,  // mis. "+ Tambah Mata Pelajaran Baru" -- kosongin kalau nggak perlu
    'tambahUrl' => null,
    'errorBag' => 'default',
    'required' => false,
])

@php
    // "-cari" SENGAJA ditambahin -- kalau id-nya persis sama kayak $name,
    // form.elements[name] bentrok sama id ini pas dicari via bracket notation
    // (HTML spec: named lookup itu cocokin BERDASARKAN name ATAUPUN id),
    // hasilnya jadi RadioNodeList isi 2 elemen (bukan 1 elemen hidden-nya
    // doang) dan initModals() (fitur "Ubah") jadi salah kira ini grup radio,
    // nyoba nyetel .checked yang nggak ngefek buat input hidden -- akibatnya
    // field.value nggak pernah ke-isi pas mode Ubah dibuka.
    $id = $attributes->get('id', $name.'-cari');
    $daftar = collect($options)->values();
    $terpilihId = old($name, $value);
    $terpilih = $daftar->firstWhere('id', is_numeric($terpilihId) ? (int) $terpilihId : $terpilihId);
@endphp

{{--
    Dropdown biasa (<select>) buat daftar panjang (mis. 47 mapel, 131 guru)
    capek discroll -- ini versi "ketik buat cari" generik, pola SAMA persis
    kayak x-ui.cari-siswa (JS-nya sengaja dipisah sendiri di initCariPilihan()
    biar nggak ganggu cari-siswa yang udah jalan), bedanya di sini bisa
    dipasang di field APA AJA (kelas/guru/mapel/dll), bukan cuma siswa.

    Opsional: kasih tombolLabel+tambahUrl buat nampilin link "+ Tambah X
    Baru" nempel di bagian bawah hasil pencarian -- biar kalau pilihannya
    belum ada, admin nggak perlu keluar dulu cari menu Tambah Mapel manual.
--}}
<div {{ $attributes->class('flex flex-col gap-1.5') }} data-cari-pilihan data-list='@json($daftar)'>
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
                placeholder="{{ $placeholder }}"
                value="{{ $terpilih['nama'] ?? '' }}"
                data-cari-pilihan-input
                class="w-full border-none bg-transparent text-[15px] text-ink outline-none placeholder:text-placeholder"
            >
            <button
                type="button"
                data-cari-pilihan-clear
                @if (! $terpilih) hidden @endif
                class="flex shrink-0 items-center text-muted-2 hover:text-alpha"
                aria-label="Ganti pilihan"
            >
                <x-icon name="close" :size="18" />
            </button>
        </div>

        <input type="hidden" name="{{ $name }}" value="{{ $terpilihId }}" data-cari-pilihan-value>

        <div
            data-cari-pilihan-hasil
            hidden
            class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-surface-alt bg-card py-1 shadow-lg"
        >
            <div data-cari-pilihan-daftar></div>

            @if ($tambahLabel && $tambahUrl)
                <a href="{{ $tambahUrl }}" target="_blank" rel="noopener"
                   class="sticky bottom-0 flex items-center gap-1.5 border-t border-surface-alt bg-card px-3.5 py-2.5 text-sm font-semibold text-navy hover:bg-surface-alt">
                    <x-icon name="add_circle" :size="16" />
                    {{ $tambahLabel }}
                </a>
            @endif
        </div>
    </div>

    @error($name, $errorBag)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @endError
</div>
