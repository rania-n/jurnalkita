@php
    $guru = auth()->user()->guru;
    $hari = \App\Support\HariSekolah::hariIni();
    $piketHariIni = auth()->user()->piketHariIni();
    $isWali = auth()->user()->isWali();

    if ($piketHariIni) {
        $jpAktif = \App\Support\Waktu::jpAktifSekarang();
        $dalamJamSekolah = \App\Support\Waktu::dalamJamSekolah();

        // Piket BUKAN berarti otomatis nggak ada jadwal ngajar hari itu --
        // dua-duanya bisa nempel di hari yang sama. Dihitung juga di sini
        // (bukan cuma di cabang "else" di bawah) biar "Jadwal Mengajar Hari
        // Ini" tetap kelihatan kalau ternyata guru ini piket SEKALIGUS ngajar
        // -- jangan disembunyiin cuma gara-gara lagi piket (lihat pemakaian
        // di bawah, dekat "Jadwal Mengajar Hari Ini").
        $jadwalHariIni = $hari && $guru
            ? $guru->jadwals()->with('kelas', 'mapel')->where('hari', $hari)->orderBy('jam_ke_mulai')->get()
            : collect();
        $jurnalGuruHariIni = $guru
            ? $guru->jurnals()->whereDate('tanggal', today())->get()->keyBy('jadwal_id')
            : collect();

        // Jam berakhir JP TERAKHIR hari ini -- dipakai buat kasih tau "piket
        // sampai jam berapa" di badge atas (piket nggak punya jam sendiri di
        // data, jadi dipakein jam pulang sekolah beneran).
        $jpTerakhirHariIni = \App\Models\JamPelajaran::where('kategori', \App\Support\Waktu::kategori())
            ->orderByDesc('jam_ke')->first();

        // Jadwal + jurnal SATU HARI SEKOLAH (bukan cuma jadwal guru ini sendiri
        // -- piket ngawasin SEMUA kelas), dipetakan ke status per baris. Cuma
        // 'hadir'/'tidak_hadir' yang valid sekarang (status "Tugas Luar" udah
        // dihapus dari aplikasi, lihat migration remove_tugas_status).
        $jadwalsHariIni = $hari
            ? \App\Models\Jadwal::where('hari', $hari)->with('kelas', 'mapel', 'guru')->get()
            : collect();

        $jurnalsHariIni = $jadwalsHariIni->isNotEmpty()
            ? \App\Models\Jurnal::whereIn('jadwal_id', $jadwalsHariIni->pluck('id'))
                ->whereDate('tanggal', today())
                ->get()
                ->keyBy('jadwal_id')
            : collect();

        $piketRows = $jadwalsHariIni->map(function ($jadwal) use ($jurnalsHariIni) {
            $jurnal = $jurnalsHariIni->get($jadwal->id);
            $status = $jurnal->status_guru ?? 'belum_diisi';

            return [
                'jadwal' => $jadwal,
                'jurnal' => $jurnal,
                'status' => $status,
                'statusLabel' => $status === 'hadir' ? 'Hadir' : ($status === 'tidak_hadir' ? 'Tidak Hadir' : 'Belum Diisi'),
            ];
        });

        $totalJp = $piketRows->count();
        $totalHadir = $piketRows->where('status', 'hadir')->count();
        $totalTidakHadir = $piketRows->where('status', 'tidak_hadir')->count();
        $totalBelumDiisi = $piketRows->where('status', 'belum_diisi')->count();
        $totalTerisi = $totalHadir + $totalTidakHadir;
        $persenTerisi = $totalJp > 0 ? round(($totalTerisi / $totalJp) * 100) : 0;

        // JP yang beneran lagi jalan detik ini -- yang paling mendesak buat
        // dipantau (kelas yang belum diisi jurnalnya PADAHAL jamnya lagi
        // berlangsung sekarang, bukan cuma "hari ini" secara umum).
        $jpAktifRows = $jpAktif
            ? $piketRows->filter(fn ($r) => $r['jadwal']->jam_ke_mulai <= $jpAktif && $r['jadwal']->jam_ke_selesai >= $jpAktif)
            : collect();
        $jpAktifBelumDiisi = $jpAktifRows->where('status', 'belum_diisi');
        $jpAktifSudahDiisi = $jpAktifRows->where('status', '!=', 'belum_diisi');

        $guruTidakHadirHariIni = $piketRows->where('status', 'tidak_hadir');

        $dispensasiHariIni = \App\Models\Dispensasi::with('siswa.kelas')
            ->where(fn ($q) => $q->whereDate('tanggal', '<=', today())
                ->where(fn ($w) => $w->whereNull('tanggal_selesai')->orWhereDate('tanggal_selesai', '>=', today())))
            ->latest()
            ->get();

        $dispensasiPendingCount = $dispensasiHariIni->where('status_akhir', 'pending')->count();
        $dispensasiApprovedCount = $dispensasiHariIni->where('status_akhir', 'approved')->count();

        // berlakuPada() (BUKAN where('hari', ...) polos) -- piket sekarang bisa
        // per TANGGAL spesifik (nggak otomatis berulang tiap minggu di hari
        // yang sama), jadi query ini harus ngecek tanggal HARI INI beneran,
        // bukan cuma "kebetulan sama-sama hari Senin".
        // ::query()->berlakuPada() (BUKAN JadwalPiket::berlakuPada() langsung) --
        // model ini punya 2 method beda nama sama: scopeBerlakuPada() (query)
        // DAN berlakuPada() (instance, dipakai piketHariIni()). Panggil statis
        // langsung ke Model malah kena method instance-nya (error "cannot be
        // called statically"), bukan scope-nya.
        $rekanPiket = \App\Models\JadwalPiket::query()->berlakuPada(today())->with('guru.user')->get();

        $wakaBertugas = \App\Models\User::wakaUntukHariIni();
        $waLinkWaka = $wakaBertugas?->no_hp
            ? \App\Support\WaLink::url($wakaBertugas->no_hp, "Halo Bapak/Ibu {$wakaBertugas->name} (Waka Kesiswaan), saya ".auth()->user()->name.' (Guru Piket hari ini), ingin koordinasi terkait dispensasi siswa.')
            : null;
    } else {
        $jadwalHariIni = $hari && $guru
            ? $guru->jadwals()->with('kelas', 'mapel')->where('hari', $hari)->orderBy('jam_ke_mulai')->get()
            : collect();

        // Jadwal yang jurnalnya sudah diisi hari ini
        $jurnalGuruHariIni = $guru
            ? $guru->jurnals()->whereDate('tanggal', today())->get()->keyBy('jadwal_id')
            : collect();
    }

    // Pilihan awal cuma ditampilkan SEKALI per login (bukan tiap kali buka
    // dasbor) -- ditandai session (bukan localStorage) biar konsisten walau
    // guru buka dari perangkat/browser berbeda tiap login.
    $tampilkanPilihanAwal = ! session('pilihan_awal_guru_tampil');
    session(['pilihan_awal_guru_tampil' => true]);
@endphp

<x-layouts.app title="Beranda Guru" width="wide">
    {{-- Nama guru udah ada di header atas (avatar + nama), dan "Beranda"
         sendiri udah kelihatan dari menu yang lagi disorot di sidebar/navbar
         -- teks itu nggak nambah informasi apa pun, jadi nggak usah
         ditampilkan lagi sama sekali di sini. Badge piket (kalau ada) tetap
         ditampilkan, cukup lewat div ringkas -- nggak perlu x-page-header
         lagi kalau nggak ada judul yang mau ditampilkan. --}}
    @if (! $piketHariIni)
        <x-ui.jam-sekarang :jp-sekarang="\App\Support\Waktu::jpAktifSekarang()" />
    @endif

    @if ($tampilkanPilihanAwal)
        <dialog id="modal-pilihan-awal"
                class="fixed inset-0 m-auto w-[min(26rem,calc(100vw-2rem))] rounded-2xl border-0 bg-card p-0 text-ink shadow-2xl backdrop:bg-navy/30 backdrop:backdrop-blur-sm">
            <div class="flex flex-col items-center gap-1 px-6 pb-2 pt-7 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-surface-alt text-navy">
                    <x-icon name="waving_hand" :size="28" />
                </span>
                <h3 class="mt-2 text-lg font-bold text-ink">Halo, {{ auth()->user()->name }}!</h3>
                @if ($piketHariIni)
                    <p class="text-sm text-muted">Anda bertugas piket hari ini. Mau langsung pantau piket, atau lihat ringkasan beranda dulu?</p>
                @else
                    <p class="text-sm text-muted">Mau langsung isi jurnal, atau lihat-lihat beranda dulu?</p>
                @endif
            </div>

            <div class="flex flex-col gap-2 p-6 pt-4">
                @if ($piketHariIni)
                    <x-ui.button :href="route('piket.monitor.index')" icon="monitoring" class="w-full">Pantau Piket Sekarang</x-ui.button>
                @else
                    <x-ui.button :href="route('jurnal.create')" icon="edit_note" class="w-full">Isi Jurnal Sekarang</x-ui.button>
                @endif
                <x-ui.button type="button" variant="secondary" data-modal-close class="w-full">Lihat Beranda Dulu</x-ui.button>
            </div>
        </dialog>

        @push('scripts')
            <script>document.getElementById('modal-pilihan-awal')?.showModal();</script>
        @endpush
    @endif

    @if ($piketHariIni)
        {{-- ==================== DASBOR GURU PIKET ==================== --}}

        {{-- Progress bar + ringkasan cepat --}}
        <div class="mb-4 rounded-2xl border border-surface-alt bg-card p-4 shadow-[var(--shadow-soft)] sm:p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-ink">Pantauan Jurnal Hari Ini</h2>
                    <p class="mt-0.5 text-xs text-muted">
                        <strong class="text-ink">{{ $totalTerisi }}</strong> dari {{ $totalJp }} jam pelajaran sudah dilaporkan guru
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-extrabold text-navy">{{ $persenTerisi }}%</span>
                    <span class="text-xs text-muted">terisi</span>
                </div>
            </div>

            <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-surface-alt">
                <div class="h-full rounded-full bg-gradient-to-r from-navy to-hadir transition-all duration-500" style="width: {{ $persenTerisi }}%"></div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3">
                <div class="flex flex-col rounded-xl bg-surface-alt/70 p-3">
                    <span class="text-[11px] font-semibold text-muted">Total Terjadwal</span>
                    <span class="mt-0.5 text-lg font-bold text-ink">{{ $totalJp }} <span class="text-xs font-normal text-muted">JP</span></span>
                </div>
                <div class="flex flex-col rounded-xl bg-hadir-soft/60 p-3">
                    <span class="text-[11px] font-semibold text-hadir">Guru Hadir</span>
                    <span class="mt-0.5 text-lg font-bold text-hadir">{{ $totalHadir }} <span class="text-xs font-normal opacity-80">JP</span></span>
                </div>
                {{-- max-sm:col-span-2 -- 3 kartu di grid 2 kolom (HP) selalu
                     nyisain yang terakhir ini sendirian di barisnya (2+1),
                     di-stretch penuh KHUSUS di bawah breakpoint sm -- begitu
                     naik ke sm:grid-cols-3, 3 kartunya udah pas 1 baris,
                     nggak butuh di-stretch lagi (makanya bukan grid-fill-last
                     generik, itu bakal maksa dia turun baris sendiri di
                     breakpoint 3 kolom). --}}
                <div class="flex flex-col rounded-xl bg-sakit-soft/60 p-3 max-sm:col-span-2">
                    <span class="text-[11px] font-semibold text-sakit">Belum Diisi</span>
                    <span class="mt-0.5 text-lg font-bold text-sakit">{{ $totalBelumDiisi }} <span class="text-xs font-normal opacity-80">JP</span></span>
                </div>
            </div>
        </div>

        {{-- Menu aksi cepat --}}
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="{{ route('piket.monitor.index') }}" class="press flex items-center gap-3 rounded-2xl border border-transparent bg-card p-4 shadow-[var(--shadow-soft)] transition-all hover:border-navy">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="monitoring" :size="24" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink">Monitor Piket</p>
                    <p class="truncate text-xs text-muted">Cek status seluruh kelas & guru</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="shrink-0 text-muted" />
            </a>

            <a href="{{ route('dispensasi.create') }}" class="press flex items-center gap-3 rounded-2xl border border-transparent bg-card p-4 shadow-[var(--shadow-soft)] transition-all hover:border-navy">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="add_circle" :size="24" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink">Buat Dispen</p>
                    <p class="truncate text-xs text-muted">Izin keluar siswa / lomba</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="shrink-0 text-muted" />
            </a>

            <a href="{{ route('piket.monitor.ekspor', ['tanggal' => today()->toDateString()]) }}" class="press flex items-center gap-3 rounded-2xl border border-transparent bg-card p-4 shadow-[var(--shadow-soft)] transition-all hover:border-navy">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="download" :size="24" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink">Unduh Rekap</p>
                    <p class="truncate text-xs text-muted">Ekspor PDF rekap piket hari ini</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="shrink-0 text-muted" />
            </a>
        </div>

        {{-- Live monitor JP yang lagi jalan sekarang -- ini yang paling
             mendesak, jadi selalu kebuka full kalau ada yang belum diisi
             (bukan disembunyiin di balik <details>). Daftar detailnya sendiri
             baru dibungkus <details> pas jumlahnya lumayan banyak. --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-surface-alt bg-card shadow-[var(--shadow-soft)]">
            @if (! $jpAktif)
                <div class="p-4 sm:p-5">
                    <div class="flex items-center gap-2 border-b border-surface-alt pb-3">
                        <span class="flex h-3 w-3 rounded-full bg-muted"></span>
                        <h3 class="text-sm font-bold text-ink">Pantauan Sesi Sekarang</h3>
                    </div>
                    <div class="py-6 text-center text-muted">
                        <x-icon name="snooze" :size="32" class="mx-auto mb-1 text-muted-2" />
                        <p class="text-sm font-semibold text-ink">
                            {{ $dalamJamSekolah ? 'Saat ini waktu istirahat / jeda pelajaran' : 'Di luar jam pelajaran aktif sekolah' }}
                        </p>
                        <p class="mt-0.5 text-xs text-muted">Monitoring per-JP otomatis aktif saat jam pelajaran dimulai.</p>
                    </div>
                </div>
            @elseif ($jpAktifBelumDiisi->isEmpty())
                <div class="p-4 sm:p-5">
                    <div class="flex items-center justify-between border-b border-surface-alt pb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-3 w-3 rounded-full bg-hadir"></span>
                            <h3 class="text-sm font-bold text-ink">Sedang Berlangsung: JP {{ $jpAktif }}</h3>
                        </div>
                        <span class="text-xs font-semibold text-hadir">{{ $jpAktifSudahDiisi->count() }}/{{ $jpAktifRows->count() }} Kelas Terisi (100%)</span>
                    </div>
                    <div class="py-5 text-center text-hadir">
                        <x-icon name="task_alt" :size="32" class="mx-auto mb-1 text-hadir" />
                        <p class="text-sm font-bold text-ink">Semua Kelas di JP {{ $jpAktif }} Sudah Terisi!</p>
                        <p class="mt-0.5 text-xs text-muted">Seluruh guru yang terjadwal di jam ini sudah melaporkan kehadiran dan materi.</p>
                    </div>
                </div>
            @else
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between p-4 transition-colors select-none hover:bg-surface/50 sm:p-5 [&::-webkit-details-marker]:hidden">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="relative flex h-3 w-3 shrink-0">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sakit opacity-75"></span>
                                <span class="relative inline-flex h-3 w-3 rounded-full bg-sakit"></span>
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-ink">Sedang Berlangsung: JP {{ $jpAktif }}</h3>
                                    <span class="rounded-full bg-sakit-soft px-2 py-0.5 text-[10px] font-bold text-sakit">{{ $jpAktifBelumDiisi->count() }} Belum Diisi</span>
                                </div>
                                <p class="mt-0.5 text-xs text-muted">{{ $jpAktifSudahDiisi->count() }}/{{ $jpAktifRows->count() }} Kelas Terisi</p>
                            </div>
                        </div>
                        <span class="ml-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-alt text-muted transition-colors group-hover:text-ink">
                            <x-icon name="expand_more" :size="20" class="transition-transform duration-200 group-open:rotate-180" />
                        </span>
                    </summary>

                    <div class="border-t border-surface-alt bg-surface/30 p-4 sm:p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="flex items-center gap-1 text-xs font-bold text-sakit">
                                <x-icon name="warning" :size="15" />
                                {{ $jpAktifBelumDiisi->count() }} kelas belum diisi jurnal di JP {{ $jpAktif }}
                            </p>
                            <a href="{{ route('piket.monitor.index') }}" class="text-xs font-semibold text-navy hover:underline">Monitor Piket Lengkap →</a>
                        </div>

                        <div class="max-h-80 overflow-y-auto pr-1">
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($jpAktifBelumDiisi as $item)
                                    <div class="flex items-start justify-between gap-2 rounded-xl border border-sakit/20 bg-card p-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-ink">{{ $item['jadwal']->kelas->nama }}</p>
                                            <p class="mt-0.5 truncate text-[11px] text-muted">{{ $item['jadwal']->mapel->nama }}</p>
                                            <p class="mt-0.5 truncate text-[11px] font-medium text-ink">{{ $item['jadwal']->guru->nama }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-md bg-sakit-soft px-1.5 py-0.5 text-[10px] font-bold text-sakit">Belum Diisi</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </details>
            @endif
        </div>

        {{-- Dispensasi hari ini & tim koordinasi piket --}}
        <div class="mb-6 grid grid-cols-1 items-start gap-4 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-surface-alt bg-card shadow-[var(--shadow-soft)]">
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between p-4 transition-colors select-none hover:bg-surface/50 sm:p-5 [&::-webkit-details-marker]:hidden">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-alt text-navy">
                                <x-icon name="fact_check" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-ink">Dispensasi Siswa Hari Ini</h3>
                                    @if ($dispensasiPendingCount > 0)
                                        <span class="rounded-full bg-sakit-soft px-2 py-0.5 text-[10px] font-bold text-sakit">{{ $dispensasiPendingCount }} Menunggu</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 truncate text-[11px] text-muted">{{ $dispensasiHariIni->count() }} siswa izin ({{ $dispensasiPendingCount }} menunggu, {{ $dispensasiApprovedCount }} disetujui)</p>
                            </div>
                        </div>
                        <span class="ml-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-alt text-muted transition-colors group-hover:text-ink">
                            <x-icon name="expand_more" :size="20" class="transition-transform duration-200 group-open:rotate-180" />
                        </span>
                    </summary>

                    <div class="border-t border-surface-alt p-4 pt-3 sm:p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-xs font-medium text-muted">Daftar Pengajuan Hari Ini</span>
                            <a href="{{ route('dispensasi.index') }}" class="text-xs font-semibold text-navy hover:underline">Lihat Semua →</a>
                        </div>

                        @if ($dispensasiHariIni->isEmpty())
                            <div class="py-6 text-center text-muted">
                                <x-icon name="verified" :size="28" class="mx-auto mb-1 text-muted-2" />
                                <p class="text-xs font-semibold text-ink">Tidak ada siswa dispensasi hari ini</p>
                                <p class="mt-0.5 text-[11px] text-muted">Belum ada pengajuan izin keluar atau kegiatan lomba.</p>
                            </div>
                        @else
                            <div class="flex max-h-64 flex-col gap-2 overflow-y-auto pr-1">
                                @foreach ($dispensasiHariIni->take(6) as $disp)
                                    <a href="{{ route('dispensasi.surat', $disp) }}" class="flex items-center justify-between gap-2 rounded-xl border border-surface-alt/60 p-2.5 transition-colors hover:bg-surface-alt/60">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <p class="truncate text-xs font-bold text-ink">{{ $disp->siswa->nama }}</p>
                                                <span class="text-[10px] text-muted">· {{ $disp->siswa->kelas?->nama }}</span>
                                            </div>
                                            <p class="mt-0.5 truncate text-[11px] text-muted">{{ $disp->labelJam() }} · {{ $disp->alasan }}</p>
                                        </div>
                                        <span @class([
                                            'shrink-0 rounded-md px-2 py-0.5 text-[10px] font-bold',
                                            'bg-sakit-soft text-sakit' => $disp->status_akhir === 'pending',
                                            'bg-hadir-soft text-hadir' => $disp->status_akhir === 'approved',
                                            'bg-alpha-soft text-alpha' => $disp->status_akhir === 'rejected',
                                        ])>
                                            {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$disp->status_akhir] ?? $disp->status_akhir }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-3 border-t border-surface-alt pt-3">
                            <x-ui.button :href="route('dispensasi.create')" icon="add" class="!h-9 w-full !text-xs">Buat Pengajuan Dispensasi</x-ui.button>
                        </div>
                    </div>
                </details>
            </div>

            <div class="overflow-hidden rounded-2xl border border-surface-alt bg-card shadow-[var(--shadow-soft)]">
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between p-4 transition-colors select-none hover:bg-surface/50 sm:p-5 [&::-webkit-details-marker]:hidden">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-alt text-navy">
                                <x-icon name="groups" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-ink">Tim Piket & Koordinasi</h3>
                                <p class="truncate text-[11px] text-muted">{{ $rekanPiket->count() }} guru piket hari ini + Waka standby</p>
                            </div>
                        </div>
                        <span class="ml-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-alt text-muted transition-colors group-hover:text-ink">
                            <x-icon name="expand_more" :size="20" class="transition-transform duration-200 group-open:rotate-180" />
                        </span>
                    </summary>

                    <div class="border-t border-surface-alt p-4 pt-3 sm:p-5">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-bold tracking-wide text-muted-2 uppercase">Rekan Guru Piket ({{ $rekanPiket->count() }})</p>
                                <span class="text-[10px] text-muted">{{ now()->translatedFormat('l, d M') }}</span>
                            </div>

                            @if ($rekanPiket->isEmpty())
                                <p class="text-xs text-muted">Belum ada rekan piket yang terjadwal hari ini.</p>
                            @else
                                <div class="flex max-h-64 flex-col gap-2 overflow-y-auto pr-1">
                                    @foreach ($rekanPiket as $rp)
                                        @php
                                            $isMe = $rp->guru_id === $guru?->id;
                                            $waRekan = $rp->guru->user?->no_hp
                                                ? \App\Support\WaLink::url($rp->guru->user->no_hp, "Halo Bapak/Ibu {$rp->guru->nama}, sesama guru piket hari ini.")
                                                : null;
                                        @endphp
                                        <div class="flex items-center justify-between gap-2 rounded-xl border border-surface-alt/50 bg-surface/50 p-2.5">
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-bold text-ink">
                                                    {{ $rp->guru->nama }}
                                                    @if ($isMe) <span class="ml-1 rounded bg-navy/10 px-1.5 py-0.5 text-[10px] font-semibold text-navy">(Anda)</span> @endif
                                                </p>
                                                <p class="truncate text-[11px] text-muted">{{ $rp->keterangan ?: 'Piket Harian' }}</p>
                                            </div>
                                            @if ($waRekan && ! $isMe)
                                                <a href="{{ $waRekan }}" target="_blank" rel="noopener" class="flex shrink-0 items-center gap-1 rounded-lg border border-hadir/25 bg-hadir-soft px-2 py-1 text-[11px] font-bold text-hadir transition-colors hover:bg-[#bef3ab]">
                                                    <x-icon name="chat" :size="13" /> WA
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <p class="mt-2 text-[11px] font-bold tracking-wide text-muted-2 uppercase">Waka Kesiswaan Standby</p>
                            @if ($wakaBertugas)
                                <div class="flex items-center justify-between gap-2 rounded-xl border border-surface-alt/50 bg-surface/50 p-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold text-ink">{{ $wakaBertugas->name }}</p>
                                        <p class="text-[11px] text-muted">Persetujuan Dispensasi Tahap 2</p>
                                    </div>
                                    @if ($waLinkWaka)
                                        <a href="{{ $waLinkWaka }}" target="_blank" rel="noopener" class="flex shrink-0 items-center gap-1 rounded-lg bg-navy/10 px-2.5 py-1 text-[11px] font-bold text-navy transition-colors hover:bg-navy/20">
                                            <x-icon name="chat" :size="13" /> Koordinasi
                                        </a>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-muted">Belum ada Waka Kesiswaan yang terdaftar.</p>
                            @endif
                        </div>
                    </div>
                </details>
            </div>
        </div>

        {{-- Guru tidak hadir hari ini -- baris ini muncul cuma kalau memang ada,
             biar nggak numpuk kotak kosong pas semua guru hadir. --}}
        @if ($guruTidakHadirHariIni->isNotEmpty())
            <div class="mb-6 overflow-hidden rounded-2xl border border-izin/25 bg-card shadow-[var(--shadow-soft)]">
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between p-4 transition-colors select-none hover:bg-surface/50 sm:p-5 [&::-webkit-details-marker]:hidden">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-izin-soft text-izin">
                                <x-icon name="assignment_late" :size="18" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-ink">Catatan Guru Tidak Hadir</h3>
                                    <span class="rounded-full bg-izin-soft px-2 py-0.5 text-[10px] font-bold text-izin">{{ $guruTidakHadirHariIni->count() }} Guru</span>
                                </div>
                                <p class="mt-0.5 truncate text-[11px] text-muted">Pastikan kelas terkait sudah mendapatkan tugas pengganti</p>
                            </div>
                        </div>
                        <span class="ml-2 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-alt text-muted transition-colors group-hover:text-ink">
                            <x-icon name="expand_more" :size="20" class="transition-transform duration-200 group-open:rotate-180" />
                        </span>
                    </summary>

                    <div class="border-t border-surface-alt bg-surface/30 p-4 sm:p-5">
                        <div class="max-h-72 overflow-y-auto pr-1">
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                @foreach ($guruTidakHadirHariIni as $izin)
                                    <div class="rounded-xl border border-surface-alt bg-card p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-bold text-ink">{{ $izin['jadwal']->guru->nama }}</p>
                                                <p class="text-[11px] text-muted">{{ $izin['jadwal']->kelas->nama }} · JP {{ $izin['jadwal']->jam_ke_mulai }}–{{ $izin['jadwal']->jam_ke_selesai }} · {{ $izin['jadwal']->mapel->nama }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-md bg-alpha-soft px-1.5 py-0.5 text-[10px] font-bold text-alpha">Tidak Hadir</span>
                                        </div>
                                        @if ($izin['jurnal']?->tugas_tambahan || $izin['jurnal']?->alasan)
                                            <div class="mt-2 rounded-lg border border-surface-alt bg-surface/60 p-2 text-[11px] text-ink">
                                                <span class="font-semibold text-muted">Tugas / Alasan:</span>
                                                <p class="mt-0.5 text-muted-2">{{ $izin['jurnal']->tugas_tambahan ?? $izin['jurnal']->alasan }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        @endif
    @endif

    @if ($isWali && ! $piketHariIni)
        <a href="{{ route('guru.wali-kelas.index') }}" class="press mb-6 flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="groups" :size="24" />
            </span>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">Wali Kelas</p>
                <p class="text-xs text-muted">Lihat rekap kehadiran kelas yang Anda ampu</p>
            </div>
            <x-icon name="chevron_right" :size="20" class="text-muted" />
        </a>
    @endif

    {{-- Piket TETAP bisa nempel jadwal ngajar di hari yang sama -- kalau
         ternyata ada, tetap ditampilkan (jangan disembunyiin cuma gara-gara
         lagi piket). Kalau piket TANPA jadwal ngajar (kasus paling umum),
         bagian ini disembunyikan total -- state kosongnya ("Tidak ada jadwal
         hari ini, isi jurnal buat jadwal lain") nggak relevan buat fokus
         piket hari itu. --}}
    @if (! $piketHariIni || $jadwalHariIni->isNotEmpty())
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-ink">Jadwal Mengajar Hari Ini</h2>
            <a href="{{ route('jurnal.index') }}" class="text-sm font-semibold text-navy">Riwayat Jurnal →</a>
        </div>

        @if ($jadwalHariIni->isEmpty())
            <x-ui.empty icon="event_busy" title="Tidak ada jadwal hari ini" desc="Mau isi jurnal untuk jadwal lain? Pilih dari daftar jadwal Anda.">
                <x-ui.button :href="route('jurnal.create')" icon="edit_note" class="mt-2">Isi Jurnal</x-ui.button>
            </x-ui.empty>
        @else
            <x-ui.card-list class="grid-fill-last">
                @foreach ($jadwalHariIni as $j)
                    @php
                        $statusJamAsli = \App\Support\Waktu::statusJpHariIni($j->jam_ke_mulai, $j->jam_ke_selesai);
                        $jurnalUntukJadwal = $jurnalGuruHariIni->get($j->id);
                        $sudahIsiIni = $jurnalUntukJadwal !== null;
                        // "Sudah Lewat" polos itu ambigu -- guru bisa salah
                        // kira jamnya emang udah kelewatan padahal jurnalnya
                        // UDAH diisi, atau sebaliknya nyangka masih bisa
                        // nyusul padahal mode disiplin udah beneran nolak.
                        // Kalau jurnal sudah diisi, tampilkan status
                        // verifikasinya. Kalau BELUM diisi & mode disiplin (jamnya
                        // beneran kekunci, nggak bisa diisi lagi lewat form
                        // biasa), tegasin "Terlewat" -- beda dari "Sudah
                        // Lewat" yang kesannya masih bisa nyusul kapan aja.
                        $terlewatTerkunci = $statusJamAsli === 'lewat' && ! $sudahIsiIni
                            && \App\Models\PengaturanJurnal::mode() === 'disiplin';
                        $statusJam = $sudahIsiIni
                            ? null
                            : ($statusJamAsli === 'lewat'
                                ? ($terlewatTerkunci ? 'terlewat' : 'lewat')
                                : $statusJamAsli);
                        $statusBadges = $jurnalUntukJadwal?->statusRingkas() ?? [];
                    @endphp
                    <x-ui.list-card
                        :title="$j->mapel->nama"
                        :meta="[$j->kelas->nama . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai, 'Ruang ' . ($j->ruang ?? '-')]"
                    >
                        @if ($jurnalUntukJadwal || $statusJam)
                            <x-slot:badge>
                                @if ($jurnalUntukJadwal)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($statusBadges as $statusBadge)
                                            <x-ui.status-badge :status="$statusBadge['status']">{{ $statusBadge['label'] }}</x-ui.status-badge>
                                        @endforeach
                                    </div>
                                @else
                                    <x-ui.status-badge :status="$statusJam" />
                                @endif
                            </x-slot:badge>
                        @endif
                        <x-slot:actions>
                            @if ($sudahIsiIni)
                                <x-ui.action-button label="Sudah diisi" icon="check_circle" variant="success" href="{{ route('jurnal.index') }}" />
                            @elseif ($terlewatTerkunci)
                                <x-ui.action-button label="Terlewat" icon="block" variant="neutral" disabled />
                            @else
                                <x-ui.action-button label="Isi Jurnal" icon="edit_note" variant="info" :href="route('jurnal.create', ['jadwal' => $j->id])" />
                            @endif
                        </x-slot:actions>
                    </x-ui.list-card>
                @endforeach
            </x-ui.card-list>
        @endif
    @endif
</x-layouts.app>
