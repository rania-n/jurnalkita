<x-layouts.app title="Tambah Jadwal Piket">
    <x-page-header
        title="Tambah Jadwal Piket"
        subtitle="Atur jadwal piket harian staff"
        :back="route('piket.jadwal.index')"
    />

    <form method="POST" action="{{ route('piket.jadwal.index') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.select label="Hari" name="hari">
            <option value="" disabled selected hidden>Pilih Hari</option>
            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                <option value="{{ strtolower($h) }}">{{ $h }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.select label="Guru Piket" name="guru">
            <option value="" disabled selected hidden>Pilih Guru</option>
            <option value="1">Budi Santoso, S.Pd</option>
            <option value="2">Winartin, S.Pd</option>
        </x-ui.select>

        <div class="flex gap-3">
            <x-ui.input label="Jam Mulai" name="jam_mulai" type="time" value="07:00" class="flex-1" />
            <x-ui.input label="Jam Selesai" name="jam_selesai" type="time" value="12:00" class="flex-1" />
        </div>

        <x-ui.input label="Keterangan" name="keterangan" placeholder="Keterangan tambahan (opsional)" />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="add">Tambah Jadwal Piket</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
