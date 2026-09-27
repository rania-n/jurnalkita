@php
    // ─── Perlu Perhatian & Tindakan Admin ─────────────────────────────────────
    $pendingAkun = \App\Models\User::where('status', 'pending')->count();

    $siswaAlphaTinggi = \App\Models\Absensi::where('status', 'alpha')
        ->whereHas('jurnal', fn ($q) => $q->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year))
        ->get()->countBy('siswa_id')->filter(fn ($n) => $n >= 3)->count();

    $totalTindakan = ($pendingAkun > 0 ? 1 : 0) + ($siswaAlphaTinggi > 0 ? 1 : 0);

    // ─── Data Pokok Sekolah ───────────────────────────────────────────────────
    $totalSiswa = \App\Models\Siswa::count();
    $totalGuru = \App\Models\Guru::count();
    $totalKelas = \App\Models\Kelas::count();
    $totalMapel = \App\Models\Mapel::count();
    $totalJadwal = \App\Models\Jadwal::count();
    $totalPiket = \App\Models\JadwalPiket::count();

    $totalWarga = $totalSiswa + $totalGuru;
    $persenSiswa = $totalWarga > 0 ? round(($totalSiswa / $totalWarga) * 100, 1) : 0;
    $persenGuru = $totalWarga > 0 ? round(($totalGuru / $totalWarga) * 100, 1) : 0;
    $rataSiswaPerKelas = $totalKelas > 0 ? round($totalSiswa / $totalKelas) : 0;

    // ─── Tahun Ajaran Aktif ───────────────────────────────────────────────────
    $tahunAjaranAktif = \App\Models\TahunAjaran::where('aktif', true)->first();
@endphp

<x-layouts.admin title="Beranda" heading="Beranda">
    <div class="flex flex-col gap-6">

        {{-- ── 1. Perlu Perhatian (Tindakan Tertunda) ───────────────────────── --}}
        @if ($totalTindakan > 0)
            <div class="overflow-hidden rounded-2xl border border-surface-alt bg-card p-4 shadow-[var(--shadow-soft)]">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-alpha text-card shadow-sm">
                            <x-icon name="notification_important" :size="20" />
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-ink">Perlu Perhatian</h3>
                                <span class="rounded-full bg-alpha px-2 py-0.5 text-[10px] font-bold text-card">{{ $totalTindakan }} Tindakan</span>
                            </div>
                            <p class="text-xs text-muted">Ada permohonan akun dan data yang perlu segera ditindaklanjuti.</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2 shrink-0 pt-1 lg:pt-0">
                        @if ($pendingAkun > 0)
                            <a href="{{ route('master.akun.persetujuan') }}"
                               class="press inline-flex items-center gap-1.5 rounded-lg border border-alpha/30 bg-card px-3 py-1.5 text-xs font-semibold text-alpha hover:border-alpha hover:bg-alpha-soft transition-all">
                                <x-icon name="how_to_reg" :size="16" />
                                <span>{{ $pendingAkun }} Pendaftaran Akun Baru</span>
                                <x-icon name="arrow_forward" :size="13" />
                            </a>
                        @endif


                        @if ($siswaAlphaTinggi > 0)
                            <a href="{{ route('rekap.siswa.index') }}"
                               class="press inline-flex items-center gap-1.5 rounded-lg border border-alpha/30 bg-card px-3 py-1.5 text-xs font-semibold text-alpha hover:border-alpha hover:bg-alpha-soft transition-all">
                                <x-icon name="report_problem" :size="16" />
                                <span>{{ $siswaAlphaTinggi }} Siswa Tanpa Keterangan &ge; 3 Kali</span>
                                <x-icon name="arrow_forward" :size="13" />
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 2. Ringkasan Data Pokok Sekolah ─────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-surface-alt bg-card shadow-[var(--shadow-soft)]">
            {{-- Panel Header --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-surface-alt bg-surface/40 px-5 py-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-navy text-card">
                        <x-icon name="school" :size="18" />
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-ink">Data Pokok Sekolah</h3>
                        <p class="text-[11px] text-muted">Ringkasan data guru, siswa, kelas, dan jadwal pelajaran</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if ($tahunAjaranAktif)
                        <span class="inline-flex items-center gap-1 rounded-md bg-navy/10 px-2.5 py-1 text-[11px] font-bold text-navy">
                            <x-icon name="event_repeat" :size="14" />
                            Tahun Ajaran {{ $tahunAjaranAktif->nama }} {{ $tahunAjaranAktif->semester ? '('.ucfirst($tahunAjaranAktif->semester).')' : '' }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1 rounded-md bg-surface-alt px-2.5 py-1 text-[11px] font-semibold text-muted-2">
                        <x-icon name="layers" :size="14" />
                        {{ $totalKelas }} Kelas
                    </span>
                </div>
            </div>

            {{-- Komposisi Warga Sekolah --}}
            <div class="p-5">
                <div class="mb-2.5 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-ink">Perbandingan Guru dan Siswa</span>
                        <span class="rounded-full bg-navy/10 px-2 py-0.5 text-[10px] font-extrabold text-navy">
                            {{ number_format($totalWarga, 0, ',', '.') }} Total
                        </span>
                    </div>
                    <span class="text-[11px] text-muted">Rata-rata <strong>{{ $rataSiswaPerKelas }} siswa</strong> per kelas</span>
                </div>

                {{-- Bar Proporsi Warga Sekolah --}}
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-surface-alt">
                    <div class="h-full bg-navy transition-all hover:opacity-90" style="width: {{ $persenSiswa }}%" title="Siswa: {{ $totalSiswa }} ({{ $persenSiswa }}%)"></div>
                    <div class="h-full bg-izin transition-all hover:opacity-90" style="width: {{ $persenGuru }}%" title="Guru: {{ $totalGuru }} ({{ $persenGuru }}%)"></div>
                </div>

                {{-- Kartu Metrik Data Pokok (6 Kotak Selaras) --}}
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- 1. Siswa --}}
                    <a href="{{ route('master.siswa.index') }}"
                       class="press group flex items-center justify-between rounded-xl border border-surface-alt bg-card p-3.5 transition-all hover:border-navy hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                                <x-icon name="school" :size="22" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-extrabold text-ink">{{ number_format($totalSiswa, 0, ',', '.') }}</span>
                                    <span class="text-xs font-bold text-navy">Siswa</span>
                                </div>
                                <p class="text-[11px] text-muted">Siswa aktif ({{ $persenSiswa }}%)</p>
                            </div>
                        </div>
                        <x-icon name="chevron_right" :size="18" class="text-muted opacity-40 transition-all group-hover:opacity-100 group-hover:text-navy group-hover:translate-x-0.5" />
                    </a>

                    {{-- 2. Guru --}}
                    <a href="{{ route('master.guru.index') }}"
                       class="press group flex items-center justify-between rounded-xl border border-surface-alt bg-card p-3.5 transition-all hover:border-navy hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                                <x-icon name="groups" :size="22" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-extrabold text-ink">{{ $totalGuru }}</span>
                                    <span class="text-xs font-bold text-navy">Guru</span>
                                </div>
                                <p class="text-[11px] text-muted">Guru terdaftar ({{ $persenGuru }}%)</p>
                            </div>
                        </div>
                        <x-icon name="chevron_right" :size="18" class="text-muted opacity-40 transition-all group-hover:opacity-100 group-hover:text-navy group-hover:translate-x-0.5" />
                    </a>

                    {{-- 3. Kelas --}}
                    <a href="{{ route('master.kelas.index') }}"
                       class="press group flex items-center justify-between rounded-xl border border-surface-alt bg-card p-3.5 transition-all hover:border-navy hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                                <x-icon name="meeting_room" :size="22" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-extrabold text-ink">{{ $totalKelas }}</span>
                                    <span class="text-xs font-bold text-navy">Kelas</span>
                                </div>
                                <p class="text-[11px] text-muted">Tingkat X, XI, dan XII</p>
                            </div>
                        </div>
                        <x-icon name="chevron_right" :size="18" class="text-muted opacity-40 transition-all group-hover:opacity-100 group-hover:text-navy group-hover:translate-x-0.5" />
                    </a>

                    {{-- 4. Mata Pelajaran --}}
                    <a href="{{ route('master.mapel.index') }}"
                       class="press group flex items-center justify-between rounded-xl border border-surface-alt bg-card p-3.5 transition-all hover:border-navy hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                                <x-icon name="menu_book" :size="22" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-extrabold text-ink">{{ $totalMapel }}</span>
                                    <span class="text-xs font-bold text-navy">Mapel</span>
                                </div>
                                <p class="text-[11px] text-muted">Mata pelajaran aktif semester ini</p>
                            </div>
                        </div>
                        <x-icon name="chevron_right" :size="18" class="text-muted opacity-40 transition-all group-hover:opacity-100 group-hover:text-navy group-hover:translate-x-0.5" />
                    </a>

                    {{-- 5. Jadwal Pelajaran --}}
                    <a href="{{ route('master.jadwal-pelajaran.index') }}"
                       class="press group flex items-center justify-between rounded-xl border border-surface-alt bg-card p-3.5 transition-all hover:border-navy hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                                <x-icon name="calendar_month" :size="22" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-extrabold text-ink">{{ $totalJadwal }}</span>
                                    <span class="text-xs font-bold text-navy">Jadwal</span>
                                </div>
                                <p class="text-[11px] text-muted">Jadwal KBM per minggu</p>
                            </div>
                        </div>
                        <x-icon name="chevron_right" :size="18" class="text-muted opacity-40 transition-all group-hover:opacity-100 group-hover:text-navy group-hover:translate-x-0.5" />
                    </a>

                    {{-- 6. Jadwal Piket --}}
                    <a href="{{ route('master.jadwal-piket.index') }}"
                       class="press group flex items-center justify-between rounded-xl border border-surface-alt bg-card p-3.5 transition-all hover:border-navy hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                                <x-icon name="event_available" :size="22" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-extrabold text-ink">{{ $totalPiket }}</span>
                                    <span class="text-xs font-bold text-navy">Piket</span>
                                </div>
                                <p class="text-[11px] text-muted">Jadwal guru piket per minggu</p>
                            </div>
                        </div>
                        <x-icon name="chevron_right" :size="18" class="text-muted opacity-40 transition-all group-hover:opacity-100 group-hover:text-navy group-hover:translate-x-0.5" />
                    </a>
                </div>
            </div>
        </div>

        {{-- ── 3. Pusat Pintasan Menu Administrasi ──────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-surface-alt bg-card shadow-[var(--shadow-soft)]">
            <div class="border-b border-surface-alt bg-surface/30 px-5 py-3.5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-ink">
                    <x-icon name="dashboard_customize" :size="18" class="text-navy" />
                    Pintasan Menu
                </h3>
                <p class="text-[11px] text-muted">Akses cepat ke menu persetujuan, monitoring piket, dan pengaturan sistem</p>
            </div>

            <div class="grid grid-cols-1 divide-y divide-surface-alt sm:grid-cols-2 sm:divide-y-0 sm:gap-px sm:bg-surface-alt lg:grid-cols-3">
                {{-- 1. Persetujuan Akun --}}
                <a href="{{ route('master.akun.persetujuan') }}"
                   class="group flex items-start gap-3.5 bg-card p-4 transition-colors hover:bg-surface/60">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                        <x-icon name="how_to_reg" :size="20" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-sm font-bold text-ink">Persetujuan Akun</p>
                            @if ($pendingAkun > 0)
                                <span class="rounded-full border border-sakit/30 bg-sakit-soft px-2 py-0.5 text-[10px] font-bold text-sakit">{{ $pendingAkun }} Menunggu</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-muted">Verifikasi dan setujui pendaftaran akun baru</p>
                    </div>
                </a>

                {{-- 2. Monitor Piket --}}
                <a href="{{ route('piket.monitor.index') }}"
                   class="group flex items-start gap-3.5 bg-card p-4 transition-colors hover:bg-surface/60">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                        <x-icon name="monitoring" :size="20" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink">Monitor Piket</p>
                        <p class="mt-0.5 text-xs text-muted">Pantau pengisian jurnal dan kehadiran guru hari ini</p>
                    </div>
                </a>

                {{-- 3. Rekap Kehadiran Siswa --}}
                <a href="{{ route('rekap.siswa.index') }}"
                   class="group flex items-start gap-3.5 bg-card p-4 transition-colors hover:bg-surface/60">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                        <x-icon name="bar_chart" :size="20" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink">Rekap Kehadiran Siswa</p>
                        <p class="mt-0.5 text-xs text-muted">Rekap absensi siswa: hadir, izin, sakit, dan alpa</p>
                    </div>
                </a>

                {{-- 4. Log Audit Sistem --}}
                <a href="{{ route('master.audit-log.index') }}"
                   class="group flex items-start gap-3.5 bg-card p-4 transition-colors hover:bg-surface/60">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                        <x-icon name="history" :size="20" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink">Log Audit</p>
                        <p class="mt-0.5 text-xs text-muted">Riwayat aktivitas pengguna dan perubahan data</p>
                    </div>
                </a>

                {{-- 5. Pengaturan Jurnal --}}
                <a href="{{ route('master.pengaturan-jurnal.index') }}"
                   class="group flex items-start gap-3.5 bg-card p-4 transition-colors hover:bg-surface/60">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                        <x-icon name="tune" :size="20" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink">Pengaturan Jurnal</p>
                        <p class="mt-0.5 text-xs text-muted">Atur batas waktu pengisian, toleransi, dan mode jurnal</p>
                    </div>
                </a>

                {{-- 6. Cadangan Data --}}
                <a href="{{ route('master.backup.index') }}"
                   class="group flex items-start gap-3.5 bg-card p-4 transition-colors hover:bg-surface/60">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy transition-colors group-hover:bg-navy group-hover:text-card">
                        <x-icon name="cloud_download" :size="20" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-ink">Cadangan Data</p>
                        <p class="mt-0.5 text-xs text-muted">Cadangkan dan unduh salinan database sekolah</p>
                    </div>
                </a>
            </div>
        </div>

    </div>
</x-layouts.admin>
