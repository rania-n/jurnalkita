@php
    $q = request('cari');
    $kelasId = request('kelas');
    $jk = request('jk');

    $rows = \App\Models\Siswa::with('kelas')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%")))
        ->when($kelasId, fn ($b) => $b->where('kelas_id', $kelasId))
        ->when($jk, fn ($b) => $b->where('jenis_kelamin', $jk))
        ->orderBy('nama')
        ->paginate(20)->withQueryString();

    $kelasOptions = \App\Models\Kelas::orderBy('nama')->pluck('nama', 'id');
@endphp

<x-layouts.admin title="Data Siswa" heading="Data Siswa">
    <x-admin.page title="Data Siswa" subtitle="{{ $rows->total() }} siswa">
        <x-slot:action>
            <x-ui.button :href="route('master.siswa.create')" icon="add">Tambah Siswa</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.siswa.index')">
        <x-admin.f-search placeholder="Nama atau NIS..." />
        <x-admin.f-select name="kelas" label="Kelas" :options="$kelasOptions" all="Semua Kelas" />
        <x-admin.f-select name="jk" label="Jenis Kelamin" :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" all="Semua" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada siswa yang cocok" />
    @else
        <x-admin.table :head="['NIS', 'Nama', 'Kelas', 'L/P', 'Jabatan', '']">
            @foreach ($rows as $s)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 text-muted">{{ $s->nis }}</td>
                    <td class="px-4 py-3 font-semibold text-ink">{{ $s->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $s->kelas?->nama ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $s->jenis_kelamin }}</td>
                    <td class="px-4 py-3">
                        @if ($s->jabatan === 'pengurus')
                            <x-ui.status-badge status="izin">Pengurus</x-ui.status-badge>
                        @else
                            <span class="text-muted">Anggota</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions :edit="route('master.siswa.create')" :delete-action="route('admin.stub')" delete-confirm="Yakin hapus {{ $s->nama }}?" />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <div class="mt-4">{{ $rows->links() }}</div>
    @endif
</x-layouts.admin>
