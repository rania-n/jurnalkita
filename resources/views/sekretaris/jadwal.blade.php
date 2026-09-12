@php
    $hariLabel = config('akademik.hari');
@endphp

<x-layouts.app title="Jadwal Pelajaran Kelas" width="wide">
    <x-page-header title="Jadwal Pelajaran" :subtitle="$kelas->nama . ' · seminggu'" />

    @if ($jadwalPerHari->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada jadwal pelajaran" desc="Hubungi admin untuk menambahkan jadwal kelas ini." />
    @else
        <div class="flex flex-col gap-6">
            @foreach ($hariLabel as $key => $label)
                @continue (! $jadwalPerHari->has($key))

                <div>
                    <h2 class="mb-2 text-sm font-bold text-ink">{{ $label }}</h2>
                    <x-ui.card-list>
                        @foreach ($jadwalPerHari[$key] as $j)
                            <x-ui.list-card
                                :title="$j->mapel->nama"
                                :meta="['JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai . ' · ' . $j->guru->nama, 'Ruang ' . ($j->ruang ?? '-')]"
                            />
                        @endforeach
                    </x-ui.card-list>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
