@php
    $q = request('cari');
    $tingkat = request('tingkat');

    $rows = \App\Models\Kelas::with('wali')->withCount('siswas')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('jurusan', 'like', "%{$q}%")))
        ->when($tingkat, fn ($b) => $b->where('tingkat', $tingkat))
        ->orderBy('tingkat')->orderBy('nama')
        ->get();
@endphp

<x-layouts.admin title="Data Kelas" heading="Data Kelas">
    <x-admin.page title="Data Kelas" subtitle="{{ $rows->count() }} kelas">
        <x-slot:action>
            <x-ui.button :href="route('master.kelas.create')" icon="add">Tambah Kelas</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.kelas.index')">
        <x-admin.f-search placeholder="Nama kelas / jurusan..." />
        <x-admin.f-select name="tingkat" label="Tingkat" :options="['X' => 'X', 'XI' => 'XI', 'XII' => 'XII']" all="Semua Tingkat" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada kelas yang cocok" />
    @else
        <x-admin.table :head="['Nama', 'Tingkat', 'Jurusan', 'Jml Siswa', 'Wali Kelas', '']">
            @foreach ($rows as $k)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $k->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->tingkat }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->jurusan ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->siswas_count }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->wali?->nama ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions :edit="route('master.kelas.create')" :delete-action="route('admin.stub')" delete-confirm="Yakin hapus kelas {{ $k->nama }}?" />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
