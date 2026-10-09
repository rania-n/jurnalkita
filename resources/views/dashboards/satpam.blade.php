<x-layouts.app title="Portal Satpam">
    <x-page-header title="Portal Gerbang Satpam" subtitle="Pantau dan konfirmasi kepulangan siswa" />

    <div class="space-y-6">
        <div class="rounded-xl border border-surface-alt bg-card p-4">
            <h2 class="font-bold text-ink">Siswa Izin Keluar (Belum Kembali)</h2>
            <p class="text-sm text-muted-2">Daftar siswa yang saat ini sedang izin meninggalkan sekolah.</p>

            <div class="mt-4 flex flex-col gap-3">
                @forelse($dispensasiKeluar as $disp)
                    <div class="flex items-center justify-between rounded-lg border border-surface-alt p-3">
                        <div>
                            <p class="font-semibold text-ink">{{ $disp->siswa->nama }} ({{ $disp->siswa->kelas?->nama }})</p>
                            <p class="text-xs text-muted-2">Alasan: {{ $disp->alasan }}</p>
                            <p class="text-xs text-muted-2">Waktu Izin: JP {{ $disp->jam_ke_mulai ?: 'Full' }}</p>
                        </div>
                        <form method="POST" action="{{ route('satpam.konfirmasi-kembali', $disp) }}">
                            @csrf
                            <x-ui.button type="submit" size="sm" variant="primary">Konfirmasi Kembali</x-ui.button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-muted">Tidak ada siswa yang sedang izin keluar saat ini.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-surface-alt bg-card p-4">
            <h2 class="font-bold text-ink">Scan Surat Izin Keluar</h2>
            <p class="text-sm text-muted-2">Gunakan kamera bawaan HP untuk memindai QR Code pada surat dispensasi siswa.</p>
        </div>
    </div>
</x-layouts.app>
