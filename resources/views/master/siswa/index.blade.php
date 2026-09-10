@php
    $data = [
        ['no' => '01', 'nama' => 'Ahmad Fauzi', 'nis' => '12345', 'kelas' => 'X RPL 1', 'jk' => 'L'],
        ['no' => '02', 'nama' => 'Dewi Lestari', 'nis' => '12346', 'kelas' => 'X RPL 1', 'jk' => 'P'],
        ['no' => '03', 'nama' => 'Fajar Nugraha', 'nis' => '12347', 'kelas' => 'X RPL 1', 'jk' => 'L'],
    ];
@endphp

<x-layouts.app title="Data Siswa" width="wide">
    <x-page-header title="Data Siswa" subtitle="Kelola Data Siswa" :back="route('master.index')" />

    <div class="flex flex-col gap-3">
        <x-ui.search-bar name="cari" placeholder="Cari nama atau NIS siswa..." />

        <div class="flex gap-2">
            <x-ui.filter-select name="kelas">
                <option value="">Semua Kelas</option>
                <option value="x-rpl-1">X RPL 1</option>
            </x-ui.filter-select>
            <x-ui.filter-select name="jk">
                <option value="">Jenis Kelamin</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </x-ui.filter-select>
        </div>

        <x-ui.add-button :href="route('master.siswa.create')">Tambah Data Siswa</x-ui.add-button>

        <x-ui.card-list class="mt-1">
            @foreach ($data as $d)
                <x-ui.list-card :title="$d['nama']" :meta="['NIS: ' . $d['nis'], 'Kelas: ' . $d['kelas']]">
                    <x-slot:leading>
                        <x-ui.avatar :label="$d['no']" :gender="$d['jk']" />
                    </x-slot:leading>
                    <x-slot:actions>
                        <x-ui.action-button label="Ubah" icon="edit" variant="info" :href="route('master.siswa.create')" />
                        <x-ui.action-button label="Hapus" icon="delete" variant="danger" href="#" data-confirm="Yakin hapus data siswa ini?" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    </div>
</x-layouts.app>
