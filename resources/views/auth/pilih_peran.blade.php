<x-layouts.guest title="Pilih Peran">
    <x-page-header
        title="Pilih Peran Daftar"
        subtitle="Silakan pilih jenis keanggotaan Anda"
        :back="route('login')"
    />

    <div class="flex flex-col gap-5">
        @php
            $peran = [
                ['route' => 'register_guru', 'icon' => 'school', 'title' => 'Guru Mata Pelajaran / Staff Piket', 'desc' => 'Daftar sebagai tenaga pengajar untuk melakukan pengisian jurnal KBM dan absensi siswa di kelas.'],
                ['route' => 'register_pengurus_kelas', 'icon' => 'groups', 'title' => 'Pengurus Kelas / Siswa', 'desc' => 'Mewakili ketua kelas atau sekretaris untuk melihat riwayat jurnal dan membantu administrasi KBM harian.'],
            ];
        @endphp

        @foreach ($peran as $item)
            <a
                href="{{ route($item['route']) }}"
                class="press flex flex-col gap-4 rounded-2xl bg-card p-6 shadow-[var(--shadow-card)] transition-all hover:-translate-y-0.5"
            >
                <div class="flex items-start justify-between">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface-alt text-navy">
                        <x-icon :name="$item['icon']" :size="24" />
                    </span>
                    <x-icon name="chevron_right" :size="20" class="text-muted-2" />
                </div>
                <div class="flex flex-col gap-1">
                    <h2 class="text-lg font-bold leading-snug text-ink">{{ $item['title'] }}</h2>
                    <p class="text-[13px] leading-relaxed text-muted-2">{{ $item['desc'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
</x-layouts.guest>
