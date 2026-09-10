@php
    $data = [
        ['status' => 'menunggu', 'nama' => 'Dude Fahrezi', 'kelas' => 'XI RPL 2', 'tanggal' => '06-09-2026'],
        ['status' => 'disetujui', 'nama' => 'Putri Zahwa', 'kelas' => 'XI RPL 2', 'tanggal' => '06-09-2026'],
        ['status' => 'ditolak', 'nama' => 'Varadita April', 'kelas' => 'XI RPL 2', 'tanggal' => '05-09-2026'],
        ['status' => 'disetujui', 'nama' => 'Fitra Fahrezi', 'kelas' => 'X RPL 1', 'tanggal' => '05-09-2026'],
    ];
    $tab = request('status', 'semua');
    $tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
@endphp

<x-layouts.app title="Daftar Dispensasi" menu="default" width="wide">
    <x-page-header
        title="Daftar Dispensasi"
        subtitle="Riwayat dan status persetujuan dispensasi oleh Staff Piket dan Waka Kesiswaan."
    />

    <div class="flex flex-col gap-3">
        <x-ui.search-bar name="cari" placeholder="Cari nama atau alasan dispensasi..." />

        <div class="flex gap-2">
            <x-ui.filter-select name="kelas">
                <option value="">Semua Kelas</option>
                <option value="x-rpl-1">X RPL 1</option>
                <option value="xi-rpl-2">XI RPL 2</option>
            </x-ui.filter-select>
            <x-ui.filter-select name="tanggal">
                <option value="">Tanggal</option>
                <option value="hari-ini">Hari ini</option>
                <option value="minggu-ini">Minggu ini</option>
            </x-ui.filter-select>
        </div>

        <x-ui.tabs :tabs="collect($tabs)->map(fn ($label, $value) => [
            'label' => $label,
            'url' => $value === 'semua' ? route('dispensasi.index') : route('dispensasi.index', ['status' => $value]),
            'active' => $tab === $value,
        ])->values()->all()" />

        <x-ui.add-button :href="route('dispensasi.create')">Ajukan Dispensasi</x-ui.add-button>

        <x-ui.card-list class="mt-1">
            @foreach ($data as $d)
                @continue($tab !== 'semua' && $tab !== $d['status'])
                <x-ui.list-card :title="$d['nama']" :meta="[$d['kelas'], 'Tanggal: ' . $d['tanggal']]">
                    <x-slot:badge><x-ui.status-badge :status="$d['status']" /></x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button label="Detail" icon="badge" :href="route('dispensasi.show')" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    </div>
</x-layouts.app>
