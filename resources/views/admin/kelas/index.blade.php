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
        ->orderedByHierarchy()
        ->get();

    $guruList = \App\Models\Guru::orderBy('nama')->get(['id', 'nama']);
    $kelasAktifList = \App\Models\Kelas::aktif()->orderedByHierarchy()->get(['id', 'nama']);
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

    @if ($kelasAktifList->isNotEmpty())
        <section class="mb-5 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)] sm:p-5">
            <h2 class="text-base font-bold text-ink">Ubah Status PKL Beberapa Kelas</h2>
            <p class="mt-1 text-sm text-muted">Pilih beberapa kelas aktif sekaligus. Kelas arsip tidak ikut diubah.</p>

            <form method="POST" action="{{ route('master.kelas.status-massal') }}" class="mt-4 grid gap-4 lg:grid-cols-2" data-confirm="Ubah status kelas yang dipilih?">
                @csrf
                @method('PATCH')
                <div>
                    <x-ui.cari-checkbox
                        label="Kelas yang diubah"
                        name="kelas_ids"
                        :options="$kelasAktifList"
                        hint="Cari nama kelas, lalu centang satu atau beberapa kelas."
                    />
                </div>
                <div class="flex flex-col justify-between gap-4">
                    <x-ui.choice
                        label="Status baru"
                        name="status"
                        :options="['pkl' => 'PKL', 'aktif' => 'Aktif']"
                        value="pkl"
                        required
                    />
                    <x-ui.button type="submit" icon="save" class="w-full sm:w-auto">Terapkan ke Kelas Terpilih</x-ui.button>
                </div>
            </form>

            <div class="mt-4 flex flex-col gap-3 border-t border-surface-alt pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted">Jadikan semua kelas XII pada tahun ajaran aktif berstatus PKL.</p>
                <form method="POST" action="{{ route('master.kelas.status-massal') }}" data-confirm="Jadikan semua kelas XII tahun ajaran aktif sebagai PKL?">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="tingkat" value="XII">
                    <input type="hidden" name="status" value="pkl">
                    <x-ui.button type="submit" variant="secondary" icon="school" class="w-full sm:w-auto">Semua Kelas XII → PKL</x-ui.button>
                </form>
            </div>
        </section>
    @endif

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada kelas yang cocok" />
    @else
        <x-admin.table :head="['Nama', 'Tingkat', 'Jurusan', 'Jml Siswa', 'Wali Kelas', '']">
            @foreach ($rows as $k)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">
                        {{ $k->nama }}
                        @if ($k->pkl())
                            <x-ui.status-badge status="pkl" class="ml-1.5">PKL</x-ui.status-badge>
                        @endif
                    </td>
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
                            :edit-fill="['tingkat' => $k->tingkat, 'jurusan' => $k->jurusan, 'nomor' => $k->nomor, 'wali_id' => $k->wali_id, 'status' => $k->status]"
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
                <x-ui.choice label="Tingkat" name="tingkat" :options="['X' => 'X', 'XI' => 'XI', 'XII' => 'XII']" required />
                <x-ui.input label="Nomor" name="nomor" type="number" min="1" placeholder="1" class="w-24" />
            </div>

            <x-ui.choice
                label="Jurusan"
                name="jurusan"
                :options="collect($jurusanList)->mapWithKeys(fn ($nama, $kode) => [$kode => $kode])->all()"
                required
            />

            <x-ui.cari-pilihan
                label="Wali Kelas"
                name="wali_id"
                :options="$guruList"
                placeholder="Ketik nama guru..."
                required
            />

            <x-ui.choice
                label="Status"
                name="status"
                :options="['aktif' => 'Aktif', 'pkl' => 'PKL']"
                value="aktif"
                required
            />
            <p class="-mt-3 text-xs text-muted-2">Kelas XII pada tahun ajaran aktif otomatis berstatus PKL. Untuk kelas XI yang PKL, pilih PKL secara manual atau lewat ubah status massal.</p>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
