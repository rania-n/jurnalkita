@php
    $q = request('cari');
    $mapelId = request('mapel');

    $rows = \App\Models\Guru::with('mapelUtama', 'mapels', 'user')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%")))
        ->when($mapelId, fn ($b) => $b->where(fn ($w) => $w->where('mapel_utama_id', $mapelId)->orWhereHas('mapels', fn ($m) => $m->where('mapels.id', $mapelId))))
        ->orderBy('nama')
        ->get();

    $mapelList = \App\Models\Mapel::orderBy('nama')->get(['id', 'nama']);
@endphp

<x-layouts.admin title="Data Guru" heading="Data Guru">
    <x-admin.page title="Data Guru" subtitle="{{ $rows->count() }} guru">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-guru" data-modal-title="Tambah Guru">Tambah Guru</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.guru.index')">
        <x-admin.f-search placeholder="Nama atau NIP..." />
        <x-ui.cari-pilihan name="mapel" label="Mata Pelajaran" :options="$mapelList" all="Semua Mapel" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada guru yang cocok" />
    @else
        <x-admin.table :head="['Nama', 'NIP', 'Mapel Utama', 'Mapel Tambahan', 'Akun', '']">
            @foreach ($rows as $g)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $g->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $g->nip ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $g->mapelUtama?->nama ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $g->mapels->pluck('nama')->join(', ') ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.status-badge :status="$g->user_id ? 'disetujui' : 'menunggu'">{{ $g->user_id ? 'Ada' : 'Belum' }}</x-ui.status-badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            :detail="route('master.guru.show', $g)"
                            edit-modal="modal-guru"
                            edit-title="Ubah Guru"
                            :edit-id="$g->id"
                            :edit-fill="[
                                'nama' => $g->nama,
                                'nip' => $g->nip,
                                'mapel_utama_id' => $g->mapel_utama_id,
                                'mapel_tambahan' => $g->mapels->pluck('id'),
                            ]"
                            :delete-action="route('master.guru.destroy', $g)"
                            delete-confirm="Yakin hapus data guru {{ $g->nama }}?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-guru" title="Tambah Guru">
        <form method="POST" action="{{ route('master.guru.save') }}" class="flex flex-col gap-4">
            @csrf
            <p class="text-xs text-muted-2">
                Data guru saja. Akun login + No. WhatsApp diisi lewat menu "Manajemen Akun → Buat Akun".
            </p>
            <x-ui.input label="Nama Lengkap" name="nama" required />
            <x-ui.input label="NIP (opsional)" name="nip" />

            <x-ui.cari-pilihan
                label="Mapel Utama"
                name="mapel_utama_id"
                :options="$mapelList"
                placeholder="Ketik nama mapel... (opsional)"
            />

            <x-ui.cari-checkbox
                label="Mapel Tambahan (jika mengajar lebih dari 1)"
                name="mapel_tambahan"
                :options="$mapelList"
            />

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
