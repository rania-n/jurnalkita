@php
    $rows = \App\Models\Kelas::with('wali')->withCount('siswas')->orderBy('nama')->get();
@endphp

<x-layouts.admin title="Data Kelas" heading="Data Kelas">
    <x-admin.page title="Data Kelas" subtitle="{{ $rows->count() }} kelas">
        <x-slot:action>
            <x-ui.button :href="route('master.kelas.create')" icon="add">Tambah Kelas</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada data kelas" />
    @else
        <x-admin.table :head="['Nama', 'Tingkat', 'Jurusan', 'Jumlah Siswa', 'Wali Kelas', '']">
            @foreach ($rows as $k)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $k->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->tingkat }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->jurusan ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->siswas_count }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->wali?->nama ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            :edit="route('master.kelas.create')"
                            :delete-action="route('admin.stub')"
                            delete-confirm="Yakin hapus kelas {{ $k->nama }}?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
