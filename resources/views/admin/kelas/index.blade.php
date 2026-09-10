@php
    $q = request('cari');
    $tingkat = request('tingkat');

    $rows = \App\Models\Kelas::with('wali')->withCount('siswas')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('jurusan', 'like', "%{$q}%")))
        ->when($tingkat, fn ($b) => $b->where('tingkat', $tingkat))
        ->orderBy('tingkat')->orderBy('nama')
        ->get();

    $guruList = \App\Models\Guru::orderBy('nama')->get(['id', 'nama']);
@endphp

<x-layouts.admin title="Data Kelas" heading="Data Kelas">
    <x-admin.page title="Data Kelas" subtitle="{{ $rows->count() }} kelas">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-kelas" data-modal-title="Tambah Kelas">Tambah Kelas</x-ui.button>
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
                        <x-admin.row-actions
                            edit-modal="modal-kelas"
                            edit-title="Ubah Kelas"
                            :edit-id="$k->id"
                            :edit-fill="['nama' => $k->nama, 'tingkat' => $k->tingkat, 'jurusan' => $k->jurusan, 'wali_id' => $k->wali_id]"
                            :delete-action="route('master.kelas.destroy', $k)"
                            delete-confirm="Yakin hapus kelas {{ $k->nama }}?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-kelas" title="Tambah Kelas">
        <form method="POST" action="{{ route('master.kelas.save') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.input label="Nama Kelas" name="nama" placeholder="Contoh: X RPL 1" />
            <x-ui.select label="Tingkat" name="tingkat">
                <option value="" disabled selected hidden>Pilih tingkat</option>
                <option value="X">X</option>
                <option value="XI">XI</option>
                <option value="XII">XII</option>
            </x-ui.select>
            <x-ui.input label="Jurusan" name="jurusan" placeholder="Contoh: RPL" />
            <x-ui.select label="Wali Kelas" name="wali_id">
                <option value="">— tanpa wali —</option>
                @foreach ($guruList as $g)
                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                @endforeach
            </x-ui.select>
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
