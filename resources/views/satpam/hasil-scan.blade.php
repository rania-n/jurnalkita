<x-layouts.guest title="Hasil Scan">
    <div class="flex flex-col items-center gap-3 text-center">
        @if ($valid)
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-hadir-soft text-hadir">
                <x-icon name="check_circle" :size="48" fill />
            </span>
            <h1 class="text-2xl font-bold text-hadir">Disetujui</h1>
            <div class="mt-2 w-full rounded-xl bg-surface p-4 text-left text-sm">
                <p class="font-bold text-ink">Daftar Siswa ({{ $anggota->count() }})</p>
                <ol class="mt-1 list-inside list-decimal space-y-1 text-ink">
                    @foreach ($anggota as $item)
                        <li>{{ $item->siswa->nama }} <span class="text-muted">({{ $item->siswa->kelas?->nama ?? '—' }})</span></li>
                    @endforeach
                </ol>
                <p class="mt-2 text-muted">{{ $dispensasi->labelJam() }} · {{ $dispensasi->labelTanggal() }}</p>
                <p class="mt-1 text-muted">{{ $dispensasi->alasan }}</p>
            </div>
            @if(auth()->check() && auth()->user()->role === 'satpam' && $dispensasi->jenis === 'izin_keluar' && !$dispensasi->waktu_kembali)
                <form method="POST" action="{{ route('satpam.konfirmasi-kembali', $dispensasi) }}" class="w-full mt-4">
                    @csrf
                    <x-ui.button type="submit" block icon="how_to_reg">Konfirmasi Kembali ke Sekolah</x-ui.button>
                </form>
            @endif
        @else
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-alpha-soft text-alpha">
                <x-icon name="cancel" :size="48" fill />
            </span>
            <h1 class="text-2xl font-bold text-alpha">Tidak Berlaku</h1>
            <p class="text-sm text-muted">QR sudah kedaluwarsa, tidak valid, atau dispensasinya belum/tidak disetujui.</p>
        @endif
    </div>
</x-layouts.guest>
