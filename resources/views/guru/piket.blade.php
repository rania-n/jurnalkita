@php
    $guru = auth()->user()->guru;
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
    $piket = $guru?->jadwalPikets()->get()->groupBy('hari') ?? collect();
@endphp

<x-layouts.app title="Jadwal Piket Saya" width="wide">
    <x-page-header title="Jadwal Piket Saya" subtitle="Hari Anda bertugas piket" />

    @if ($piket->isEmpty())
        <x-ui.empty icon="event_busy" title="Anda tidak terjadwal piket" desc="Hubungi admin jika ada perubahan." />
    @else
        <x-ui.card-list>
            @foreach ($hariLabel as $key => $label)
                @if ($piket->has($key))
                    @foreach ($piket[$key] as $p)
                        <x-ui.list-card
                            :title="$label"
                            :meta="[($p->mulai?->format('H:i') ?? '07:00') . ' – ' . ($p->selesai?->format('H:i') ?? '12:00'), $p->keterangan ?: 'Piket harian']"
                        />
                    @endforeach
                @endif
            @endforeach
        </x-ui.card-list>
    @endif

    <x-alert type="info" class="mt-6">
        Saat bertugas piket, Anda bisa menyetujui pengajuan dispensasi lewat menu Dispensasi.
    </x-alert>
</x-layouts.app>
