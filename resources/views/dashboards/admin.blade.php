@php
    $stat = [
        ['label' => 'Guru', 'value' => \App\Models\Guru::count(), 'icon' => 'groups', 'route' => 'master.index'],
        ['label' => 'Siswa', 'value' => \App\Models\Siswa::count(), 'icon' => 'school', 'route' => 'master.siswa.index'],
        ['label' => 'Kelas', 'value' => \App\Models\Kelas::count(), 'icon' => 'meeting_room', 'route' => 'master.kelas.index'],
        ['label' => 'Mapel', 'value' => \App\Models\Mapel::count(), 'icon' => 'menu_book', 'route' => 'master.mapel.index'],
    ];
    $pendingAkun = \App\Models\User::where('status', 'pending')->count();
@endphp

<x-layouts.app title="Beranda Admin" width="wide">
    <x-page-header title="Beranda Admin" subtitle="Ringkasan data sekolah" />

    @if ($pendingAkun > 0)
        <x-alert type="warning" class="mb-4">
            Ada <strong>{{ $pendingAkun }}</strong> pendaftaran akun menunggu persetujuan.
        </x-alert>
    @endif

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($stat as $s)
            <a href="{{ route($s['route']) }}" class="press flex flex-col gap-2 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon :name="$s['icon']" :size="22" />
                </span>
                <span class="text-2xl font-bold text-ink">{{ $s['value'] }}</span>
                <span class="text-xs text-muted">{{ $s['label'] }}</span>
            </a>
        @endforeach
    </div>

    <x-ui.button :href="route('master.index')" class="mt-6" icon="database">Buka Master Data</x-ui.button>
</x-layouts.app>
