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
        Saat bertugas piket, Anda bisa mengajukan dispensasi siswa lewat menu <strong>Dispensasi</strong>.
        Pengajuan diteruskan ke Waka Kesiswaan untuk disetujui.
    </x-alert>

<div class="mt-4">
    <h4 class="fw-bold mb-3">📅 Jadwal Mengajar Minggu Ini</h4>
    <div class="row g-3">
        <!-- Senin -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Senin</div>
                <div class="card-body p-2">
                    <small class="text-muted">07.30 - 10.00</small><br>
                    <strong>Bahasa Inggris — X RPL 1</strong>
                </div>
            </div>
        </div>
        <!-- Selasa -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Selasa</div>
                <div class="card-body p-2">
                    <small class="text-muted">10.00 - 12.30</small><br>
                    <strong>Bahasa Inggris — XI RPL 2</strong>
                </div>
            </div>
        </div>
        <!-- Rabu -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Rabu</div>
                <div class="card-body p-2">
                    <small class="text-muted">07.30 - 09.30</small><br>
                    <strong>Bahasa Inggris — X PPLG 1</strong>
                </div>
            </div>
        </div>
        <!-- Kamis -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Kamis</div>
                <div class="card-body p-2 text-muted">Tidak ada jadwal mengajar</div>
            </div>
        </div>
        <!-- Jumat -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Jumat</div>
                <div class="card-body p-2">
                    <small class="text-muted">08.00 - 10.00</small><br>
                    <strong>Bahasa Inggris — XI RPL 1</strong>
                </div>
            </div>
        </div>
    </div>
</div>


</x-layouts.app>


