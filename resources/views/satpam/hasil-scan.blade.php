<x-layouts.guest title="Hasil Scan">
    <div class="flex flex-col items-center gap-3 text-center">
        @if ($valid)
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-hadir-soft text-hadir">
                <x-icon name="check_circle" :size="48" fill />
            </span>
            <h1 class="text-2xl font-bold text-hadir">DISETUJUI</h1>
            <div class="mt-2 w-full rounded-xl bg-surface p-4 text-left text-sm">
                <p class="text-lg font-bold text-ink">{{ $dispensasi->siswa->nama }}</p>
                <p class="text-muted">{{ $dispensasi->siswa->kelas?->nama ?? '—' }}</p>
                <p class="mt-2 text-muted">{{ $dispensasi->labelJam() }} · {{ $dispensasi->labelTanggal() }}</p>
                <p class="mt-1 text-muted">{{ $dispensasi->alasan }}</p>
            </div>
        @else
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-alpha-soft text-alpha">
                <x-icon name="cancel" :size="48" fill />
            </span>
            <h1 class="text-2xl font-bold text-alpha">TIDAK BERLAKU</h1>
            <p class="text-sm text-muted">QR sudah kedaluwarsa, tidak valid, atau dispensasinya belum/tidak disetujui.</p>
        @endif
    </div>
</x-layouts.guest>
