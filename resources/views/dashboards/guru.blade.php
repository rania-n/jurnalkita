@php
    $guru = auth()->user()->guru;
    $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? null;
    $jadwalHariIni = $guru?->jadwals()->with(['kelas', 'mapel'])->where('hari', $hari)->orderBy('jam_ke_mulai')->get() ?? collect();
    $piketHariIni = $guru?->jadwalPikets()->where('hari', $hari)->exists();
@endphp

<x-layouts.app title="Beranda Guru" width="wide">
    <x-page-header title="Beranda" :subtitle="'Selamat mengajar, ' . (auth()->user()->name)" />

    @if ($piketHariIni)
        <x-alert type="info" class="mb-4">Anda bertugas <strong>piket</strong> hari ini.</x-alert>
    @endif

    <h2 class="mb-3 text-sm font-bold text-ink">Jadwal Mengajar Hari Ini</h2>

    @if ($jadwalHariIni->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal hari ini" />
    @else
        <x-ui.card-list>
            @foreach ($jadwalHariIni as $j)
                <x-ui.list-card
                    :title="$j->mapel->nama"
                    :meta="[$j->kelas->nama . ' · Jam ke-' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai, 'Ruang ' . ($j->ruang ?? '-')]"
                >
                    <x-slot:actions>
                        <x-ui.action-button label="Isi Jurnal" icon="edit_note" variant="info" :href="route('jurnal.create')" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    @endif
</x-layouts.app>
