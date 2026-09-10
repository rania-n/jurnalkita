<x-layouts.app title="Tambah Data Siswa">
    <x-page-header title="Tambah Data Siswa" subtitle="Kelola data siswa yang tersedia" :back="route('master.siswa.index')" />

    <form method="POST" action="{{ route('master.store', 'siswa') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.select label="Kelas" name="kelas">
            <option value="" disabled selected hidden>Pilih Kelas</option>
            <option value="x-rpl-1">X RPL 1</option>
            <option value="x-rpl-2">X RPL 2</option>
        </x-ui.select>

        <x-ui.input label="NIS" name="nis" inputmode="numeric" placeholder="Masukkan Nomor Induk Siswa" />
        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Masukkan nama lengkap siswa" />

        <x-ui.select label="Nomor Presensi" name="no_presensi">
            @for ($i = 1; $i <= 40; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </x-ui.select>

        <x-ui.select label="Jenis Kelamin" name="jk">
            <option value="" disabled selected hidden>Pilih Jenis Kelamin</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </x-ui.select>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="add">Tambah Siswa</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
