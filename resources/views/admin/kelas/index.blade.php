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
    <x-admin.page title="Data Kelas" :subtitle="$rows->count() . ' kelas'">
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
        <section class="mb-5 rounded-2xl bg-card shadow-[var(--shadow-soft)]">
            {{-- Header section --}}
            <div class="border-b border-surface-alt px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-navy/10">
                        <x-icon name="school" :size="18" class="text-navy" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-ink">Ubah Status PKL Kelas</h2>
                        <p class="text-sm text-muted">Pilih satu atau beberapa kelas aktif lalu terapkan status PKL.</p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                {{-- Form: pilih kelas + status --}}
                {{-- data-confirm SENGAJA dipasang di TOMBOLnya, bukan di <form> --
                     dulu nempel di <form>, akibatnya konfirmasi muncul di SETIAP
                     klik di dalam form ini (klik kotak cari, checkbox, radio pun
                     kena), bukan cuma pas klik submit. --}}
                <form method="POST" action="{{ route('master.kelas.status-massal') }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PATCH')

                    {{-- Langkah 1: pilih kelas --}}
                    <div>
                        <p class="mb-2 text-sm font-semibold text-ink">1. Pilih kelas yang ingin diubah</p>
                        <x-ui.cari-checkbox
                            name="kelas_ids"
                            :options="$kelasAktifList"
                            required
                        />
                    </div>

                    {{-- Langkah 2: pilih status + tombol --}}
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <p class="mb-2 text-sm font-semibold text-ink">2. Status baru</p>
                            <x-ui.choice
                                name="status"
                                :options="['pkl' => 'PKL', 'aktif' => 'Bukan PKL']"
                                value="pkl"
                                required
                            />
                        </div>
                        <x-ui.button
                            type="submit"
                            icon="save"
                            class="w-full sm:w-auto"
                            data-confirm="Ubah status kelas yang dipilih?"
                        >Terapkan ke Kelas Terpilih</x-ui.button>
                    </div>
                </form>

                {{-- Shortcut: semua kelas XII → PKL --}}
                <div class="mt-5 flex flex-col gap-3 rounded-xl border border-surface-alt bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-ink">Jadikan semua kelas XII menjadi PKL</p>
                        <p class="mt-0.5 text-xs text-muted">Berlaku untuk semua kelas XII pada tahun ajaran aktif.</p>
                    </div>
                    <form method="POST" action="{{ route('master.kelas.status-massal') }}" class="shrink-0">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="tingkat" value="XII">
                        <input type="hidden" name="status" value="pkl">
                        <x-ui.button
                            type="submit"
                            variant="secondary"
                            icon="school"
                            class="w-full sm:w-auto"
                            data-confirm="Jadikan semua kelas XII tahun ajaran aktif sebagai PKL?"
                        >Semua Kelas XII → PKL</x-ui.button>
                    </form>
                </div>
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
                    <td class="px-4 py-3 text-muted">
                        @if ($k->wali)
                            <a href="{{ route('master.guru.show', $k->wali) }}" class="text-navy hover:underline">{{ $k->wali->nama }}</a>
                        @else — @endif
                    </td>
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
