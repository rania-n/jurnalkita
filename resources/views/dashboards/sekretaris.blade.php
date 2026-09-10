@php
    $siswa = auth()->user()->siswa;
    $kelas = $siswa?->kelas;
@endphp

<x-layouts.app title="Beranda Pengurus Kelas" width="wide">
    <x-page-header title="Beranda" :subtitle="$kelas?->nama ?? 'Pengurus Kelas'" />

    <div class="flex flex-col gap-3">
        <a href="{{ route('dispensasi.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="fact_check" :size="24" />
            </span>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">Dispensasi Kelas</p>
                <p class="text-xs text-muted">Ajukan &amp; pantau status dispensasi siswa</p>
            </div>
            <x-icon name="chevron_right" :size="20" class="text-muted" />
        </a>
    </div>

    <x-alert type="info" class="mt-6">
        Verifikasi jurnal guru akan tampil di sini setelah modul verifikasi selesai.
    </x-alert>
</x-layouts.app>
