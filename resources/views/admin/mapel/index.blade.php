@php
    $q = request('cari');
    $rows = \App\Models\Mapel::when($q, fn ($b) => $b->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('kode', 'like', "%{$q}%")))
        ->orderBy('nama')->get();
@endphp

<x-layouts.admin title="Mata Pelajaran" heading="Mata Pelajaran">
    <x-admin.page title="Mata Pelajaran" subtitle="{{ $rows->count() }} mapel">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-mapel" data-modal-title="Tambah Mata Pelajaran">Tambah Mapel</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.mapel.index')">
        <x-admin.f-search placeholder="Kode atau nama..." />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada mata pelajaran yang cocok" />
    @else
        <x-admin.table :head="['Kode', 'Nama', '']">
            @foreach ($rows as $m)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 text-muted">{{ $m->kode }}</td>
                    <td class="px-4 py-3 font-semibold text-ink">{{ $m->nama }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-mapel"
                            edit-title="Ubah Mata Pelajaran"
                            :edit-id="$m->id"
                            :edit-fill="['kode' => $m->kode, 'nama' => $m->nama]"
                            :delete-action="route('admin.stub')"
                            delete-confirm="Hapus {{ $m->nama }}?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-mapel" title="Tambah Mata Pelajaran">
        <form method="POST" action="{{ route('master.store', 'mapel') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.input label="Kode" name="kode" placeholder="Contoh: MAT" />
            <x-ui.input label="Nama Mata Pelajaran" name="nama" placeholder="Contoh: Matematika" />
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
