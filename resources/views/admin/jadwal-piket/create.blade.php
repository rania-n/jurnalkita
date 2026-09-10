<x-layouts.admin title="Tambah Jadwal Piket" heading="Tambah Jadwal Piket">
    <x-admin.page title="Tambah Jadwal Piket" :back="route('master.jadwal-piket.index')" />

    <x-admin.form-card :action="route('master.store', 'jadwal-piket')" submit="Simpan" :cancel="route('master.jadwal-piket.index')">
        <x-ui.select label="Hari" name="hari">
            <option value="" disabled selected hidden>Pilih hari</option>
            @foreach (['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'] as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select label="Guru Piket" name="guru_id">
            <option value="" disabled selected hidden>Pilih guru</option>
            @foreach (\App\Models\Guru::orderBy('nama')->get() as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
            @endforeach
        </x-ui.select>
        <div class="flex gap-3">
            <x-ui.input label="Jam Mulai" name="mulai" type="time" value="07:00" class="flex-1" />
            <x-ui.input label="Jam Selesai" name="selesai" type="time" value="12:00" class="flex-1" />
        </div>
        <x-ui.input label="Keterangan (opsional)" name="keterangan" :value="old('keterangan')" />
    </x-admin.form-card>
</x-layouts.admin>
