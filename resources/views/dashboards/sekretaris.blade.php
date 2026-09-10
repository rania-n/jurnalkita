@php
    $user = auth()->user();
    $kelas = $user->isSekretaris() ? $user->kelasSekretaris() : null;
    $pending = $kelas
        ? \App\Models\Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
            ->where('status_verifikasi', 'pending')->count()
        : 0;
@endphp

<x-layouts.app title="Beranda Pengurus Kelas" width="wide">
    <x-page-header title="Beranda" :subtitle="$kelas?->nama ?? 'Pengurus Kelas'" />

    @unless ($kelas)
        <x-alert type="warning">Akun ini bukan pengurus kelas atau belum terhubung ke kelas. Hubungi admin.</x-alert>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <a href="{{ route('sekretaris.jurnal.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="fact_check" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Verifikasi Jurnal</p>
                    <p class="text-xs text-muted">
                        {{ $pending ? "$pending jurnal perlu diperiksa" : 'Semua jurnal sudah diperiksa' }}
                    </p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>

            <a href="{{ route('sekretaris.jurnal.pengganti') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="edit_note" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Isi Jurnal Pengganti</p>
                    <p class="text-xs text-muted">Untuk guru tugas luar / tidak hadir yang memberi tugas via WA</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>
        </div>
    @endunless
</x-layouts.app>
