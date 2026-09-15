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
        <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-hadir-soft px-1 text-[11px] font-bold text-hadir">{{ $hadir }}</span>
        <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-sakit-soft px-1 text-[11px] font-bold text-sakit">{{ $sakit }}</span>
        <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-izin-soft px-1 text-[11px] font-bold text-izin">{{ $izin }}</span>
        <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-alpha-soft px-1 text-[11px] font-bold text-alpha">{{ $alpha }}</span>
        <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-dispen-soft px-1 text-[11px] font-bold text-dispen">{{ $dispensasi }}</span>
        @if (! is_null($terlambat))
            <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-surface-alt px-1 text-[11px] font-bold text-navy">{{ $terlambat }}</span>
        @endif
    </div>
</div>
