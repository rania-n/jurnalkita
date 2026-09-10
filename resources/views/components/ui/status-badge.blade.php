@props(['status' => 'menunggu'])

@php
    $key = strtolower(trim($status));
    $map = [
        'menunggu' => ['bg-sakit-soft text-sakit', 'Menunggu'],
        'disetujui' => ['bg-hadir-soft text-hadir', 'Disetujui'],
        'ditolak' => ['bg-alpha-soft text-alpha', 'Ditolak'],
        'hadir' => ['bg-hadir-soft text-hadir', 'Hadir'],
        'izin' => ['bg-izin-soft text-izin', 'Izin'],
        'sakit' => ['bg-sakit-soft text-sakit', 'Sakit'],
        'alpha' => ['bg-alpha-soft text-alpha', 'Alpha'],
        'dispen' => ['bg-dispen-soft text-dispen', 'Dispen'],
        'dispensasi' => ['bg-dispen-soft text-dispen', 'Dispensasi'],
        'pending' => ['bg-sakit-soft text-sakit', 'Menunggu'],
        'terverifikasi' => ['bg-hadir-soft text-hadir', 'Terverifikasi'],
        'revisi' => ['bg-alpha-soft text-alpha', 'Revisi'],
    ];
    [$cls, $label] = $map[$key] ?? ['bg-surface-alt text-muted', ucfirst($key)];
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold {$cls}") }}>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</span>
