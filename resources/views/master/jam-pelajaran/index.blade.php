@php
    $set = request('set', 'senin-kamis');
    $sets = ['senin-kamis' => 'Senin-Kamis', 'jumat' => 'Jumat', 'kustom' => 'Kustom'];
    $jp = [
        ['jp' => 'JP 1', 'mulai' => '07:00', 'selesai' => '07:45'],
        ['jp' => 'JP 2', 'mulai' => '07:45', 'selesai' => '08:30'],
        ['jp' => 'JP 3', 'mulai' => '08:30', 'selesai' => '09:15'],
        ['jp' => 'JP 4', 'mulai' => '09:30', 'selesai' => '10:15'],
        ['jp' => 'JP 5', 'mulai' => '10:15', 'selesai' => '11:00'],
        ['jp' => 'JP 6', 'mulai' => '11:00', 'selesai' => '11:45'],
    ];
@endphp

<x-layouts.app title="Jam Pelajaran">
    <x-page-header title="Jam Pelajaran" subtitle="Konfigurasi rentang waktu jam pelajaran" :back="route('master.index')" />

    <div class="flex flex-col gap-4">
        <x-ui.tabs :tabs="collect($sets)->map(fn ($label, $value) => [
            'label' => $label,
            'url' => route('master.jam-pelajaran.index', ['set' => $value]),
            'active' => $set === $value,
        ])->values()->all()" />

        <div class="rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <div class="grid grid-cols-[3rem_1fr_1fr] gap-x-3 border-b border-surface-alt pb-2 text-[11px] font-bold uppercase tracking-wide text-muted">
                <span>JP</span><span>Jam Mulai</span><span>Jam Selesai</span>
            </div>
            @foreach ($jp as $row)
                <div class="grid grid-cols-[3rem_1fr_1fr] items-center gap-x-3 py-2 text-sm">
                    <span class="font-bold text-ink">{{ $row['jp'] }}</span>
                    <span class="rounded-lg bg-surface px-3 py-2 text-muted">{{ $row['mulai'] }}</span>
                    <span class="rounded-lg bg-surface px-3 py-2 text-muted">{{ $row['selesai'] }}</span>
                </div>
            @endforeach
        </div>

        <x-ui.sticky-bar>
            <x-ui.button :href="route('master.jam-pelajaran.edit', ['set' => $set])" block icon="edit">Edit Jam Pelajaran</x-ui.button>
        </x-ui.sticky-bar>
    </div>
</x-layouts.app>
