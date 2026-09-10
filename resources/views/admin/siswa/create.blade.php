<x-layouts.admin title="Tambah Siswa" heading="Tambah Siswa">
    <x-admin.page title="Tambah Siswa" :back="route('master.siswa.index')" />

    <x-admin.form-card :action="route('master.store', 'siswa')" submit="Simpan Siswa" :cancel="route('master.siswa.index')">
        <x-ui.select label="Kelas" name="kelas_id">
            <option value="" disabled selected hidden>Pilih kelas</option>
            @foreach (\App\Models\Kelas::orderBy('nama')->get() as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input label="NIS" name="nis" inputmode="numeric" placeholder="Nomor Induk Siswa" :value="old('nis')" />
        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Nama lengkap siswa" :value="old('nama')" />
        <x-ui.input label="Nomor Presensi" name="no_absen" type="number" min="1" :value="old('no_absen')" />
        <x-ui.select label="Jenis Kelamin" name="jenis_kelamin">
            <option value="" disabled selected hidden>Pilih</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </x-ui.select>
    </x-admin.form-card>
</x-layouts.admin>
