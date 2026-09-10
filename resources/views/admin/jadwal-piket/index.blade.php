@php
    $hari = request('hari', 'senin');
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
    $rows = \App\Models\JadwalPiket::with('guru')->where('hari', $hari)->get();
@endphp

<x-layouts.admin title="Jadwal Piket" heading="Jadwal Piket">
    <x-admin.page title="Jadwal Piket" subtitle="Penugasan piket guru per hari">
        <x-slot:action>
            <x-ui.button :href="route('master.jadwal-piket.create')" icon="add">Tambah Piket</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($hariLabel as $key => $label)
            <a href="{{ route('master.jadwal-piket.index', ['hari' => $key]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold transition-colors', 'bg-navy text-card' => $hari === $key, 'text-muted-2 hover:text-ink' => $hari !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

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
                        <x-admin.row-actions :edit="route('master.jadwal-piket.create')" :delete-action="route('admin.stub')" delete-confirm="Hapus jadwal piket ini?" />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
