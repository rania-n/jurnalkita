@php
    $q = request()->query('cari');
    $tingkat = request()->query('tingkat');
    $jurusan = request()->query('jurusan');
    $jurusanList = config('akademik.jurusan');

    $rows = \App\Models\Kelas::with('wali')->withCount('siswas')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w
            ->where('nama', 'like', "%{$q}%")
            ->orWhere('jurusan', 'like', "%{$q}%")
            ->orWhereHas('wali', fn ($g) => $g->where('nama', 'like', "%{$q}%"))
        ))
        ->when($tingkat, fn ($b) => $b->where('tingkat', $tingkat))
        ->when($jurusan, fn ($b) => $b->where('jurusan', $jurusan))
        ->orderBy('tingkat')->orderBy('jurusan')->orderBy('nomor')
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
        <x-admin.f-search placeholder="Nama kelas, jurusan, atau wali kelas..." />
        <x-admin.f-select name="tingkat" label="Tingkat" :options="['X' => 'X', 'XI' => 'XI', 'XII' => 'XII']" all="Semua Tingkat" />
        <x-admin.f-select name="jurusan" label="Jurusan" :options="$jurusanList" all="Semua Jurusan" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada kelas yang cocok" />
    @else
        <x-admin.table :head="['Nama', 'Tingkat', 'Jurusan', 'Jml Siswa', 'Wali Kelas', '']">
            @foreach ($rows as $k)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $k->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->tingkat }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->jurusanNama() }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->siswas_count }}</td>
                    <td class="px-4 py-3 text-muted">{{ $k->wali?->nama ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            :detail="route('master.kelas.show', $k)"
                            edit-modal="modal-kelas"
                            edit-title="Ubah Kelas"
                            :edit-id="$k->id"
                            :edit-fill="['tingkat' => $k->tingkat, 'jurusan' => $k->jurusan, 'nomor' => $k->nomor, 'wali_id' => $k->wali_id]"
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
            <p class="text-xs text-muted-2">Nama kelas dibuat otomatis, contoh: <strong>X RPL 1</strong>.</p>

            <div class="flex gap-3">
                <x-ui.select label="Tingkat" name="tingkat" class="flex-1" required>
                    <option value="" disabled selected hidden>Pilih</option>
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                </x-ui.select>
                <x-ui.input label="Nomor" name="nomor" type="number" min="1" placeholder="1" class="w-24" />
            </div>

            <x-ui.select label="Jurusan" name="jurusan" required>
                <option value="" disabled selected hidden>Pilih jurusan</option>
                @foreach ($jurusanList as $kode => $nama)
                    <option value="{{ $kode }}">{{ $kode }} — {{ $nama }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select label="Wali Kelas" name="wali_id">
                <option value="">— tanpa wali —</option>
                @foreach ($guruList as $g)
                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                @endforeach
            </x-ui.select>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
