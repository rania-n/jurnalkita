@props([
    'nama',
    'meta' => null,
    'hadir' => 0,
    'sakit' => 0,
    'izin' => 0,
    'alpha' => 0,
    'dispensasi' => 0,
    'terlambat' => null,
    'sorot' => false,
])

{{--
    Kartu ringkas rekap kehadiran satu siswa -- dipakai di HP sebagai pengganti
    tabel (dulu 6-9 kolom ditumpuk jadi satu-satu, kepanjangan). Angkanya
    ditaruh sebagai badge bulat berwarna sejajar TANPA label di tiap badge --
    urutan warnanya dijelaskan sekali lewat <x-ui.rekap-legend> di atas daftar.
--}}
<div {{ $attributes->class(['flex items-center justify-between gap-3 rounded-xl border border-surface-alt bg-card p-3', 'bg-alpha-soft/30' => $sorot]) }}>
    <div class="min-w-0">
        <p class="truncate text-sm font-semibold text-ink">{{ $nama }}</p>
        @if ($meta)
            <p class="truncate text-xs text-muted-2">{{ $meta }}</p>
        @endif
    </div>
    <div class="flex shrink-0 flex-wrap justify-end gap-1">
        <x-ui.rekap-badge tone="hadir">{{ $hadir }}</x-ui.rekap-badge>
        <x-ui.rekap-badge tone="sakit">{{ $sakit }}</x-ui.rekap-badge>
        <x-ui.rekap-badge tone="izin">{{ $izin }}</x-ui.rekap-badge>
        <x-ui.rekap-badge tone="alpha">{{ $alpha }}</x-ui.rekap-badge>
        <x-ui.rekap-badge tone="dispensasi">{{ $dispensasi }}</x-ui.rekap-badge>
        @if (! is_null($terlambat))
            <x-ui.rekap-badge tone="terlambat">{{ $terlambat }}</x-ui.rekap-badge>
        @endif
    </div>
</div>
