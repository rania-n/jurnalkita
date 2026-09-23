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
    'all' => null,        // teks pas kosong, mis. "Semua Kelas" -- pasang ini buat mode FILTER (bukan form input)
    'compact' => false,   // kotak lebih kecil (h-10, ala x-admin.f-select) -- dipasang otomatis kalau $all diisi
    'autoSubmit' => false, // langsung submit form begitu milih -- buat filter tabel (ala f-select onchange)
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
    // Mode filter (dipasang $all): value awal dari query string langsung
    // (bukan old(), field ini nggak nyambung ke validasi form biasa),
    // dan compact+autoSubmit otomatis nyala kalau belum eksplisit dimatiin.
    $modeFilter = $all !== null;
    $compact = $compact || $modeFilter;
    $autoSubmit = $autoSubmit || $modeFilter;
    $terpilihId = $modeFilter ? request()->query($name) : old($name, $value);
    $terpilih = $daftar->firstWhere('id', is_numeric($terpilihId) ? (int) $terpilihId : $terpilihId);
    // Dihitung DI SINI (bukan langsung nulis "@error(...)" sebagai key string
    // di dalam @class([...]) di bawah) -- directive Blade nggak ke-compile
    // kalau ditulis di dalam string literal gitu, malah numpuk jadi teks
    // "@error(...)" mentah yang nempel sebagai class HTML (gara-gara ada
    // spasi di dalemnya, "!border-alpha" di tengah teks itu tetap ke-parse
    // browser sebagai 1 class token yang valid & SELALU ke-apply, nggak
    // peduli beneran ada errornya apa nggak -- makanya border-nya selalu
    // merah). Filter (mode $all) nggak pernah nampilin error validasi -- ini
    // query-string biasa, bukan field form yang divalidasi, jadi border
    // merah di sini cuma bakal nyasar dari error form LAIN yang kebetulan
    // pakai nama field sama (mis. "kelas_id" di form Tambah Jadwal) dan
    // nyangkut di session.
    $adaError = ! $modeFilter && $errors->getBag($errorBag)->has($name);
@endphp

{{--
    Dropdown biasa (<select>) buat daftar panjang (mis. 47 mapel, 131 guru,
    72 kelas) capek discroll -- ini versi "ketik buat cari" generik, pola
    SAMA persis kayak x-ui.cari-siswa (JS-nya sengaja dipisah sendiri di
    initCariPilihan() biar nggak ganggu cari-siswa yang udah jalan), bedanya
    di sini bisa dipasang di field APA AJA (kelas/guru/mapel/dll).

    2 mode:
    - Form input (default) -- kotak gede ala x-ui.input, submit manual.
    - Filter tabel -- pasang prop "all" (teks placeholder pas kosong, mis.
      "Semua Kelas"), otomatis jadi kotak kompak ala x-admin.f-select +
      langsung submit form begitu milih/hapus (pola sama kayak onchange
      submit di f-select).

    Opsional: kasih tambahLabel+tambahUrl buat nampilin link "+ Tambah X
    Baru" nempel di bagian bawah hasil pencarian -- biar kalau pilihannya
    belum ada, nggak perlu keluar dulu cari menu Tambah Mapel manual.
--}}
<div
    {{-- "id" SENGAJA nggak diteruskan ke wrapper -- dipakainya khusus buat
         $id (label "for" + kotak teks) di bawah, bukan div ini. Kalau
         diteruskan juga ke wrapper, jadi 2 elemen beda pegang id yang sama
         persis (invalid HTML, bikin getElementById nebak-nebak). --}}
    {{ $attributes->except('id')->class(['flex flex-col gap-1', 'min-w-[9rem] flex-1' => $compact, 'gap-1.5' => ! $compact]) }}
    data-cari-pilihan
    data-list='@json($daftar)'
    @if ($autoSubmit) data-auto-submit @endif
>
    @if ($label && $compact)
        <span class="text-xs font-semibold text-muted-2">{{ $label }}</span>
    @elseif ($label)
        <x-ui.label :for="$id" :required="$required">{{ $label }}</x-ui.label>
    @endif

    <div class="relative">
        <div @class([
            'flex items-center gap-2 border border-surface-alt bg-card transition-colors focus-within:border-navy',
            '!border-alpha' => $adaError,
            'h-10 rounded-lg px-3' => $compact,
            'h-[52px] rounded-xl px-4' => ! $compact,
        ])>
            <x-icon name="search" :size="$compact ? 16 : 20" class="shrink-0 text-muted-2" />
            <input
                type="text"
                id="{{ $id }}"
                autocomplete="off"
                placeholder="{{ $modeFilter ? ($all ?: $placeholder) : $placeholder }}"
                value="{{ $terpilih['nama'] ?? '' }}"
                data-cari-pilihan-input
                @class([
                    'w-full border-none bg-transparent text-ink outline-none',
                    // Kompak (filter bar) pakai warna placeholder yang sama
                    // kayak kotak filter tetangganya (search-bar/f-select) --
                    // text-placeholder itu punya kotak form gede (x-ui.input).
                    'text-sm font-medium placeholder:text-muted' => $compact,
                    'text-[15px] placeholder:text-placeholder' => ! $compact,
                ])
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
