@php
    $tone = [
        'hadir' => 'bg-hadir-soft text-hadir',
        'tidak_hadir' => 'bg-alpha-soft text-alpha',
        'belum_diisi' => 'bg-sakit-soft text-sakit',
    ];
    $rentangBeda = ! $dari->isSameDay($sampai);
    $hariLabel = config('akademik.hari')[['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$dari->dayOfWeek - 1] ?? ''] ?? null;
    $admin = auth()->user()->role === 'admin';
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
            <x-slot:action>
                <x-ui.button :href="$urlEkspor" variant="secondary" icon="download" class="w-full sm:w-auto">Ekspor Ringkasan</x-ui.button>
            </x-slot:action>
        </x-admin.page>
    @else
        <x-page-header title="Monitor Piket" :subtitle="$subtitle" always-row size="sm">
            <x-ui.button :href="$urlEkspor" variant="secondary" icon="download" class="w-full sm:w-auto">Ekspor Ringkasan</x-ui.button>
        </x-page-header>
    @endif

    <x-ui.auto-refresh :url="route('piket.monitor.versi', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()])" />

    {{-- Filter status -- lewat query string (?status=...), sama pola kayak
         tab Riwayat Jurnal -- biar TETAP di tab yang sama begitu tanggal/mode
         diganti (reload halaman), bukan balik ke "Semua" terus. Jumlah
         disembunyikan kalau 0 (nggak nambah info, cuma bikin rame). --}}
    <div class="mb-4 flex gap-1 overflow-x-auto rounded-lg border border-surface-alt bg-card p-1">
        @foreach (['' => 'Semua', 'hadir' => 'Sudah Diisi', 'tidak_hadir' => 'Tidak Hadir', 'belum_diisi' => 'Belum Diisi'] as $key => $label)
            @php $jumlah = $key === '' ? $rekapTotal->sum() : ($rekapTotal[$key] ?? 0); @endphp
            <a href="{{ route('piket.monitor.index', array_merge(request()->except('status', 'page'), $key === '' ? [] : ['status' => $key])) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $statusAktif === $key, 'text-muted-2 hover:text-ink' => $statusAktif !== $key])>
                {{ $label }}
                @if ($jumlah > 0)
                    <span class="opacity-70">({{ $jumlah }})</span>
                @endif
            </a>
        @endforeach
    </div>

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
    <x-admin.filters :action="route('piket.monitor.index')" hideButtons="true">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <input type="hidden" name="status" value="{{ $statusAktif }}">
        <div class="flex w-full gap-2">
            <x-admin.f-date name="dari" label="Dari tanggal" :value="$dari->toDateString()" max="{{ today()->toDateString() }}" onchange="this.form.submit()" />
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
                @php
                    $cariGrup = str($g['label'])->lower();
                @endphp
                <div class="rounded-xl border border-surface-alt bg-card overflow-hidden" data-grup-card data-cari="{{ $cariGrup }}">
                    <div class="flex flex-nowrap items-start justify-between gap-2 p-2 sm:p-3">
                        <div class="flex items-start gap-1 sm:gap-2 min-w-0">
                            <button type="button" class="btn-toggle-tabel shrink-0 p-0.5 sm:p-1 mt-0.5 text-muted-2 hover:text-ink hover:bg-surface-alt rounded-md transition-colors" title="Sembunyikan/Tampilkan Tabel">
                                <x-icon name="keyboard_arrow_down" :size="20" class="icon-chevron transition-transform duration-200" />
                            </button>
                            <div class="min-w-0">
                                <p class="text-[13px] sm:text-sm font-bold text-ink leading-tight truncate">{{ $g['label'] }}</p>
                                <p class="text-[11px] sm:text-xs text-muted leading-snug mt-0.5" data-grup-count>
                                    {{ $g['rows']->count() }} jam pelajaran
                                    @if ($g['rekap']['belum_diisi'] ?? 0)
                                        <span class="inline-block">· <span class="font-semibold text-sakit">{{ $g['rekap']['belum_diisi'] }} belum diisi</span></span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <x-ui.action-button
                            label="Ekspor Lengkap"
                            icon="download"
                            class="!px-2 !py-1 !text-[10px] shrink-0 mt-0.5"
                            :href="route('piket.monitor.ekspor.detail', ['tipe' => $mode, 'id' => $g['id'], 'dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()])"
                        />
                    </div>

                    <div class="overflow-x-auto tabel-container border-t border-surface-alt" hidden>
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
                            <tbody class="divide-y divide-surface-alt">
                                @foreach ($g['rows'] as $b)
                                    @php
                                        $lawan = $mode === 'kelas' ? $b['jadwal']->guru->nama : $b['jadwal']->kelas->nama;
                                        $cariBaris = str($g['label'].' '.$lawan.' '.$b['jadwal']->mapel->nama)->lower();
                                        $bisaDiklik = $b['jurnal'] !== null;
                                        $tanggalBaris = \Illuminate\Support\Carbon::parse($b['tanggal']);
                                        $jamBaris = \App\Support\Waktu::rentangJam($b['jadwal']->jam_ke_mulai, $b['jadwal']->jam_ke_selesai, $tanggalBaris);
                                    @endphp
                                    <tr
                                        data-baris-monitor
                                        data-status="{{ $b['status'] }}"
                                        data-cari="{{ $cariBaris }}"
                                        @class([
                                            'bg-sakit-soft/30' => $b['status'] === 'belum_diisi',
                                            'cursor-pointer hover:bg-surface/60' => $bisaDiklik,
                                        ])
                                        @if ($bisaDiklik)
                                            data-modal-open="modal-jurnal-detail"
                                            data-modal-title="Detail Jurnal — {{ $lawan }}"
                                            data-ajax-url="{{ route('piket.monitor.jurnal', $b['jurnal']) }}"
                                            tabindex="0"
                                        @endif
                                    >
                                        @if ($rentangBeda)
                                            <td class="px-3 py-2 text-muted whitespace-nowrap">{{ $tanggalBaris->translatedFormat('d M Y') }}</td>
                                        @endif
                                        <td class="px-3 py-2 text-muted">
                                            JP {{ $b['jadwal']->jam_ke_mulai }}–{{ $b['jadwal']->jam_ke_selesai }}
                                            @if ($jamBaris)
                                                <span class="block text-[11px] text-muted-2 sm:inline sm:text-inherit">{{ $jamBaris }}</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-ink">{{ $b['jadwal']->mapel->nama }}</td>
                                        <td class="px-3 py-2 text-muted">{{ $lawan }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold {{ $tone[$b['status']] }}">
                                                {{ $b['statusLabel'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-muted">
                                            {{ $b['jurnal']->materi ?? ($b['jurnal']->tugas_tambahan ?? '—') }}
                                            @if ($bisaDiklik)
                                                <x-icon name="chevron_right" :size="16" class="ml-1 inline text-muted-2 align-middle" />
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

    @push('scripts')
        <script>
            (function () {
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

                // Toggle Accordion untuk Tabel
                const toggleBtns = document.querySelectorAll('.btn-toggle-tabel');
                toggleBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const card = btn.closest('[data-grup-card]');
                        const tabelContainer = card.querySelector('.tabel-container');
                        const icon = btn.querySelector('.icon-chevron');
                        
                        tabelContainer.hidden = !tabelContainer.hidden;
                        if (tabelContainer.hidden) {
                            icon.classList.remove('rotate-180');
                        } else {
                            icon.classList.add('rotate-180');
                        }
                    });
                });
            })();
        </script>
    @endpush
</x-dynamic-component>
