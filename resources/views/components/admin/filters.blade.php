@props(['action', 'hideButtons' => false, 'ignore' => []])

@php
    // 'hari'/'tab'/'set'/'role' itu pemilih TAMPILAN (tab bar), bukan filter
    // pencarian -- jangan ikut dianggap "ada filter aktif" walau nilainya
    // bukan default (dulu 'role' kelewat di Manajemen Akun, jadi tombol
    // Reset nongol cuma gara-gara klik tab role, bukan filter beneran).
    // SENGAJA bukan 'status' -- itu tab di Riwayat Jurnal, tapi filter
    // BENERAN di Data Siswa, jadi kalau perlu diabaikan pakai prop $ignore
    // per halaman (lihat guru/jurnal/index.blade.php).
    $hasFilter = collect(request()->except(array_merge(['page', 'hari', 'tab', 'set', 'role'], (array) $ignore)))
        ->filter(fn ($v) => $v !== '' && $v !== null)->isNotEmpty();
@endphp

<form method="GET" action="{{ $action }}" class="mb-4 flex flex-wrap items-end gap-3">
    {{ $slot }}

    @if (!$hideButtons && $hasFilter)
        <a href="{{ $action }}" class="flex h-10 shrink-0 items-center gap-1.5 rounded-lg border border-surface-alt bg-card px-3 text-sm font-semibold text-muted hover:border-alpha hover:text-alpha">
            <x-icon name="close" :size="16" /> Reset
        </a>
    @endif
</form>
