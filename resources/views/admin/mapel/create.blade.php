<x-layouts.admin title="Tambah Mapel" heading="Tambah Mata Pelajaran">
    <x-admin.page title="Tambah Mata Pelajaran" :back="route('master.mapel.index')" />

    <x-admin.form-card :action="route('master.store', 'mapel')" submit="Simpan Mapel" :cancel="route('master.mapel.index')">
        <x-ui.input label="Kode" name="kode" placeholder="Contoh: MAT" :value="old('kode')" />
        <x-ui.input label="Nama Mata Pelajaran" name="nama" placeholder="Contoh: Matematika" :value="old('nama')" />
    </x-admin.form-card>
</x-layouts.admin>
