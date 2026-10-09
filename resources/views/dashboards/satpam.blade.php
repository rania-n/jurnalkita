<x-layouts.app title="Portal Satpam">
    <x-page-header title="Portal Gerbang Satpam" subtitle="Pantau, pindai, dan konfirmasi kepulangan siswa" />

    <div class="space-y-6">
        <div class="rounded-xl border border-surface-alt bg-card p-4">
            <h2 class="font-bold text-ink">Pindai Surat Izin Keluar</h2>
            <p class="text-sm text-muted-2">Arahkan kamera ke QR Code pada surat dispensasi siswa.</p>
            <x-ui.button class="mt-3" block icon="qr_code_scanner" data-modal-open="modal-scan-qr" data-scan-buka>Buka Kamera Pemindai</x-ui.button>
        </div>

        <x-ui.modal id="modal-scan-qr" title="Pindai QR Surat Izin">
            <div class="flex flex-col gap-3">
                <video data-scan-video playsinline muted class="max-h-[50vh] w-full rounded-xl bg-ink object-contain"></video>
                <p data-scan-pesan class="text-center text-sm text-muted">Arahkan kamera ke QR Code.</p>
            </div>
        </x-ui.modal>

        <div class="rounded-xl border border-surface-alt bg-card p-4">
            <h2 class="font-bold text-ink">Siswa Izin Keluar (Belum Kembali)</h2>
            <p class="text-sm text-muted-2">Daftar siswa yang saat ini sedang izin meninggalkan sekolah.</p>

            <div class="mt-4 flex flex-col gap-3">
                @forelse($dispensasiKeluar as $disp)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-surface-alt p-3">
                        <div>
                            <p class="font-semibold text-ink">{{ $disp->siswa->nama }} ({{ $disp->siswa->kelas?->nama ?? '—' }})</p>
                            <p class="text-xs text-muted-2">Alasan: {{ $disp->alasan }}</p>
                            <p class="text-xs text-muted-2">Waktu Izin: {{ $disp->labelJam() }} · {{ $disp->labelTanggal() }}</p>
                        </div>
                        <form method="POST" action="{{ route('satpam.konfirmasi-kembali', $disp) }}">
                            @csrf
                            <x-ui.button type="submit" icon="how_to_reg">Konfirmasi Kembali</x-ui.button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-muted">Tidak ada siswa yang sedang izin keluar saat ini.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-surface-alt bg-card p-4">
            <h2 class="font-bold text-ink">Dispensasi &amp; Izin Keluar Berlaku Hari Ini</h2>
            <p class="text-sm text-muted-2">Seluruh siswa yang telah disetujui untuk lomba atau izin keluar hari ini.</p>

            <div class="mt-4 flex flex-col gap-3">
                @forelse($berlakuHariIni as $disp)
                    <div class="flex items-start justify-between gap-3 rounded-lg border border-surface-alt p-3">
                        <div>
                            <p class="font-semibold text-ink">{{ $disp->siswa->nama }} ({{ $disp->siswa->kelas?->nama ?? '—' }})</p>
                            <p class="text-xs text-muted-2">{{ $disp->jenis === 'lomba' ? 'Lomba' : 'Izin keluar' }} · {{ $disp->labelJam() }} · {{ $disp->labelTanggal() }}</p>
                            <p class="text-xs text-muted-2">Alasan: {{ $disp->alasan }}</p>
                        </div>
                        @if ($disp->jenis === 'izin_keluar')
                            <x-ui.status-badge :status="$disp->waktu_kembali ? 'hadir' : 'dispensasi'">{{ $disp->waktu_kembali ? 'Sudah Kembali' : 'Di Luar' }}</x-ui.status-badge>
                        @else
                            <x-ui.status-badge status="dispen">Lomba</x-ui.status-badge>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-muted">Tidak ada dispensasi atau izin keluar yang berlaku hari ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const dialog = document.getElementById('modal-scan-qr');
                const video = dialog.querySelector('[data-scan-video]');
                const pesan = dialog.querySelector('[data-scan-pesan]');
                let stream = null, timer = null;

                function tutup() {
                    clearInterval(timer);
                    stream?.getTracks().forEach((t) => t.stop());
                    stream = null;
                    video.srcObject = null;
                }

                async function mulai() {
                    if (!('BarcodeDetector' in window)) {
                        video.hidden = true;
                        pesan.textContent = 'Peramban ini belum mendukung pemindai. Gunakan aplikasi Kamera bawaan HP untuk memindai QR Code.';
                        return;
                    }
                    video.hidden = false;
                    pesan.textContent = 'Arahkan kamera ke QR Code.';
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                    } catch (e) {
                        video.hidden = true;
                        pesan.textContent = 'Kamera tidak dapat dibuka. Izinkan akses kamera pada peramban, lalu coba lagi.';
                        return;
                    }
                    video.srcObject = stream;
                    await video.play();
                    const detector = new BarcodeDetector({ formats: ['qr_code'] });
                    timer = setInterval(async () => {
                        const hasil = await detector.detect(video).catch(() => []);
                        for (const kode of hasil) {
                            try {
                                const url = new URL(kode.rawValue);
                                if (url.pathname === '/satpam/scan') {
                                    tutup();
                                    window.location.href = @js(route('satpam.scan', [], false)) + url.search;
                                    return;
                                }
                            } catch (e) {}
                            pesan.textContent = 'QR Code ini bukan surat izin keluar yang sah.';
                        }
                    }, 400);
                }

                document.querySelector('[data-scan-buka]').addEventListener('click', mulai);
                dialog.addEventListener('close', tutup);
            })();
        </script>
    @endpush
</x-layouts.app>
