@php
    $hariLabel = config('akademik.hari');
@endphp

<x-layouts.app title="Jadwal Mengajar Saya" width="wide">
    <x-page-header title="Jadwal Mengajar Saya" subtitle="Jadwal mengajar & piket Anda seminggu" />

    @if ($jadwalPerHari->isEmpty() && $piketPerHari->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada jadwal" desc="Hubungi admin untuk menambahkan jadwal Anda." />
    @else
        <div class="flex flex-col gap-6">
            @foreach ($hariLabel as $key => $label)
                @continue (! $jadwalPerHari->has($key) && ! $piketPerHari->has($key))

                <div>
                    <h2 class="mb-2 text-sm font-bold text-ink">{{ $label }}</h2>
                    <x-ui.card-list>
                        @if ($piketPerHari->has($key))
                            @foreach ($piketPerHari[$key] as $p)
                                <x-ui.list-card
                                    title="Piket Harian"
                                    :meta="[$p->mulai?->format('H:i') . '–' . $p->selesai?->format('H:i'), $p->keterangan ?: 'Jaga piket sekolah']"
                                >
                                    <x-slot:badge>
                                        <x-ui.status-badge status="izin">Piket</x-ui.status-badge>
                                    </x-slot:badge>
                                </x-ui.list-card>
                            @endforeach
                        @endif

                        @if ($jadwalPerHari->has($key))
                            @foreach ($jadwalPerHari[$key] as $j)
                                <x-ui.list-card
                                    :title="$j->mapel->nama"
                                    :meta="[$j->kelas->nama . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai, 'Ruang ' . ($j->ruang ?? '-')]"
                                />
                            @endforeach
                        @endif
                    </x-ui.card-list>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
