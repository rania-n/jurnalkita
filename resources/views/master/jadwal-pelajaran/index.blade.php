@php
    $data = [
        ['mapel' => 'Matematika', 'detail' => 'Senin · JP 1 - JP 2 · R58', 'guru' => 'Winartin, S.Pd', 'kelas' => 'X RPL 1'],
        ['mapel' => 'Matematika', 'detail' => 'Selasa · JP 1 - JP 2 · R58', 'guru' => 'Winartin, S.Pd', 'kelas' => 'X RPL 1'],
        ['mapel' => 'Pemrograman Web', 'detail' => 'Rabu · JP 3 - JP 6 · Lab RPL', 'guru' => 'Budi Santoso, S.Pd', 'kelas' => 'XI RPL 2'],
    ];
@endphp

<x-layouts.app title="Daftar Jadwal Pelajaran" width="wide">
    <x-page-header title="Daftar Jadwal Pelajaran" subtitle="Kelola jadwal pelajaran yang tersedia" :back="route('master.index')" />

    <div class="flex flex-col gap-3">
        <x-ui.search-bar name="cari" placeholder="Cari mata pelajaran..." />

        <div class="flex gap-2">
            <x-ui.filter-select name="kelas">
                <option value="">Semua Kelas</option>
                <option value="x-rpl-1">X RPL 1</option>
            </x-ui.filter-select>
            <x-ui.filter-select name="hari">
                <option value="">Hari</option>
                <option value="senin">Senin</option>
            </x-ui.filter-select>
            <x-ui.filter-select name="guru">
                <option value="">Nama Guru</option>
                <option value="1">Winartin, S.Pd</option>
            </x-ui.filter-select>
        </div>

        <x-ui.add-button :href="route('master.jadwal-pelajaran.create')">Tambah Jadwal Pelajaran</x-ui.add-button>

        <x-ui.card-list class="mt-1">
            @foreach ($data as $d)
                <x-ui.list-card :title="$d['mapel']" :meta="[$d['detail'], $d['guru'], 'Kelas: ' . $d['kelas']]">
                    <x-slot:actions>
                        <x-ui.action-button label="Ubah" icon="edit" variant="info" :href="route('master.jadwal-pelajaran.create')" />
                        <x-ui.action-button label="Hapus" icon="delete" variant="danger" href="#" data-confirm="Yakin hapus jadwal ini?" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    </div>
</x-layouts.app>
