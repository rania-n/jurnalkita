<x-layouts.admin title="Tambah Jadwal Pelajaran" heading="Tambah Jadwal Pelajaran">
    <x-admin.page title="Tambah Jadwal Pelajaran" :back="route('master.jadwal-pelajaran.index')" />

    <x-admin.form-card :action="route('master.store', 'jadwal-pelajaran')" submit="Simpan Jadwal" :cancel="route('master.jadwal-pelajaran.index')">
        <x-ui.select label="Hari" name="hari">
            <option value="" disabled selected hidden>Pilih hari</option>
            @foreach (['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'] as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select label="Kelas" name="kelas_id">
            <option value="" disabled selected hidden>Pilih kelas</option>
            @foreach (\App\Models\Kelas::orderBy('nama')->get() as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select label="Mata Pelajaran" name="mapel_id">
            <option value="" disabled selected hidden>Pilih mapel</option>
            @foreach (\App\Models\Mapel::orderBy('nama')->get() as $m)
                <option value="{{ $m->id }}">{{ $m->nama }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select label="Guru Pengajar" name="guru_id">
            <option value="" disabled selected hidden>Pilih guru</option>
            @foreach (\App\Models\Guru::orderBy('nama')->get() as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
            @endforeach
        </x-ui.select>
        <div class="flex gap-3">
            <x-ui.input label="Jam ke- (mulai)" name="jam_ke_mulai" type="number" min="1" class="flex-1" :value="old('jam_ke_mulai')" />
            <x-ui.input label="Jam ke- (selesai)" name="jam_ke_selesai" type="number" min="1" class="flex-1" :value="old('jam_ke_selesai')" />
        </div>
        <x-ui.input label="Ruang" name="ruang" placeholder="Contoh: Lab RPL" :value="old('ruang')" />
    </x-admin.form-card>
</x-layouts.admin>
