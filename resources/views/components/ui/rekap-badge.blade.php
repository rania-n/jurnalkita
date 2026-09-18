@props(['tone'])

@php
    $cls = match ($tone) {
        'hadir' => 'bg-hadir-soft text-hadir',
        'sakit' => 'bg-sakit-soft text-sakit',
        'izin' => 'bg-izin-soft text-izin',
        'alpha' => 'bg-alpha-soft text-alpha',
        'dispensasi' => 'bg-dispen-soft text-dispen',
        'terlambat' => 'bg-surface-alt text-navy',
        default => 'bg-surface-alt text-ink',
    };
@endphp

{{--
    Badge bulat angka rekap kehadiran satu warna -- SATU sumber styling yang
    dipakai bareng di tabel desktop (rekap/siswa, wali-kelas/rekap,
    sekretaris/rekap) DAN kartu mobile (x-ui.rekap-chip-card), biar
    "bulet-bulet" lucu yang tadinya cuma ada di HP konsisten muncul juga di
    desktop -- bukan cuma angka polos.
--}}
<span {{ $attributes->class("inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full px-1.5 text-[11px] font-bold {$cls}") }}>{{ $slot }}</span>
