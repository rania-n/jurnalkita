@php
    $perluApproval = \App\Models\Dispensasi::where('status_piket', 'approved')
        ->where('status_waka', 'pending')->count();
@endphp

<x-layouts.app title="Beranda Waka" width="wide">
    <x-page-header title="Beranda Waka Kesiswaan" subtitle="Persetujuan dispensasi tahap 2" />

    <a href="{{ route('dispensasi.index') }}" class="press flex max-w-md items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
            <x-icon name="approval" :size="24" />
        </span>
        <div class="flex-1">
            <p class="text-sm font-bold text-ink">Antrean Dispensasi</p>
            <p class="text-xs text-muted">{{ $perluApproval }} pengajuan menunggu persetujuan Anda</p>
        </div>
        <x-icon name="chevron_right" :size="20" class="text-muted" />
    </a>
</x-layouts.app>
