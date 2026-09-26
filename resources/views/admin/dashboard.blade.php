@php
    // Perlu Perhatian -- SEMUA hal yang butuh tindakan admin dikumpulkan jadi
    // satu tempat paling atas (bukan tersebar: dulu alert pendaftaran akun di
    // atas sendiri, sisanya di bagian "Kesiswaan Hari Ini" di bawah, padahal
    // sama-sama hal yang perlu ditindaklanjuti). Kartu data (jumlah Guru,
    // Siswa, dst.) dipisah ke bagian "Ringkasan Data" karena sifatnya
    // referensi, bukan sesuatu yang perlu segera dikerjakan.
    $pendingAkun = \App\Models\User::where('status', 'pending')->count();

    $dispensasiMenunggu = \App\Models\Dispensasi::where('status_piket', 'approved')
        ->where('status_waka', 'pending')->count();

    $siswaAlphaTinggi = \App\Models\Absensi::where('status', 'alpha')
        ->whereHas('jurnal', fn ($q) => $q->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year))
        ->get()->countBy('siswa_id')->filter(fn ($n) => $n >= 3)->count();

    $perluPerhatian = collect([
        $pendingAkun > 0 ? ['label' => 'Pendaftaran akun menunggu persetujuan', 'value' => $pendingAkun, 'icon' => 'how_to_reg', 'route' => 'master.akun.persetujuan'] : null,
        $dispensasiMenunggu > 0 ? ['label' => 'Dispensasi menunggu persetujuan Waka', 'value' => $dispensasiMenunggu, 'icon' => 'fact_check', 'route' => 'dispensasi.index'] : null,
        $siswaAlphaTinggi > 0 ? ['label' => 'Siswa alpa 3 kali atau lebih bulan ini', 'value' => $siswaAlphaTinggi, 'icon' => 'bar_chart', 'route' => 'rekap.siswa.index'] : null,
    ])->filter()->values();

    $ringkasanData = [
        ['label' => 'Guru', 'value' => \App\Models\Guru::count(), 'icon' => 'groups', 'route' => 'master.guru.index'],
        ['label' => 'Siswa', 'value' => \App\Models\Siswa::count(), 'icon' => 'school', 'route' => 'master.siswa.index'],
        ['label' => 'Kelas', 'value' => \App\Models\Kelas::count(), 'icon' => 'meeting_room', 'route' => 'master.kelas.index'],
        ['label' => 'Mata Pelajaran', 'value' => \App\Models\Mapel::count(), 'icon' => 'menu_book', 'route' => 'master.mapel.index'],
        ['label' => 'Jadwal Pelajaran', 'value' => \App\Models\Jadwal::count(), 'icon' => 'calendar_month', 'route' => 'master.jadwal-pelajaran.index'],
        ['label' => 'Jadwal Piket', 'value' => \App\Models\JadwalPiket::count(), 'icon' => 'event_available', 'route' => 'master.jadwal-piket.index'],
    ];
@endphp

<x-layouts.admin title="Beranda" heading="Beranda">
    <x-ui.jam-sekarang :jp-sekarang="\App\Support\Waktu::jpAktifSekarang()" />

    <h2 class="mb-3 mt-6 text-sm font-bold text-ink">Perlu Perhatian</h2>
    @if ($perluPerhatian->isEmpty())
        <p class="mb-2 text-sm text-muted">Tidak ada hal yang perlu ditinjau saat ini.</p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ($perluPerhatian as $p)
                <a href="{{ route($p['route']) }}" class="press flex flex-col gap-3 rounded-xl border border-alpha/30 bg-alpha-soft p-5 hover:border-alpha">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-card text-alpha">
                        <x-icon :name="$p['icon']" :size="22" />
                    </span>
                    <span class="text-3xl font-bold text-ink">{{ $p['value'] }}</span>
                    <span class="text-sm text-muted">{{ $p['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <h2 class="mb-3 mt-8 text-sm font-bold text-ink">Ringkasan Data</h2>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
        @foreach ($ringkasanData as $s)
            <a href="{{ route($s['route']) }}" class="press flex flex-col gap-3 rounded-xl border border-surface-alt bg-card p-5 hover:border-navy">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon :name="$s['icon']" :size="22" />
                </span>
                <span class="text-3xl font-bold text-ink">{{ $s['value'] }}</span>
                <span class="text-sm text-muted">{{ $s['label'] }}</span>
            </a>
        @endforeach
    </div>

    <h2 class="mb-3 mt-8 text-sm font-bold text-ink">Pintasan</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('piket.monitor.index') }}" class="press flex items-center gap-3 rounded-xl border border-surface-alt bg-card p-5 hover:border-navy">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="monitoring" :size="22" />
            </span>
            <span class="text-sm font-semibold text-ink">Monitor Piket Hari Ini</span>
        </a>
    </div>
</x-layouts.admin>
