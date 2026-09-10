<x-layouts.app title="Jurnal Pengganti">
    <x-page-header
        title="Isi Jurnal Pengganti"
        subtitle="Untuk guru yang memberi tugas via WA & tidak sempat mengisi sendiri"
        :back="route('sekretaris.jurnal.index')"
    />

    <x-alert type="info" class="mb-4">Hanya untuk status <strong>Tugas Luar</strong> atau <strong>Tidak Hadir</strong>. Jurnal ini otomatis terverifikasi.</x-alert>

    @if ($jadwals->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal kelas ini hari ini" />
    @else
        <form method="POST" action="{{ route('sekretaris.jurnal.pengganti.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.select label="Mata Pelajaran (jadwal)" name="jadwal_id" class="sm:col-span-2">
                    <option value="" disabled selected hidden>Pilih jadwal</option>
                    @foreach ($jadwals as $j)
                        <option value="{{ $j->id }}" @selected(old('jadwal_id') == $j->id)>
                            {{ $j->mapel->nama }} — {{ $j->guru->nama }} (JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }})
                        </option>
                    @endforeach
                </x-ui.select>

                <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai">
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_mulai') == $i)>Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
                <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai">
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_selesai') == $i)>Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>

                <x-ui.choice
                    label="Status Kehadiran Guru"
                    name="status_guru"
                    class="sm:col-span-2"
                    :options="['tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir']"
                    :tones="['tugas' => 'izin', 'tidak_hadir' => 'alpha']"
                    :value="old('status_guru', 'tugas')"
                />

                <x-ui.textarea label="Materi / Tugas yang diberikan" name="materi" :rows="3" class="sm:col-span-2" placeholder="Contoh: mengerjakan LKS halaman 12–15.">{{ old('materi') }}</x-ui.textarea>
                <x-ui.textarea label="Tugas Tambahan (opsional)" name="tugas_tambahan" :rows="2" class="sm:col-span-2">{{ old('tugas_tambahan') }}</x-ui.textarea>
            </div>

            <x-ui.sticky-bar>
                <x-ui.button type="submit" block icon="save">Simpan Jurnal Pengganti</x-ui.button>
            </x-ui.sticky-bar>
        </form>
    @endif
</x-layouts.app>
