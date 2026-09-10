@php
    $data = [
        ['nama' => 'Matematika', 'kode' => 'MAT-001'],
        ['nama' => 'Kreativitas, Inovasi, dan Kewirausahaan', 'kode' => 'KIK-001'],
        ['nama' => 'Mapel Pilihan RPL', 'kode' => 'MPR-001'],
        ['nama' => 'Bahasa Jepang', 'kode' => 'BJE-001'],
    ];
@endphp

<x-layouts.app title="Daftar Mata Pelajaran" width="wide">
    <x-page-header title="Daftar Mata Pelajaran" subtitle="Kelola mata pelajaran yang tersedia" :back="route('master.index')" />

    <div class="flex flex-col gap-3">
        <x-ui.search-bar name="cari" placeholder="Cari mata pelajaran..." />
        <x-ui.add-button :href="route('master.mapel.create')">Tambah Mata Pelajaran</x-ui.add-button>

        <x-ui.card-list class="mt-1">
            @foreach ($data as $d)
                <x-ui.list-card :title="$d['nama']" :meta="[$d['kode']]">
                    <x-slot:actions>
                        <x-ui.action-button label="Ubah" icon="edit" variant="info" :href="route('master.mapel.create')" />
                        <x-ui.action-button label="Hapus" icon="delete" variant="danger" href="#" data-confirm="Yakin hapus mata pelajaran ini?" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    </div>
</x-layouts.app>
