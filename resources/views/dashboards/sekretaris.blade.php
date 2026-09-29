@php
    $user = auth()->user();
    $kelas = $user->isSekretaris() ? $user->kelasSekretaris() : null;
    $pending = $kelas
        ? \App\Models\Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
            ->inReviewQueue()->count()
        : 0;

    // K: dulu dasbor ini cuma 3 kotak menu doang -- ditambah jadwal hari ini
    // biar pengurus kelas langsung lihat pelajaran mana yang gurunya sudah
    // hadir/tidak hadir/belum diisi tanpa buka menu lain dulu.
    $hariIni = \App\Support\HariSekolah::hariIni();
    $hariKhusus = \App\Models\HariKhusus::untukTanggal(today());
    $jadwalHariIni = $kelas && $hariIni
        ? $kelas->jadwals()->with('mapel', 'guru')->where('hari', $hariIni)->orderBy('jam_ke_mulai')->get()
        : collect();
    if ($hariKhusus?->jenis === 'pulang_cepat') {
        $jadwalHariIni = $jadwalHariIni->reject(fn ($jadwal) => \App\Support\Waktu::jadwalDitiadakan($jadwal, today()));
    }
    $jurnalHariIni = $jadwalHariIni->isNotEmpty()
        ? \App\Models\Jurnal::whereIn('jadwal_id', $jadwalHariIni->pluck('id'))->whereDate('tanggal', today())->get()->keyBy('jadwal_id')
        : collect();
    $labelStatusGuru = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tugas'];
    $toneStatusGuru = ['hadir' => 'hadir', 'tidak_hadir' => 'tugas'];
@endphp

<x-layouts.app title="Beranda Pengurus Kelas" width="wide">
    <x-page-header title="Beranda" size="sm" :subtitle="$kelas?->nama ?? 'Pengurus Kelas'" />

    @if ($hariKhusus)
        <x-alert type="info" class="mb-4">
            {{ $hariKhusus->nama }} — {{ $hariKhusus->jenis === 'tanpa_kbm' ? 'KBM dan piket ditiadakan hari ini.' : 'Kegiatan sekolah selesai pukul ' . $hariKhusus->jam_selesai->format('H:i') . '.' }}
        </x-alert>
    @endif

    @if ($kelas)
    @endif

    @unless ($kelas)
        <x-alert type="warning">Akun ini bukan pengurus kelas atau belum terhubung ke kelas. <x-ui.admin-contact />.</x-alert>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 grid-fill-last">
            <a href="{{ route('sekretaris.jurnal.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="fact_check" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Verifikasi Jurnal</p>
                    <p class="text-xs text-muted">
                        {{ $pending ? "$pending jurnal perlu diperiksa" : 'Semua jurnal sudah diperiksa' }}
                    </p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>

            <a href="{{ route('sekretaris.jurnal.pengganti') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="edit_note" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Isi Jurnal Pengganti</p>
                    <p class="text-xs text-muted">Untuk guru tidak hadir yang memberi tugas melalui WhatsApp</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>

            <a href="{{ route('sekretaris.kelas.rekap') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="bar_chart" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Rekap Kehadiran</p>
                    <p class="text-xs text-muted">Kehadiran siswa sekelas, bisa difilter tanggal</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>

            {{-- Daftar Siswa dulu di menu bawah "Kelas" -- diganti Rekap
                 Kehadiran (lihat config/navigation.php), jadi dipindah ke
                 sini biar tetap kejangkau. --}}
            <a href="{{ route('sekretaris.kelas.siswa') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="school" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Daftar Siswa</p>
                    <p class="text-xs text-muted">Kehadiran hari ini per siswa</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>
        </div>

        <h2 class="mb-2 mt-6 text-sm font-bold text-ink">Jadwal Kelas Hari Ini</h2>
        @if ($jadwalHariIni->isEmpty())
            <x-ui.empty icon="event_busy" title="Tidak ada jadwal hari ini" />
        @else
            <x-ui.card-list class="grid-fill-last">
                @foreach ($jadwalHariIni as $j)
                    @php $jr = $jurnalHariIni->get($j->id); @endphp
                    <x-ui.list-card
                        :title="$j->mapel->nama"
                        :meta="['JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai . ' · ' . $j->guru->nama]"
                    >
                        <x-slot:badge>
                            @if ($jr)
                                <x-ui.status-badge :status="$toneStatusGuru[$jr->status_guru] ?? 'menunggu'">
                                    {{ $labelStatusGuru[$jr->status_guru] ?? $jr->status_guru }}
                                </x-ui.status-badge>
                            @else
                                <x-ui.status-badge status="menunggu">Belum Diisi</x-ui.status-badge>
                            @endif
                        </x-slot:badge>
                    </x-ui.list-card>
                @endforeach
            </x-ui.card-list>
        @endif
    @endunless
</x-layouts.app>
