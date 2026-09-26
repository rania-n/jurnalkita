<x-layouts.admin title="Detail Kelas" :heading="$kelas->nama">
    <x-admin.page :title="$kelas->nama" :subtitle="$kelas->jurusanNama() . ' · ' . $siswas->count() . ' siswa'" :back="route('master.kelas.index')" />

    <div class="mb-4 flex flex-wrap gap-1.5">
        <x-ui.status-badge :status="$kelas->pkl() ? 'pkl' : 'anggota'">{{ $kelas->pkl() ? 'PKL' : 'Bukan PKL' }}</x-ui.status-badge>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-ui.field-static label="Tingkat" icon="school">{{ $kelas->tingkat }}</x-ui.field-static>
        <x-ui.field-static label="Jurusan" icon="menu_book">{{ $kelas->jurusanNama() }}</x-ui.field-static>
        <x-ui.field-static label="Wali Kelas" icon="badge">{{ $kelas->wali?->nama ?: '— belum ditentukan —' }}</x-ui.field-static>
    </div>

    <h2 class="mb-2 text-sm font-bold text-ink">Roster Siswa ({{ $siswas->count() }})</h2>
    <div class="mb-6">
        @if ($siswas->isEmpty())
            <x-ui.empty title="Belum ada siswa di kelas ini" />
        @else
            <x-admin.table :head="['No. Absen', 'Nama', 'NIS', 'Jabatan']">
                @foreach ($siswas as $s)
                    <tr>
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                        <td class="px-4 py-2.5 text-muted">{{ $s->nis }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.status-badge :status="$s->jabatan === 'pengurus' ? 'pengurus' : 'anggota'" />
                            @if ($s->isPkl())
                                <x-ui.status-badge status="pkl" class="ml-1" />
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>
        @endif
    </div>

    <h2 class="mb-2 text-sm font-bold text-ink">Jadwal Pelajaran Seminggu</h2>
    @if ($jadwalPerHari->isEmpty())
        <x-ui.empty title="Belum ada jadwal pelajaran untuk kelas ini" />
    @else
        <div class="flex flex-col gap-4">
            @foreach ($hariLabel as $key => $label)
                @continue (! $jadwalPerHari->has($key))
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-muted-2">{{ $label }}</p>
                    <x-admin.table :head="['JP', 'Mata Pelajaran', 'Guru', 'Ruang']">
                        @foreach ($jadwalPerHari[$key]->sortBy('jam_ke_mulai') as $j)
                            <tr>
                                <td class="px-4 py-2.5 text-muted">JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</td>
                                <td class="px-4 py-2.5 font-semibold text-ink">{{ $j->mapel->nama }}</td>
                                <td class="px-4 py-2.5 text-muted">{{ $j->guru->nama }}</td>
                                <td class="px-4 py-2.5 text-muted">{{ $j->ruang ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </x-admin.table>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
