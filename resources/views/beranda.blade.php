{{-- Placeholder beranda — belum ada desain Figma. Lihat docs/scope.md. --}}
<x-layouts.app title="Beranda" width="wide">
    <header class="mb-6 flex flex-col gap-0.5">
        <p class="text-sm text-muted">Selamat datang,</p>
        <h1 class="text-[22px] font-bold text-ink">Bapak / Ibu Guru</h1>
    </header>

    <div class="grid grid-cols-2 gap-3">
        @php
            $menu = [
                ['label' => 'Isi Jurnal Mengajar', 'icon' => 'edit_note', 'route' => 'jurnal.create'],
                ['label' => 'Dispensasi Siswa', 'icon' => 'fact_check', 'route' => 'dispensasi.index'],
                ['label' => 'Jadwal Piket', 'icon' => 'event_available', 'route' => 'piket.jadwal.index'],
                ['label' => 'Master Data', 'icon' => 'database', 'route' => 'master.index'],
            ];
        @endphp

        @foreach ($menu as $m)
            <a href="{{ route($m['route']) }}" class="press flex flex-col gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)] transition-colors hover:bg-surface-alt/40">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon :name="$m['icon']" :size="24" />
                </span>
                <span class="text-sm font-semibold text-ink">{{ $m['label'] }}</span>
            </a>
        @endforeach
    </div>

    <x-alert type="info" class="mt-6">
        Halaman beranda masih sementara. Isi lengkapnya menyusul setelah desain siap.
    </x-alert>
</x-layouts.app>
