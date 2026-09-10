@php
    $hari = request('hari');
    $kelasId = request('kelas');
    $guruId = request('guru');

    $rows = \App\Models\Jadwal::with('kelas', 'mapel', 'guru')
        ->when($hari, fn ($b) => $b->where('hari', $hari))
        ->when($kelasId, fn ($b) => $b->where('kelas_id', $kelasId))
        ->when($guruId, fn ($b) => $b->where('guru_id', $guruId))
        ->orderByRaw("field(hari,'senin','selasa','rabu','kamis','jumat')")
        ->orderBy('jam_ke_mulai')
        ->get();

    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
    $kelasList = \App\Models\Kelas::orderBy('nama')->get(['id', 'nama']);
    $mapelList = \App\Models\Mapel::orderBy('nama')->get(['id', 'nama']);
    $guruList = \App\Models\Guru::orderBy('nama')->get(['id', 'nama']);
@endphp

<x-layouts.admin title="Jadwal Pelajaran" heading="Jadwal Pelajaran">
    <x-admin.page title="Jadwal Pelajaran" subtitle="{{ $rows->count() }} jadwal">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-jadwal" data-modal-title="Tambah Jadwal">Tambah Jadwal</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.jadwal-pelajaran.index')">
        <x-admin.f-select name="hari" label="Hari" :options="$hariLabel" all="Semua Hari" />
        <x-admin.f-select name="kelas" label="Kelas" :options="$kelasList->pluck('nama', 'id')" all="Semua Kelas" />
        <x-admin.f-select name="guru" label="Guru" :options="$guruList->pluck('nama', 'id')" all="Semua Guru" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada jadwal yang cocok" />
    @else
        <x-admin.table :head="['Hari', 'Kelas', 'Mapel', 'Guru', 'JP', 'Ruang', '']">
            @foreach ($rows as $j)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $hariLabel[$j->hari] ?? $j->hari }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->kelas?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->mapel?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->guru?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->ruang ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-jadwal"
                            edit-title="Ubah Jadwal"
                            :edit-id="$j->id"
                            :edit-fill="['hari' => $j->hari, 'kelas_id' => $j->kelas_id, 'mapel_id' => $j->mapel_id, 'guru_id' => $j->guru_id, 'jam_ke_mulai' => $j->jam_ke_mulai, 'jam_ke_selesai' => $j->jam_ke_selesai, 'ruang' => $j->ruang]"
                            :delete-action="route('master.jadwal-pelajaran.destroy', $j)"
                            delete-confirm="Hapus jadwal ini?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-jadwal" title="Tambah Jadwal">
        <form method="POST" action="{{ route('master.jadwal-pelajaran.save') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.select label="Hari" name="hari">
                <option value="" disabled selected hidden>Pilih hari</option>
                @foreach ($hariLabel as $v => $l)
                    <option value="{{ $v }}">{{ $l }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select label="Kelas" name="kelas_id">
                <option value="" disabled selected hidden>Pilih kelas</option>
                @foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
            </x-ui.select>
            <x-ui.select label="Mata Pelajaran" name="mapel_id">
                <option value="" disabled selected hidden>Pilih mapel</option>
                @foreach ($mapelList as $m)<option value="{{ $m->id }}">{{ $m->nama }}</option>@endforeach
            </x-ui.select>
            <x-ui.select label="Guru Pengajar" name="guru_id">
                <option value="" disabled selected hidden>Pilih guru</option>
                @foreach ($guruList as $g)<option value="{{ $g->id }}">{{ $g->nama }}</option>@endforeach
            </x-ui.select>
            <div class="flex gap-3">
                <x-ui.input label="Jam ke- (mulai)" name="jam_ke_mulai" type="number" min="1" class="flex-1" />
                <x-ui.input label="Jam ke- (selesai)" name="jam_ke_selesai" type="number" min="1" class="flex-1" />
            </div>
            <x-ui.select label="Ruang" name="ruang">
                <option value="">— belum ditentukan —</option>
                @foreach (config('akademik.ruangan') as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </x-ui.select>
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
