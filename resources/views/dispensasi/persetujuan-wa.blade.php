<x-layouts.guest title="Persetujuan Dispensasi">
    <div class="mb-4 text-center">
        <span class="flex h-12 w-12 items-center justify-center mx-auto rounded-xl bg-navy text-card">
            <x-icon name="approval" :size="24" fill />
        </span>
        <h1 class="mt-2 text-lg font-bold text-ink">Persetujuan Dispensasi</h1>
        <p class="text-xs text-muted-2">Diajukan oleh {{ $dispensasi->pengaju->name }}</p>
    </div>

    <div class="flex flex-col gap-3 text-sm">
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="text-muted">Nama Siswa</span>
            <span class="font-semibold text-ink text-right">{{ $dispensasi->siswa->nama }}</span>
        </div>
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="text-muted">Kelas</span>
            <span class="font-semibold text-ink">{{ $dispensasi->siswa->kelas?->nama ?? '—' }}</span>
        </div>
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="text-muted">Tanggal / Jam</span>
            <span class="font-semibold text-ink text-right">
                {{ $dispensasi->labelTanggal() }} — {{ $dispensasi->labelJam() }}
            </span>
        </div>
        <div class="border-b border-surface-alt pb-2">
            <span class="text-muted">Alasan</span>
            <p class="mt-1 font-semibold text-ink">{{ $dispensasi->alasan }}</p>
        </div>
    </div>

    @if ($otomatisKadaluarsa ?? false)
        <x-alert type="warning" class="mt-5">
            Pengajuan ini sudah <strong>kadaluarsa</strong> — melewati tanggal berlaku tanpa sempat diputuskan, jadi otomatis dibatalkan sistem.
        </x-alert>
    @elseif ($sudahDiputuskan)
        <x-alert type="info" class="mt-5">
            Pengajuan ini sudah diputuskan sebelumnya:
            <strong>{{ $dispensasi->status_waka === 'approved' ? 'Disetujui' : 'Ditolak' }}</strong>.
        </x-alert>
    @else
        <form method="POST" action="{{ url()->full() }}" class="mt-5 flex flex-col gap-3">
            @csrf
            <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                <button type="submit" name="keputusan" value="approved"
                    class="press flex h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-hadir/25 bg-hadir-soft text-base font-bold text-hadir">
                    <x-icon name="check" :size="20" /> Setujui
                </button>
                <button type="submit" name="keputusan" value="rejected"
                    class="press flex h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-alpha/25 bg-alpha-soft text-base font-bold text-alpha">
                    <x-icon name="close" :size="20" /> Tolak
                </button>
            </div>
        </form>
    @endif
</x-layouts.guest>
