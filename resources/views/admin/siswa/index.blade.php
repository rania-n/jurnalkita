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

    $kelasList = \App\Models\Kelas::orderBy('nama')->get(['id', 'nama']);
@endphp

<x-layouts.admin title="Data Siswa" heading="Data Siswa">
    <x-admin.page title="Data Siswa" subtitle="{{ $rows->total() }} siswa">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-siswa" data-modal-title="Tambah Siswa">Tambah Siswa</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.siswa.index')">
        <x-admin.f-search placeholder="Nama atau NIS..." />
        <x-admin.f-select name="kelas" label="Kelas" :options="$kelasList->pluck('nama', 'id')" all="Semua Kelas" />
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
                        <x-admin.row-actions
                            edit-modal="modal-siswa"
                            edit-title="Ubah Siswa"
                            :edit-id="$s->id"
                            :edit-fill="['nis' => $s->nis, 'nama' => $s->nama, 'kelas_id' => $s->kelas_id, 'no_absen' => $s->no_absen, 'jenis_kelamin' => $s->jenis_kelamin, 'jabatan' => $s->jabatan]"
                            :delete-action="route('master.siswa.destroy', $s)"
                            delete-confirm="Yakin hapus {{ $s->nama }}?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <div class="mt-4">{{ $rows->links() }}</div>
    @endif

    <x-admin.modal id="modal-siswa" title="Tambah Siswa">
        <form method="POST" action="{{ route('master.siswa.save') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.select label="Kelas" name="kelas_id">
                <option value="" disabled selected hidden>Pilih kelas</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input label="NIS" name="nis" inputmode="numeric" />
            <x-ui.input label="Nama Lengkap" name="nama" />
            <x-ui.input label="Nomor Presensi" name="no_absen" type="number" min="1" />
            <x-ui.select label="Jenis Kelamin" name="jenis_kelamin">
                <option value="" disabled selected hidden>Pilih</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </x-ui.select>
            <x-ui.select label="Jabatan Kelas" name="jabatan">
                <option value="anggota">Anggota</option>
                <option value="pengurus">Pengurus Kelas</option>
            </x-ui.select>
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
