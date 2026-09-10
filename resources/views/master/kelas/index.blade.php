@php
    $data = [
        ['nama' => 'X RPL 1', 'siswa' => 36, 'wali' => 'Winartin, S.Pd'],
        ['nama' => 'X RPL 2', 'siswa' => 36, 'wali' => 'Winartin, S.Pd'],
        ['nama' => 'XI TKJ 1', 'siswa' => 36, 'wali' => 'Drs. M. Yusuf'],
        ['nama' => 'XI TKJ 2', 'siswa' => 34, 'wali' => 'Sarah Amelia, M.Pd'],
        ['nama' => 'XII RPL 1', 'siswa' => 35, 'wali' => 'Budi Santoso, S.Pd'],
    ];
@endphp

<x-layouts.app title="Data Kelas" width="wide">
    <x-page-header title="Data Kelas" subtitle="Kelola Data Kelas" :back="route('master.index')" />

    <div class="flex flex-col gap-3">
        <x-ui.search-bar name="cari" placeholder="Cari kelas..." />
        <x-ui.add-button :href="route('master.kelas.create')">Tambah Data Kelas</x-ui.add-button>

        <x-ui.card-list class="mt-1">
            @foreach ($data as $d)
                <x-ui.list-card :title="$d['nama']" :meta="[$d['siswa'] . ' Siswa', 'Wali Kelas: ' . $d['wali']]">
                    <x-slot:actions>
                        <x-ui.action-button label="Siswa" icon="groups" :href="route('master.siswa.index')" />
                        <x-ui.action-button label="Ubah" icon="edit" variant="info" :href="route('master.kelas.create')" />
                        <x-ui.action-button label="Hapus" icon="delete" variant="danger" href="#" data-confirm="Yakin hapus kelas ini?" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    </div>
</x-layouts.app>
