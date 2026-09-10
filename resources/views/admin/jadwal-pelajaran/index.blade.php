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
    $kelasOptions = \App\Models\Kelas::orderBy('nama')->pluck('nama', 'id');
    $guruOptions = \App\Models\Guru::orderBy('nama')->pluck('nama', 'id');
@endphp

<x-layouts.admin title="Jadwal Pelajaran" heading="Jadwal Pelajaran">
    <x-admin.page title="Jadwal Pelajaran" subtitle="{{ $rows->count() }} jadwal">
        <x-slot:action>
            <x-ui.button :href="route('master.jadwal-pelajaran.create')" icon="add">Tambah Jadwal</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.jadwal-pelajaran.index')">
        <x-admin.f-select name="hari" label="Hari" :options="$hariLabel" all="Semua Hari" />
        <x-admin.f-select name="kelas" label="Kelas" :options="$kelasOptions" all="Semua Kelas" />
        <x-admin.f-select name="guru" label="Guru" :options="$guruOptions" all="Semua Guru" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada jadwal yang cocok" />
    @else
        <x-admin.table :head="['Hari', 'Kelas', 'Mata Pelajaran', 'Guru', 'JP', 'Ruang', '']">
            @foreach ($rows as $j)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $hariLabel[$j->hari] ?? $j->hari }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->kelas?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->mapel?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->guru?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->ruang ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions :edit="route('master.jadwal-pelajaran.create')" :delete-action="route('admin.stub')" delete-confirm="Hapus jadwal ini?" />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
