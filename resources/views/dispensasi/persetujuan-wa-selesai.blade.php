<x-layouts.guest title="Persetujuan Dispensasi">
    <div class="flex flex-col items-center gap-3 text-center">
        @if ($keputusan === 'approved')
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-hadir-soft text-hadir">
                <x-icon name="check_circle" :size="36" fill />
            </span>
            <h1 class="text-xl font-bold text-hadir">Dispensasi Disetujui</h1>
            <p class="text-sm text-muted">
                Presensi <strong>{{ $dispensasi->siswa->nama }}</strong> sudah otomatis diperbarui.
                Terima kasih.
            </p>
        @else
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-alpha-soft text-alpha">
                <x-icon name="cancel" :size="36" fill />
            </span>
            <h1 class="text-xl font-bold text-alpha">Dispensasi Ditolak</h1>
            <p class="text-sm text-muted">Guru piket yang mengajukan akan diberitahu.</p>
        @endif
    </div>
</x-layouts.guest>
