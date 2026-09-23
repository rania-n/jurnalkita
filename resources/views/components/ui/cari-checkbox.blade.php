@props([
    'label' => null,
    'name',      // dipasang "[]" sendiri di tiap checkbox -- kirim tanpa "[]", mis. "mapel_tambahan"
    'options' => [],   // Collection/array of ['id' => ..., 'nama' => ...]
    'hint' => null,
])

@php
    $id = $attributes->get('id', $name.'-cari');
    $daftar = collect($options)->values();
@endphp

{{--
    Multi-pilih yang bisa dicari -- buat daftar yang kepanjangan kalau musti
    scroll manual nyari satu-satu (mis. 47 mapel) sambil tahan Ctrl/Cmd di
    <select multiple> biasa. Bedanya sama x-ui.cari-pilihan: di sini semua
    checkbox tetap kelihatan (nggak ilang), cuma DISARING pas ngetik -- karena
    yang mau dipilih beneran bisa lebih dari satu, bukan 1 nilai final kayak
    cari-pilihan.
--}}
<div {{ $attributes->except('id')->class('flex flex-col gap-1.5') }} data-cari-checkbox>
    @if ($label)
        <x-ui.label :for="$id">{{ $label }}</x-ui.label>
    @endif

    <div class="flex h-10 items-center gap-2 rounded-lg border border-surface-alt bg-card px-3 transition-colors focus-within:border-navy">
        <x-icon name="search" :size="16" class="shrink-0 text-muted" />
        <input
            type="text"
            id="{{ $id }}"
            autocomplete="off"
            placeholder="Cari..."
            data-cari-checkbox-input
            class="w-full border-none bg-transparent text-sm text-ink outline-none placeholder:text-muted"
        >
    </div>

    <div class="max-h-48 overflow-y-auto rounded-xl border border-surface-alt bg-card p-1.5">
        @foreach ($daftar as $opt)
            <label data-cari-checkbox-row data-nama="{{ strtolower($opt['nama']) }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-surface-alt">
                <input type="checkbox" name="{{ $name }}[]" value="{{ $opt['id'] }}" class="h-4 w-4 shrink-0 rounded border-surface-alt text-navy focus:ring-navy">
                <span class="text-sm text-ink">{{ $opt['nama'] }}</span>
            </label>
        @endforeach
        <p data-cari-checkbox-kosong hidden class="px-2 py-1.5 text-sm text-muted-2">Tidak ada yang cocok.</p>
    </div>

    @if ($hint)
        <span class="text-xs text-muted-2">{{ $hint }}</span>
    @endif
</div>
