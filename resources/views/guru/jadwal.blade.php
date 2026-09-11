@php
    $hariLabel = config('akademik.hari');
@endphp

<x-layouts.app title="Jadwal Mengajar Saya" width="wide">
    <x-page-header title="Jadwal Mengajar Saya" subtitle="Jadwal mengajar Anda seminggu" />

    @if ($jadwalPerHari->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada jadwal mengajar" desc="Hubungi admin untuk menambahkan jadwal Anda." />
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
                                :meta="[$j->kelas->nama . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai, 'Ruang ' . ($j->ruang ?? '-')]"
                            />
                        @endforeach
                    </x-ui.card-list>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
