<x-layouts.app title="Beranda Satpam">
    <x-page-header title="Beranda Satpam" subtitle="Scan QR dispensasi & catat siswa terlambat" />

    <x-ui.jam-sekarang :jp-sekarang="\App\Support\Waktu::jpAktifSekarang()" />

    <x-alert type="info" class="mb-4">
        Nggak perlu buka kamera di sini — scan QR pakai <strong>kamera bawaan HP</strong>
        seperti biasa. Hasilnya langsung kebuka di halaman ini.
    </x-alert>

    <a href="{{ route('satpam.terlambat.create') }}" class="press mb-6 flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
            <x-icon name="schedule" :size="24" />
        </span>
        <div class="flex-1">
            <p class="text-sm font-bold text-ink">Catat Siswa Terlambat</p>
            <p class="text-xs text-muted">Siswa telat masuk gerbang pagi</p>
        </div>
        <x-icon name="chevron_right" :size="20" class="text-muted" />
    </a>

    <h2 class="mb-2 text-sm font-bold text-ink">Riwayat Scan Hari Ini</h2>

    @if ($riwayatScan->isEmpty())
        <x-ui.empty icon="qr_code_scanner" title="Belum ada scan hari ini" />
    @else
        <x-ui.card-list class="mb-6 grid-fill-last">
            @foreach ($riwayatScan as $log)
                <x-ui.list-card
                    :title="$log->deskripsi"
                    :meta="[$log->created_at->format('H:i:s')]"
                >
                    <x-slot:badge>
                        <x-ui.status-badge :status="str_starts_with($log->deskripsi, 'Scan valid') ? 'disetujui' : 'ditolak'">
                            {{ str_starts_with($log->deskripsi, 'Scan valid') ? 'Valid' : 'Tidak Valid' }}
                        </x-ui.status-badge>
                    </x-slot:badge>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    @endif

    <h2 class="mb-2 mt-6 text-sm font-bold text-ink">Siswa Terlambat Hari Ini</h2>

    @if ($riwayatTerlambat->isEmpty())
        <x-ui.empty icon="schedule" title="Belum ada catatan keterlambatan hari ini" />
    @else
        <x-ui.card-list class="grid-fill-last">
            @foreach ($riwayatTerlambat as $c)
                <x-ui.list-card
                    :title="$c->siswa->nama"
                    :meta="[$c->siswa->kelas?->nama . ' · Jam ' . $c->jam_datang->format('H:i'), $c->catatan ?: 'Tanpa catatan']"
                >
                    <x-slot:actions>
                        <form method="POST" action="{{ route('satpam.terlambat.destroy', $c) }}"
                              data-confirm="Hapus catatan keterlambatan {{ $c->siswa->nama }}? Salah input bisa dihapus, tapi cuma untuk hari ini.">
                            @csrf @method('DELETE')
                            <x-ui.action-button type="submit" variant="danger" icon="delete" label="Hapus" />
                        </form>
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    @endif
</x-layouts.app>
