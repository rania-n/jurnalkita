<x-layouts.admin title="Tambah Guru" heading="Tambah Guru">
    <x-admin.page title="Tambah Guru" subtitle="Data guru saja — akun login dibuat terpisah" :back="route('master.guru.index')" />

    <x-admin.form-card :action="route('master.store', 'guru')" submit="Simpan Guru" :cancel="route('master.guru.index')">
        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Nama lengkap guru" :value="old('nama')" />
        <x-ui.input label="NIP (opsional)" name="nip" placeholder="Nomor Induk Pegawai" :value="old('nip')" />
        <x-ui.input label="No. Telepon" name="no_hp" inputmode="numeric" placeholder="Nomor WhatsApp aktif" :value="old('no_hp')" />
        <x-ui.select label="Mata Pelajaran" name="mapel_id">
            <option value="" disabled selected hidden>Pilih mata pelajaran</option>
            @foreach (\App\Models\Mapel::orderBy('nama')->get() as $m)
                <option value="{{ $m->id }}">{{ $m->nama }}</option>
            @endforeach
        </x-ui.select>
    </x-admin.form-card>
</x-layouts.admin>
