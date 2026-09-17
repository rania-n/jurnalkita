@php
    $hariLabel = config('akademik.hari');
    $tabsHari = ['semua' => 'Semua'] + $hariLabel;
@endphp

<x-layouts.app title="Jadwal Mengajar Saya" width="wide">
    <x-page-header title="Jadwal Mengajar Saya" subtitle="Jadwal mengajar & piket Anda seminggu" />

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-lg border border-surface-alt bg-card p-1">
        @foreach ($tabsHari as $key => $label)
            <a href="{{ route('guru.jadwal.index', $key === 'semua' ? [] : ['hari' => $key]) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $hari === $key, 'text-muted-2 hover:text-ink' => $hari !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($jadwalPerHari->isEmpty() && $piketPerHari->isEmpty() && $jadwalWakaPerHari->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada jadwal" desc="Hubungi admin untuk menambahkan jadwal Anda." />
    @else
        <div class="flex flex-col gap-6">
            @foreach ($hariLabel as $key => $label)
                @continue (! $jadwalPerHari->has($key) && ! $piketPerHari->has($key) && ! $jadwalWakaPerHari->has($key))

                <div>
                    <h2 class="mb-2 text-sm font-bold text-ink">{{ $label }}</h2>
                    <x-ui.card-list class="grid-fill-last">
                        @if ($jadwalWakaPerHari->has($key))
                            <x-ui.list-card
                                title="Piket Waka Kesiswaan"
                                :meta="['Sepanjang hari', 'Standby konfirmasi dispensasi']"
                            >
                                <x-slot:badge>
                                    <x-ui.status-badge status="izin">Piket</x-ui.status-badge>
                                </x-slot:badge>
                            </x-ui.list-card>
                        @endif

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
