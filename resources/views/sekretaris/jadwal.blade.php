@php
    $hariLabel = config('akademik.hari');
    $tabsHari = ['semua' => 'Semua'] + $hariLabel;
    $labelStatusGuru = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tugas'];
    $toneStatusGuru = ['hadir' => 'hadir', 'tidak_hadir' => 'tugas'];

    // Hari ini ditaruh paling atas (biar langsung kelihatan tanpa scroll),
    // sisanya tetap urut Senin-Jumat kayak biasa -- sama pola kayak
    // Guru\JadwalController.
    $urutanHari = $hariIni && isset($hariLabel[$hariIni])
        ? [$hariIni => $hariLabel[$hariIni]] + $hariLabel
        : $hariLabel;
@endphp

<x-layouts.app title="Jadwal Pelajaran Kelas" width="wide">
    <x-page-header title="Jadwal Pelajaran" :subtitle="$kelas->nama . ' · seminggu'" />

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabsHari as $key => $label)
            <a href="{{ route('sekretaris.kelas.jadwal', $key === 'semua' ? [] : ['hari' => $key]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $hari === $key, 'text-muted-2 hover:text-ink' => $hari !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($jadwalPerHari->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada jadwal pelajaran" desc="Hubungi admin untuk menambahkan jadwal kelas ini." />
    @else
        <div class="flex flex-col gap-6">
            @foreach ($urutanHari as $key => $label)
                @continue (! $jadwalPerHari->has($key))

                <div>
                    <h2 class="mb-2 flex items-center gap-2 text-sm font-bold text-ink">
                        {{ $label }}
                        @if ($key === $hariIni)
                            <span class="rounded-md bg-navy px-1.5 py-0.5 text-[10px] font-bold text-card">Hari Ini</span>
                        @endif
                    </h2>
                    <x-ui.card-list class="grid-fill-last">
                        @foreach ($jadwalPerHari[$key] as $j)
                            @php $jr = $key === $hariIni ? $jurnalHariIni->get($j->id) : null; @endphp
                            <x-ui.list-card
                                :title="$j->mapel->nama"
                                :meta="['JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai . ' · ' . $j->guru->nama, 'Ruang ' . ($j->ruang ?? '-')]"
                            >
                                @if ($key === $hariIni)
                                    <x-slot:badge>
                                        @if ($jr)
                                            <x-ui.status-badge :status="$toneStatusGuru[$jr->status_guru] ?? 'menunggu'">
                                                {{ $labelStatusGuru[$jr->status_guru] ?? $jr->status_guru }}
                                            </x-ui.status-badge>
                                        @else
                                            <x-ui.status-badge status="menunggu">Belum Diisi</x-ui.status-badge>
                                        @endif
                                    </x-slot:badge>
                                @endif
                            </x-ui.list-card>
                        @endforeach
                    </x-ui.card-list>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
