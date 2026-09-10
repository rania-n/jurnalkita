<x-layouts.app title="Tambah Data Kelas">
    <x-page-header title="Tambah Data Kelas" subtitle="Kelola data kelas yang tersedia" :back="route('master.kelas.index')" />

    <form method="POST" action="{{ route('master.store', 'kelas') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.select label="Tingkat" name="tingkat">
            <option value="" disabled selected hidden>Pilih Tingkat (X, XI, XII)</option>
            <option value="X">X</option>
            <option value="XI">XI</option>
            <option value="XII">XII</option>
        </x-ui.select>

        <x-ui.select label="Jurusan" name="jurusan">
            <option value="" disabled selected hidden>Pilih Jurusan (e.g. Rekayasa Perangkat Lunak)</option>
            <option value="RPL">Rekayasa Perangkat Lunak</option>
            <option value="TKJ">Teknik Komputer dan Jaringan</option>
            <option value="MM">Multimedia</option>
        </x-ui.select>

        <x-ui.select label="Guru Wali Kelas" name="wali">
            <option value="" disabled selected hidden>Pilih Guru Wali Kelas</option>
            <option value="1">Winartin, S.Pd</option>
            <option value="2">Budi Santoso, S.Pd</option>
        </x-ui.select>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="add">Tambah Kelas</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
