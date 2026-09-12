<x-layouts.app title="Beranda Satpam">
    <x-page-header title="Beranda Satpam" subtitle="Scan QR surat dispensasi siswa" />

    <x-alert type="info" class="mb-4">
        Nggak perlu buka kamera di sini — scan QR pakai <strong>kamera bawaan HP</strong>
        seperti biasa. Hasilnya langsung kebuka di halaman ini.
    </x-alert>

    <h2 class="mb-2 text-sm font-bold text-ink">Riwayat Scan Hari Ini</h2>

    @if ($riwayatHariIni->isEmpty())
        <x-ui.empty icon="qr_code_scanner" title="Belum ada scan hari ini" />
    @else
        <x-ui.card-list>
            @foreach ($riwayatHariIni as $log)
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
</x-layouts.app>
