@php
    $hariAktif = request('hari', 'rabu');
    $hariList = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
    $data = [
        ['nama' => 'Budi Santoso, S.Pd', 'jam' => '07:00 - 12:00'],
        ['nama' => 'Winartin, S.Pd', 'jam' => '07:00 - 12:00'],
        ['nama' => 'Drs. M. Yusuf', 'jam' => '12:00 - 16:00'],
        ['nama' => 'Sarah Amelia, M.Pd', 'jam' => '07:00 - 12:00'],
    ];
@endphp

<x-layouts.app title="Daftar Jadwal Piket" width="wide">
    <x-page-header
        title="Daftar Jadwal Piket"
        subtitle="Kelola jadwal piket yang tersedia"
        :back="route('master.index')"
    />

    <div class="flex flex-col gap-3">
        <x-ui.search-bar name="cari" placeholder="Cari nama staff..." />

        <x-ui.tabs :tabs="collect($hariList)->map(fn ($label, $value) => [
            'label' => $label,
            'url' => route('piket.jadwal.index', ['hari' => $value]),
            'active' => $hariAktif === $value,
        ])->values()->all()" />

        <x-ui.add-button :href="route('piket.jadwal.create')">Tambah Jadwal Piket</x-ui.add-button>

        <x-ui.card-list class="mt-1">
            @foreach ($data as $d)
                <x-ui.list-card :title="$d['nama']" :meta="[$hariList[$hariAktif] . ' · ' . $d['jam']]">
                    <x-slot:actions>
                        <x-ui.action-button label="Detail" icon="badge" href="#" />
                        <x-ui.action-button label="Ubah" icon="edit" variant="info" :href="route('piket.jadwal.create')" />
                        <x-ui.action-button label="Hapus" icon="delete" variant="danger" href="#" data-confirm="Yakin hapus jadwal piket ini?" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    </div>
</x-layouts.app>
