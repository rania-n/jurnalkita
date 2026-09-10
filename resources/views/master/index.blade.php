@php
    $menu = [
        ['label' => 'Data Guru', 'desc' => '45 guru terdaftar aktif', 'icon' => 'groups', 'count' => '45', 'route' => 'piket.guru.index'],
        ['label' => 'Data Kelas', 'desc' => '36 kelas aktif semester ini', 'icon' => 'meeting_room', 'count' => '36', 'route' => 'master.kelas.index'],
        ['label' => 'Data Siswa', 'desc' => '1.260 siswa terdaftar', 'icon' => 'school', 'count' => '1.2k', 'route' => 'master.siswa.index'],
        ['label' => 'Mata Pelajaran', 'desc' => 'Kelola daftar mata pelajaran', 'icon' => 'menu_book', 'count' => null, 'route' => 'master.mapel.index'],
        ['label' => 'Jadwal Pelajaran', 'desc' => 'Konfigurasi jadwal pelajaran', 'icon' => 'calendar_month', 'count' => '24', 'route' => 'master.jadwal-pelajaran.index'],
        ['label' => 'Jadwal Piket', 'desc' => 'Konfigurasi penugasan harian', 'icon' => 'event_available', 'count' => null, 'route' => 'piket.jadwal.index'],
        ['label' => 'Jam Pelajaran', 'desc' => 'Konfigurasi rentang waktu jam pelajaran', 'icon' => 'schedule', 'count' => null, 'route' => 'master.jam-pelajaran.index'],
    ];
@endphp

<x-layouts.app title="Master Data" menu="default" width="wide">
    <x-page-header title="Master Data" subtitle="Manajemen Database Sekolah" />

    <div class="flex flex-col gap-3 lg:grid lg:grid-cols-2">
        @foreach ($menu as $m)
            <a href="{{ route($m['route']) }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-card)] transition-colors hover:bg-surface-alt/30">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon :name="$m['icon']" :size="24" />
                </span>
                <div class="flex min-w-0 flex-1 flex-col">
                    <span class="text-[15px] font-bold text-ink">{{ $m['label'] }}</span>
                    <span class="truncate text-xs text-muted">{{ $m['desc'] }}</span>
                </div>
                @if ($m['count'])
                    <span class="shrink-0 rounded-md bg-surface-alt px-2 py-1 text-[11px] font-bold text-ink">{{ $m['count'] }}</span>
                @endif
                <x-icon name="chevron_right" :size="20" class="shrink-0 text-ink" />
            </a>
        @endforeach
    </div>
</x-layouts.app>
