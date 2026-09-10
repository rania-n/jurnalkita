@php
    $rows = \App\Models\Jadwal::with('kelas', 'mapel', 'guru')
        ->orderBy('hari')->orderBy('jam_ke_mulai')->get();
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
@endphp

<x-layouts.admin title="Jadwal Pelajaran" heading="Jadwal Pelajaran">
    <x-admin.page title="Jadwal Pelajaran" subtitle="{{ $rows->count() }} jadwal">
        <x-slot:action>
            <x-ui.button :href="route('master.jadwal-pelajaran.create')" icon="add">Tambah Jadwal</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada jadwal pelajaran" />
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
