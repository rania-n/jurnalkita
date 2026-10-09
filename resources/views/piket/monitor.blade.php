@php
    $rentangBeda = ! $dari->isSameDay($sampai);
    $hariLabel = config('akademik.hari')[['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$dari->dayOfWeek - 1] ?? ''] ?? null;
    $admin = auth()->user()->role === 'admin';
    // Ekspor (PDF) beda dari sekadar lihat -- tetap dikunci guru piket/waka/
    // admin (lihat PiketController::pastikanBolehEkspor()). Tombolnya
    // disembunyikan di sini biar nggak nampilin tombol yang bakal 403 kalau
    // diklik guru biasa yang bukan piket.
    $bolehEkspor = $admin || auth()->user()->role === 'waka' || auth()->user()->isPiket();
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Monitor Piket" heading="Monitor Piket" width="wide">
    @php
        $subtitle = $rentangBeda
            ? $dari->translatedFormat('d M Y') . ' s/d ' . $sampai->translatedFormat('d M Y')
            : ($hariLabel ? $hariLabel . ', ' . $dari->translatedFormat('d M Y') : $dari->translatedFormat('d M Y') . ' — akhir pekan, tidak ada jadwal pelajaran');
    @endphp

    @php $urlEkspor = route('piket.monitor.ekspor', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]); @endphp

    @if ($admin)
        <x-admin.page title="Monitor Piket" :subtitle="$subtitle">
            @if ($bolehEkspor)
                <x-slot:action>
                    <x-ui.button :href="$urlEkspor" variant="secondary" icon="download" class="w-full !h-10 !px-4 !text-sm sm:w-auto">Ekspor Ringkasan</x-ui.button>
                </x-slot:action>
            @endif
        </x-admin.page>
    @else
        <x-page-header title="Monitor Piket" :subtitle="$subtitle" always-row size="sm">
            @if ($bolehEkspor)
                <x-ui.button :href="$urlEkspor" variant="secondary" icon="download" class="w-full !h-10 !px-4 !text-sm sm:w-auto">Ekspor Ringkasan</x-ui.button>
            @endif
        </x-page-header>
    @endif

    <x-ui.auto-refresh :url="route('piket.monitor.versi', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()])" />

    {{-- Filter status -- lewat query string (?status=...), sama pola kayak
         tab Riwayat Jurnal -- biar TETAP di tab yang sama begitu tanggal/mode
         diganti (reload halaman), bukan balik ke "Semua" terus. Jumlah
         disembunyikan kalau 0 (nggak nambah info, cuma bikin rame). --}}
    <div class="mb-4 flex flex-wrap gap-1 rounded-lg border border-surface-alt bg-card p-1">
        @foreach (['' => 'Semua', 'sudah_diisi' => 'Sudah Diisi', 'belum_diisi' => 'Belum Diisi', 'tidak_diisi' => 'Tidak Diisi'] as $key => $label)
            @php 
                $jumlah = 0;
                if ($key === '') {
                    $jumlah = $rekapTotal->sum();
                } elseif ($key === 'sudah_diisi') {
                    $jumlah = ($rekapTotal['hadir'] ?? 0) + ($rekapTotal['tidak_hadir'] ?? 0) + ($rekapTotal['terlambat'] ?? 0);
                } else {
                    $jumlah = $rekapTotal[$key] ?? 0;
                }
                
                $isActive = $statusAktif === $key || ($key === 'sudah_diisi' && in_array($statusAktif, ['hadir', 'tidak_hadir', 'terlambat']));
            @endphp
            <a href="{{ route('piket.monitor.index', array_merge(request()->except('status', 'page'), $key === '' ? [] : ['status' => $key])) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $isActive, 'bg-surface text-muted-2 hover:bg-surface-alt hover:text-ink' => !$isActive])>
                {{ $label }}
                @if ($jumlah > 0)
                    <span class="opacity-70">({{ $jumlah }})</span>
                @endif
            </a>
        @endforeach
    </div>

    @if (in_array($statusAktif, ['sudah_diisi', 'hadir', 'tidak_hadir', 'terlambat']))
        <div class="mb-4 flex flex-wrap gap-1 rounded-lg border border-surface-alt bg-card p-1">
            @foreach (['sudah_diisi' => 'Semua (Sudah Diisi)', 'hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir', 'terlambat' => 'Terlambat'] as $subKey => $subLabel)
                @php 
                    $subJumlah = 0;
                    if ($subKey === 'sudah_diisi') {
                        $subJumlah = ($rekapTotal['hadir'] ?? 0) + ($rekapTotal['tidak_hadir'] ?? 0) + ($rekapTotal['terlambat'] ?? 0);
                    } else {
                        $subJumlah = $rekapTotal[$subKey] ?? 0;
                    }
                @endphp
                <a href="{{ route('piket.monitor.index', array_merge(request()->except('status', 'page'), ['status' => $subKey])) }}"
                   @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $statusAktif === $subKey, 'bg-surface text-muted-2 hover:bg-surface-alt hover:text-ink' => $statusAktif !== $subKey])>
                    {{ $subLabel }}
                    @if ($subJumlah > 0)
                        <span class="opacity-70">({{ $subJumlah }})</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    {{-- Bar pilih: kelompokkan per kelas atau per guru --}}
    <div class="mb-4 flex gap-1 rounded-lg border border-surface-alt bg-card p-1">
        @foreach (['kelas' => 'Per Kelas', 'guru' => 'Per Guru'] as $key => $label)
            <a href="{{ route('piket.monitor.index', array_merge(request()->except('mode', 'page'), ['mode' => $key])) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap', 'bg-navy text-card' => $mode === $key, 'text-muted-2 hover:text-ink' => $mode !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Rentang tanggal -- sama pola kayak Riwayat Jurnal (Dari/Sampai).
         Max hari ini di dua-duanya -- belum ada gunanya lihat piket buat
         tanggal yang belum kejalanin. --}}
    <x-admin.filters :action="route('piket.monitor.index')" :ignore="['mode']">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <input type="hidden" name="status" value="{{ $statusAktif }}">
        <div class="flex w-full gap-2">
            {{-- BEDA dari pola Riwayat Jurnal -- di sini kosong BUKAN berarti
                 "semua riwayat" (Monitor Piket per-hari, nggak ada versi
                 "semua tanggal sekaligus"), jadi kotaknya SENGAJA tetap
                 kelihatan keisi tanggal yang lagi aktif (default hari ini),
                 nggak dikosongin kayak f-date lain. --}}
            <x-admin.f-date name="dari" label="Dari tanggal" :value="$dari->toDateString()" max="{{ today()->toDateString() }}" data-pasangan="sampai" />
            <x-admin.f-date name="sampai" label="Sampai tanggal" :value="$sampai->toDateString()" max="{{ today()->toDateString() }}" onchange="this.form.submit()" />
        </div>
    </x-admin.filters>

    <div class="mb-4">
        <x-ui.search-bar id="cari-monitor" placeholder="Cari nama guru, kelas, atau mata pelajaran..." />
    </div>

    @if ($grup->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal pelajaran" desc="Rentang tanggal ini akhir pekan semua, atau belum ada jadwal sama sekali." />
    @else
        <div class="flex flex-col gap-4" id="grup-monitor">
            @foreach ($grup as $g)
                <div class="rounded-xl border border-surface-alt bg-card overflow-hidden" data-grup-card data-cari="{{ $g['cari_meta'] ?? str($g['label'])->lower() }}">
                    <div class="flex flex-nowrap items-start justify-between gap-2 p-2 sm:p-3">
                        <div class="flex items-start gap-1 sm:gap-2 min-w-0">
                            <button type="button" class="btn-toggle-tabel shrink-0 p-0.5 sm:p-1 mt-0.5 text-muted-2 hover:text-ink hover:bg-surface-alt rounded-md transition-colors" title="Sembunyikan/Tampilkan Tabel">
                                <x-icon name="keyboard_arrow_down" :size="20" class="icon-chevron transition-transform duration-200" />
                            </button>
                            <div class="min-w-0">
                                <p class="text-[13px] sm:text-sm font-bold text-ink leading-tight truncate">{{ $g['label'] }}</p>
                                <p class="text-[11px] sm:text-xs text-muted leading-snug mt-0.5" data-grup-count>
                                    {{ $g['jumlah_baris'] }} jam pelajaran
                                    @if (($g['rekap']['tidak_diisi'] ?? 0) || ($g['rekap']['belum_diisi'] ?? 0))
                                        <span class="inline-block">· <span class="font-semibold text-sakit">{{ ($g['rekap']['tidak_diisi'] ?? 0) + ($g['rekap']['belum_diisi'] ?? 0) }} kosong</span></span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if ($bolehEkspor)
                            <x-ui.action-button
                                label="Ekspor Lengkap"
                                icon="download"
                                class="!px-2 !py-1 !text-[10px] shrink-0 mt-0.5"
                                :href="route('piket.monitor.ekspor.detail', ['tipe' => $mode, 'id' => $g['id'], 'dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()])"
                            />
                        @endif
                    </div>

                    <div class="overflow-x-auto tabel-container border-t border-surface-alt" hidden data-grup-url="{{ route('piket.monitor.grup', ['tipe' => $mode, 'id' => $g['id'], 'dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString(), 'status' => request('status')]) }}" data-grup-sudah="0">
                        <table class="responsive-table responsive-table--inline w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-surface-alt">
                                    @if ($rentangBeda)
                                        <th class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-muted-2">Tanggal</th>
                                    @endif
                                    <th class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-muted-2">Jam</th>
                                    <th class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-muted-2">Mapel</th>
                                    <th class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-muted-2">{{ $mode === 'kelas' ? 'Guru' : 'Kelas' }}</th>
                                    <th class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-muted-2">Status</th>
                                    <th class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-muted-2">Materi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-alt" data-tabel-isi>
                                <tr><td colspan="6" class="p-4 text-center text-xs text-muted-2">Memuat data...</td></tr>
                            </tbody>
                        </table>
                        <noscript>
                            <div class="p-4 text-center text-xs text-muted-2">
                                Detail jadwal per kelas dimuat otomatis saat dibuka. Aktifkan JavaScript untuk melihatnya, atau buka <a href="{{ route('piket.monitor.ekspor.detail', ['tipe' => $mode, 'id' => $g['id'], 'dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]) }}" class="font-semibold text-navy underline">ekspor lengkapnya</a>.
                            </div>
                        </noscript>
                    </div>
                </div>
            @endforeach
            <p id="monitor-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
                Tidak ada baris yang cocok dengan pencarian/filter.
            </p>
        </div>
    @endif

    {{-- Modal detail jurnal (dipakai bareng semua baris yang bisa diklik --
         isinya di-fetch AJAX per baris, lihat initModals() di app.js). --}}
    <x-ui.modal id="modal-jurnal-detail" title="Detail Jurnal" size="lg">
        <div data-modal-ajax-target></div>
    </x-ui.modal>

    @if ($lihatJurnal)
        {{-- Dibuka lewat ?lihat=<id> (habis dari notifikasi) -- tombol tersembunyi ini di-klik
             otomatis lewat script di bawah. ID modal sama kayak di loop tabel atas, isinya
             difetch AJAX. --}}
        @php
            $lawan = $mode === 'guru' ? $lihatJurnal->jadwal->kelas->nama : $lihatJurnal->guru->nama;
            $judulLihat = 'Detail Jurnal — ' . $lawan;
        @endphp
        <button type="button" class="hidden" id="btn-auto-lihat"
            data-modal-open="modal-jurnal-detail"
            data-modal-title="{{ $judulLihat }}"
            data-ajax-url="{{ route('piket.monitor.jurnal', $lihatJurnal) }}">
        </button>
    @endif

    @push('scripts')
        <script>
            (function () {
                const btnLihat = document.getElementById('btn-auto-lihat');
                if (btnLihat) {
                    window.addEventListener('load', () => {
                        btnLihat.click();
                        // Hapus ?lihat dari URL biar kalau direfresh manual (F5) nggak
                        // terus-terusan auto-buka pop up (perbaikan bug sebelumnya).
                        const url = new URL(window.location);
                        if (url.searchParams.has('lihat')) {
                            url.searchParams.delete('lihat');
                            window.history.replaceState({}, '', url);
                        }
                    });
                }

                // Filter status sekarang server-side (?status=..., lihat
                // PiketController@index) -- JS di sini cuma ngurus pencarian
                // teks di atas baris yang SUDAH difilter status dari server.
                const cari = document.getElementById('cari-monitor');
                const grupCards = document.querySelectorAll('[data-grup-card]');
                const kosong = document.getElementById('monitor-kosong');

                function terapkan() {
                    const q = (cari?.value ?? '').trim().toLowerCase();
                    let adaGrupTampil = false;

                    grupCards.forEach((card) => {
                        const rows = card.querySelectorAll('[data-baris-monitor]');
                        let tampilDiGrup = 0;

                        rows.forEach((row) => {
                            const tampil = !q || row.dataset.cari.includes(q);
                            row.hidden = !tampil;
                            if (tampil) tampilDiGrup++;
                        });

                        card.hidden = tampilDiGrup === 0;
                        if (!card.hidden) adaGrupTampil = true;
                    });

                    kosong.hidden = adaGrupTampil;
                }

                cari?.addEventListener('input', terapkan);

                // Toggle Accordion untuk Tabel + Lazy Load AJAX
                const toggleBtns = document.querySelectorAll('.btn-toggle-tabel');
                toggleBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const card = btn.closest('[data-grup-card]');
                        const tabelContainer = card.querySelector('.tabel-container');
                        const tbody = card.querySelector('[data-tabel-isi]');
                        const icon = btn.querySelector('.icon-chevron');
                        
                        tabelContainer.hidden = !tabelContainer.hidden;
                        if (tabelContainer.hidden) {
                            icon.classList.remove('rotate-180');
                        } else {
                            icon.classList.add('rotate-180');
                            
                            // Load data baris jika belum pernah diload
                            if (tabelContainer.dataset.grupSudah === '0') {
                                tabelContainer.dataset.grupSudah = '1';
                                fetch(tabelContainer.dataset.grupUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(r => r.ok ? r.text() : Promise.reject())
                                    .then(html => {
                                        tbody.innerHTML = html;
                                        // Wire ulang klik jurnal detail karena HTML baru disuntikkan
                                        // (fungsi initModals di app.js menggunakan event delegation di document, 
                                        // jadi modal-open langsung jalan tanpa butuh wiring ulang!).
                                        // Panggil ulang pencarian jika ada filter aktif.
                                        if (cari && cari.value.trim() !== '') terapkan();
                                    })
                                    .catch(() => {
                                        tabelContainer.dataset.grupSudah = '0';
                                        tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-xs text-alpha">Gagal memuat baris. Tutup dan buka kembali untuk mencoba ulang.</td></tr>';
                                    });
                            }
                        }
                    });
                });
            })();
        </script>
    @endpush
</x-dynamic-component>
