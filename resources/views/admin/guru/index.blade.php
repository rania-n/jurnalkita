@php
    $q = request('cari');
    $mapelId = request('mapel');

    $rows = \App\Models\Guru::with('mapels', 'user')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%")))
        ->when($mapelId, fn ($b) => $b->whereHas('mapels', fn ($m) => $m->where('mapels.id', $mapelId)))
        ->orderBy('nama')
        ->get();

    $mapelOptions = \App\Models\Mapel::orderBy('nama')->pluck('nama', 'id');
@endphp

<x-layouts.admin title="Data Guru" heading="Data Guru">
    <x-admin.page title="Data Guru" subtitle="{{ $rows->count() }} guru">
        <x-slot:action>
            <x-ui.button :href="route('master.guru.create')" icon="add">Tambah Guru</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.guru.index')">
        <x-admin.f-search placeholder="Nama atau NIP..." />
        <x-admin.f-select name="mapel" label="Mata Pelajaran" :options="$mapelOptions" all="Semua Mapel" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada guru yang cocok" />
    @else
        <x-admin.table :head="['Nama', 'NIP', 'Mata Pelajaran', 'Akun', '']">
            @foreach ($rows as $g)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $g->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $g->nip ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $g->mapels->pluck('nama')->join(', ') ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.status-badge :status="$g->user_id ? 'disetujui' : 'menunggu'">{{ $g->user_id ? 'Ada' : 'Belum' }}</x-ui.status-badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions :edit="route('master.guru.create')" :delete-action="route('admin.stub')" delete-confirm="Yakin hapus data guru {{ $g->nama }}?" />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
