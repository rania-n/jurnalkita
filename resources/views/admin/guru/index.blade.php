@php
    $rows = \App\Models\Guru::with('mapels', 'user')->orderBy('nama')->get();
@endphp

<x-layouts.admin title="Data Guru" heading="Data Guru">
    <x-admin.page title="Data Guru" subtitle="{{ $rows->count() }} guru terdaftar">
        <x-slot:action>
            <x-ui.button :href="route('master.guru.create')" icon="add">Tambah Guru</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada data guru" />
    @else
        <x-admin.table :head="['Nama', 'NIP', 'Mata Pelajaran', 'Akun', '']">
            @foreach ($rows as $g)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $g->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $g->nip ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $g->mapels->pluck('nama')->join(', ') ?: '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($g->user_id)
                            <x-ui.status-badge status="disetujui">Ada</x-ui.status-badge>
                        @else
                            <x-ui.status-badge status="menunggu">Belum</x-ui.status-badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            :edit="route('master.guru.create')"
                            :delete-action="route('admin.stub')"
                            delete-confirm="Yakin hapus data guru {{ $g->nama }}?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
