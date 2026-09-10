<x-layouts.admin title="Tambah Kelas" heading="Tambah Kelas">
    <x-admin.page title="Tambah Kelas" :back="route('master.kelas.index')" />

    <x-admin.form-card :action="route('master.store', 'kelas')" submit="Simpan Kelas" :cancel="route('master.kelas.index')">
        <x-ui.input label="Nama Kelas" name="nama" placeholder="Contoh: X RPL 1" :value="old('nama')" />
        <x-ui.select label="Tingkat" name="tingkat">
            <option value="" disabled selected hidden>Pilih tingkat</option>
            <option value="X">X</option>
            <option value="XI">XI</option>
            <option value="XII">XII</option>
        </x-ui.select>
        <x-ui.input label="Jurusan" name="jurusan" placeholder="Contoh: RPL" :value="old('jurusan')" />
        <x-ui.select label="Wali Kelas" name="wali_id">
            <option value="" disabled selected hidden>Pilih wali kelas</option>
            @foreach (\App\Models\Guru::orderBy('nama')->get() as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
            @endforeach
        </x-ui.select>
    </x-admin.form-card>
</x-layouts.admin>
