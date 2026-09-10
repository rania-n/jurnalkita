@php
    $rows = \App\Models\Mapel::orderBy('nama')->get();
@endphp

<x-layouts.admin title="Mata Pelajaran" heading="Mata Pelajaran">
    <x-admin.page title="Mata Pelajaran" subtitle="{{ $rows->count() }} mapel">
        <x-slot:action>
            <x-ui.button :href="route('master.mapel.create')" icon="add">Tambah Mapel</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada mata pelajaran" />
    @else
        <x-admin.table :head="['Kode', 'Nama', '']">
            @foreach ($rows as $m)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 text-muted">{{ $m->kode }}</td>
                    <td class="px-4 py-3 font-semibold text-ink">{{ $m->nama }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions :edit="route('master.mapel.create')" :delete-action="route('admin.stub')" delete-confirm="Hapus {{ $m->nama }}?" />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
