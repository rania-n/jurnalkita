@php
    $stat = [
        ['label' => 'Guru', 'value' => \App\Models\Guru::count(), 'icon' => 'groups', 'route' => 'master.guru.index'],
        ['label' => 'Siswa', 'value' => \App\Models\Siswa::count(), 'icon' => 'school', 'route' => 'master.siswa.index'],
        ['label' => 'Kelas', 'value' => \App\Models\Kelas::count(), 'icon' => 'meeting_room', 'route' => 'master.kelas.index'],
        ['label' => 'Mata Pelajaran', 'value' => \App\Models\Mapel::count(), 'icon' => 'menu_book', 'route' => 'master.mapel.index'],
        ['label' => 'Jadwal Pelajaran', 'value' => \App\Models\Jadwal::count(), 'icon' => 'calendar_month', 'route' => 'master.jadwal-pelajaran.index'],
        ['label' => 'Jadwal Piket', 'value' => \App\Models\JadwalPiket::count(), 'icon' => 'event_available', 'route' => 'master.jadwal-piket.index'],
    ];
    $pendingAkun = \App\Models\User::where('status', 'pending')->count();

    // Oversight kesiswaan — sekadar ringkasan, aksinya (approve/tolak) tetap punya piket/waka.
    $dispensasiMenunggu = \App\Models\Dispensasi::where('status_piket', 'approved')
        ->where('status_waka', 'pending')->count();

    $siswaAlphaTinggi = \App\Models\Absensi::where('status', 'alpha')
        ->whereHas('jurnal', fn ($q) => $q->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year))
        ->get()->countBy('siswa_id')->filter(fn ($n) => $n >= 3)->count();
@endphp

<x-layouts.admin title="Beranda" heading="Beranda">
    @if ($pendingAkun > 0)
        <a href="{{ route('master.akun.index', ['tab' => 'pending']) }}" class="mb-6 block">
            <x-alert type="warning">
                Ada <strong>{{ $pendingAkun }}</strong> pendaftaran akun menunggu persetujuan — klik untuk tinjau.
            </x-alert>
        </a>
    @endif

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
        @foreach ($stat as $s)
            <a href="{{ route($s['route']) }}" class="press flex flex-col gap-3 rounded-xl border border-surface-alt bg-card p-5 hover:border-navy">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon :name="$s['icon']" :size="22" />
                </span>
                <span class="text-3xl font-bold text-ink">{{ $s['value'] }}</span>
                <span class="text-sm text-muted">{{ $s['label'] }}</span>
            </a>
        @endforeach
    </div>

    <h2 class="mb-3 mt-8 text-sm font-bold text-ink">Kesiswaan Hari Ini</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('dispensasi.index') }}" class="press flex flex-col gap-3 rounded-xl border border-surface-alt bg-card p-5 hover:border-navy">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="fact_check" :size="22" />
            </span>
            <span class="text-3xl font-bold text-ink">{{ $dispensasiMenunggu }}</span>
            <span class="text-sm text-muted">Dispensasi menunggu Waka</span>
        </a>

        <a href="{{ route('piket.monitor.index') }}" class="press flex flex-col gap-3 rounded-xl border border-surface-alt bg-card p-5 hover:border-navy">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="monitoring" :size="22" />
            </span>
            <span class="text-3xl font-bold text-ink">→</span>
            <span class="text-sm text-muted">Monitor Piket hari ini</span>
        </a>

        <a href="{{ route('rekap.siswa.index') }}" class="press flex flex-col gap-3 rounded-xl border border-surface-alt bg-card p-5 hover:border-navy">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="bar_chart" :size="22" />
            </span>
            <span class="text-3xl font-bold text-ink">{{ $siswaAlphaTinggi }}</span>
            <span class="text-sm text-muted">Siswa alpha ≥3x bulan ini</span>
        </a>
    </div>
</x-layouts.admin>
