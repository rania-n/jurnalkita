@php
    $set = request('set', 'senin_kamis');
    $sets = ['senin_kamis' => 'Senin–Kamis', 'jumat' => 'Jumat', 'khusus' => 'Khusus'];
    $rows = \App\Models\JamPelajaran::where('kategori', $set)->orderBy('jam_ke')->get();
@endphp

<x-layouts.admin title="Jam Pelajaran" heading="Jam Pelajaran">
    <x-admin.page title="Jam Pelajaran" subtitle="Rentang waktu tiap jam pelajaran">
        <x-slot:action>
            <x-ui.button :href="route('master.jam-pelajaran.edit', ['set' => $set])" icon="edit">Edit</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($sets as $key => $label)
            <a href="{{ route('master.jam-pelajaran.index', ['set' => $key]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold transition-colors', 'bg-navy text-card' => $set === $key, 'text-muted-2 hover:text-ink' => $set !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada konfigurasi jam untuk kategori ini" />
    @else
        <x-admin.table :head="['Jam ke-', 'Mulai', 'Selesai', 'Keterangan']">
            @foreach ($rows as $jp)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $jp->jam_ke }}</td>
                    <td class="px-4 py-3 text-muted">{{ $jp->mulai?->format('H:i') }}</td>
                    <td class="px-4 py-3 text-muted">{{ $jp->selesai?->format('H:i') }}</td>
                    <td class="px-4 py-3 text-muted">{{ $jp->keterangan ?: '—' }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
