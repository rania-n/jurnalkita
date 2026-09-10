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

<x-layouts.app title="Edit Jam Pelajaran">
    <x-page-header title="Edit Jam Pelajaran" subtitle="Konfigurasi rentang waktu jam pelajaran" :back="route('master.jam-pelajaran.index', ['set' => $set])" />

    <form method="POST" action="{{ route('master.store', 'jam-pelajaran') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.tabs :tabs="collect($sets)->map(fn ($label, $value) => [
            'label' => $label,
            'url' => route('master.jam-pelajaran.edit', ['set' => $value]),
            'active' => $set === $value,
        ])->values()->all()" />

        <div class="rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <div class="grid grid-cols-[2.5rem_1fr_1fr_1.75rem] gap-x-2 border-b border-surface-alt pb-2 text-[11px] font-bold uppercase tracking-wide text-muted">
                <span>JP</span><span>Mulai</span><span>Selesai</span><span></span>
            </div>
            @foreach ($jp as $i => $row)
                <div class="grid grid-cols-[2.5rem_1fr_1fr_1.75rem] items-center gap-x-2 py-2">
                    <span class="text-sm font-bold text-ink">{{ $row['jp'] }}</span>
                    <input type="time" name="mulai[{{ $i }}]" value="{{ $row['mulai'] }}" class="w-full rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm text-ink outline-none focus:border-navy">
                    <input type="time" name="selesai[{{ $i }}]" value="{{ $row['selesai'] }}" class="w-full rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm text-ink outline-none focus:border-navy">
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-alpha-soft text-alpha" aria-label="Hapus baris" data-confirm="Hapus jam pelajaran ini?">
                        <x-icon name="delete" :size="16" />
                    </button>
                </div>
            @endforeach

            <button type="button" class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-[13px] font-bold text-izin">
                <x-icon name="add" :size="18" /> Tambah Jam Pelajaran
            </button>
        </div>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan Perubahan</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
