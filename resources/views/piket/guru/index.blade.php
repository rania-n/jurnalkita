@php
    $data = [
        ['nama' => 'Budi Santoso, S.Pd', 'nik' => '198501012010011001', 'mapel' => 'Matematika'],
        ['nama' => 'Winartin, S.Pd', 'nik' => '198802242013022002', 'mapel' => 'Bahasa Inggris'],
        ['nama' => 'Drs. M. Yusuf', 'nik' => '197210151998031003', 'mapel' => 'Fisika'],
        ['nama' => 'Sarah Amelia, M.Pd', 'nik' => '199208082018012004', 'mapel' => 'Biologi'],
    ];
@endphp

<x-layouts.app title="Daftar Data Guru" width="wide">
    <x-page-header
        title="Daftar Data Guru"
        subtitle="Kelola data guru staff piket"
        :back="route('master.index')"
    />

    <div class="flex flex-col gap-3">
        <x-ui.search-bar name="cari" placeholder="Cari nama staff..." />

        <div class="flex gap-2">
            <x-ui.filter-select name="kelas">
                <option value="">Semua Kelas</option>
                <option value="x-rpl-1">X RPL 1</option>
            </x-ui.filter-select>
            <x-ui.filter-select name="mapel">
                <option value="">Semua Mapel</option>
                <option value="matematika">Matematika</option>
            </x-ui.filter-select>
        </div>

        <x-ui.add-button :href="route('piket.guru.create')">Tambah Data Guru</x-ui.add-button>

        <x-ui.card-list class="mt-1">
            @foreach ($data as $d)
                <x-ui.list-card :title="$d['nama']" :meta="['NIK. ' . $d['nik'], 'Mapel: ' . $d['mapel']]">
                    <x-slot:actions>
                        <x-ui.action-button label="Detail" icon="badge" href="#" />
                        <x-ui.action-button label="Ubah" icon="edit" variant="info" :href="route('piket.guru.create')" />
                        <x-ui.action-button label="Hapus" icon="delete" variant="danger" href="#" data-confirm="Yakin hapus data guru ini?" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    </div>
</x-layouts.app>
