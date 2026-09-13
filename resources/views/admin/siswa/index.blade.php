@php
    // Pakai request()->query(...), BUKAN request('status') -- Route::view() ini
    // menyimpan parameter aksi internalnya sendiri (view/data/status/headers) di
    // route parameters, dan request('status') (helper __get) jatuh balik ke situ
    // kalau tidak ada di query string. Akibatnya $status selalu ke-isi 200 (kode
    // HTTP default Route::view()), bikin filter WHERE status=200 selalu aktif dan
    // tabel selalu kosong. request()->query('status') tidak punya fallback itu.
    $q = request()->query('cari');
    $kelasId = request()->query('kelas');
    $jk = request()->query('jk');
    $status = request()->query('status');

    $rows = \App\Models\Siswa::with('kelas')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%")))
        ->when($kelasId, fn ($b) => $b->where('kelas_id', $kelasId))
        ->when($jk, fn ($b) => $b->where('jenis_kelamin', $jk))
        ->when($status, fn ($b) => $b->where('status', $status))
        ->orderBy('nama')
        ->paginate(20)->withQueryString();

    $kelasList = \App\Models\Kelas::orderBy('nama')->get(['id', 'nama']);
    // Dropdown tambah/ubah: kelas tahun ajaran aktif + kelas siswa yang lagi ditampilkan
    // (supaya siswa yang sudah lulus/pindah tetap kepilih saat modal Ubah dibuka).
    $kelasAktifList = \App\Models\Kelas::aktif()->orderBy('nama')->get(['id', 'nama'])
        ->concat($rows->pluck('kelas')->filter())
        ->unique('id')->sortBy('nama')->values();
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
        <x-admin.f-select name="status" label="Status" :options="['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah']" all="Semua Status" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada siswa yang cocok" />
    @else
        <x-admin.table :head="['NIS', 'Nama', 'Kelas', 'L/P', 'Jabatan', 'Status', '']">
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
                        @if ($s->status === 'aktif')
                            <span class="text-muted">Aktif</span>
                        @elseif ($s->status === 'lulus')
                            <x-ui.status-badge status="hadir">Lulus</x-ui.status-badge>
                        @else
                            <x-ui.status-badge status="alpha">Pindah</x-ui.status-badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-siswa"
                            edit-title="Ubah Siswa"
                            :edit-id="$s->id"
                            :edit-fill="['nis' => $s->nis, 'nama' => $s->nama, 'kelas_id' => $s->kelas_id, 'no_absen' => $s->no_absen, 'jenis_kelamin' => $s->jenis_kelamin, 'jabatan' => $s->jabatan, 'status' => $s->status]"
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
            <p class="text-xs text-muted-2">
                Data siswa saja. Akun login (khusus pengurus kelas) + No. WhatsApp
                diisi lewat menu "Manajemen Akun → Buat Akun".
            </p>
            <x-ui.select label="Kelas" name="kelas_id">
                <option value="" disabled selected hidden>Pilih kelas</option>
                @foreach ($kelasAktifList as $k)
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
            <x-ui.select label="Status" name="status">
                <option value="aktif">Aktif</option>
                <option value="lulus">Lulus</option>
                <option value="pindah">Pindah</option>
            </x-ui.select>
            <p class="-mt-2 text-xs text-muted-2">
                Biasanya biarkan "Aktif" — "Lulus" otomatis diisi lewat Kenaikan Kelas,
                "Pindah" dipakai kalau siswa pindah sekolah.
            </p>
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
