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
@endphp

<x-layouts.admin title="Beranda" heading="Beranda">
    @if ($pendingAkun > 0)
        <x-alert type="warning" class="mb-6">
            Ada <strong>{{ $pendingAkun }}</strong> pendaftaran akun menunggu persetujuan.
        </x-alert>
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
</x-layouts.admin>
