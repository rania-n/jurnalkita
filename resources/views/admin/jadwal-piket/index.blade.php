@php
    $hari = request('hari', 'senin');
    $q = request('cari');
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];

    $rows = \App\Models\JadwalPiket::with('guru')
        ->where('hari', $hari)
        ->when($q, fn ($b) => $b->whereHas('guru', fn ($g) => $g->where('nama', 'like', "%{$q}%")))
        ->get();

    $guruList = \App\Models\Guru::orderBy('nama')->get(['id', 'nama']);
@endphp

<x-layouts.admin title="Jadwal Piket" heading="Jadwal Piket">
    <x-admin.page title="Jadwal Piket" subtitle="Penugasan piket guru per hari">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-piket" data-modal-title="Tambah Jadwal Piket">Tambah Piket</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($hariLabel as $key => $label)
            <a href="{{ route('master.jadwal-piket.index', ['hari' => $key, 'cari' => $q]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold transition-colors', 'bg-navy text-card' => $hari === $key, 'text-muted-2 hover:text-ink' => $hari !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-admin.filters :action="route('master.jadwal-piket.index')">
        <input type="hidden" name="hari" value="{{ $hari }}">
        <x-admin.f-search placeholder="Cari nama guru..." />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada guru piket untuk {{ $hariLabel[$hari] ?? $hari }}" />
    @else
        <x-admin.table :head="['Guru', 'Jam', 'Keterangan', '']">
            @foreach ($rows as $p)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $p->guru?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $p->mulai?->format('H:i') ?? '—' }} – {{ $p->selesai?->format('H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $p->keterangan ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-piket"
                            edit-title="Ubah Jadwal Piket"
                            :edit-id="$p->id"
                            :edit-fill="['hari' => $p->hari, 'guru_id' => $p->guru_id, 'mulai' => $p->mulai?->format('H:i'), 'selesai' => $p->selesai?->format('H:i'), 'keterangan' => $p->keterangan]"
                            :delete-action="route('admin.stub')"
                            delete-confirm="Hapus jadwal piket ini?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-piket" title="Tambah Jadwal Piket">
        <form method="POST" action="{{ route('master.store', 'jadwal-piket') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.select label="Hari" name="hari">
                <option value="" disabled selected hidden>Pilih hari</option>
                @foreach ($hariLabel as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </x-ui.select>
            <x-ui.select label="Guru Piket" name="guru_id">
                <option value="" disabled selected hidden>Pilih guru</option>
                @foreach ($guruList as $g)<option value="{{ $g->id }}">{{ $g->nama }}</option>@endforeach
            </x-ui.select>
            <div class="flex gap-3">
                <x-ui.input label="Jam Mulai" name="mulai" type="time" value="07:00" class="flex-1" />
                <x-ui.input label="Jam Selesai" name="selesai" type="time" value="12:00" class="flex-1" />
            </div>
            <x-ui.input label="Keterangan (opsional)" name="keterangan" />
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
