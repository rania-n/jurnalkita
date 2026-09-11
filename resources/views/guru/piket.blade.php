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
        <a href="{{ route('piket.monitor.index') }}" class="press mb-4 flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="monitoring" :size="24" />
            </span>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">Monitor Piket</p>
                <p class="text-xs text-muted">Pantau kehadiran semua guru hari ini, kelas mana yang belum diisi jurnalnya</p>
            </div>
            <x-icon name="chevron_right" :size="20" class="text-muted" />
        </a>

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
        Saat bertugas piket, Anda bisa mengajukan dispensasi siswa lewat menu <strong>Dispensasi</strong>.
        Pengajuan diteruskan ke Waka Kesiswaan untuk disetujui.
    </x-alert>
</x-layouts.app>
