<x-layouts.app title="Tambah Jadwal Pelajaran">
    <x-page-header title="Tambah Jadwal Pelajaran" subtitle="Kelola jadwal kelas per hari & jam pelajaran" :back="route('master.jadwal-pelajaran.index')" />

    <form method="POST" action="{{ route('master.store', 'jadwal-pelajaran') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.select label="Hari" name="hari">
            <option value="" disabled selected hidden>Pilih Hari (e.g. Senin)</option>
            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                <option value="{{ strtolower($h) }}">{{ $h }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.select label="Kelas" name="kelas">
            <option value="" disabled selected hidden>Pilih Kelas (e.g. X RPL 1)</option>
            <option value="x-rpl-1">X RPL 1</option>
        </x-ui.select>

        <div class="flex gap-3">
            <x-ui.select label="Jam Mulai" name="jam_mulai" class="flex-1">
                <option value="1">Jam ke-1</option>
                <option value="2">Jam ke-2</option>
            </x-ui.select>
            <x-ui.select label="Jam Selesai" name="jam_selesai" class="flex-1">
                <option value="3">Jam ke-3</option>
                <option value="4">Jam ke-4</option>
            </x-ui.select>
        </div>

        <x-ui.select label="Mata Pelajaran" name="mapel">
            <option value="" disabled selected hidden>Pilih Mata Pelajaran</option>
            <option value="matematika">Matematika</option>
        </x-ui.select>

        <x-ui.select label="Guru Pengajar" name="guru">
            <option value="" disabled selected hidden>Pilih Guru Pengajar</option>
            <option value="1">Winartin, S.Pd</option>
        </x-ui.select>

        <x-ui.select label="Ruangan" name="ruangan">
            <option value="" disabled selected hidden>Pilih Ruangan (e.g. Lab 1)</option>
            <option value="r58">R58</option>
            <option value="lab-rpl">Lab RPL</option>
        </x-ui.select>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="add">Tambah Jadwal Pelajaran</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
