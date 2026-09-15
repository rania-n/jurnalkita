@php
    $tone = [
        'hadir' => 'bg-hadir-soft text-hadir',
        'tugas' => 'bg-izin-soft text-izin',
        'tidak_hadir' => 'bg-alpha-soft text-alpha',
        'belum_diisi' => 'bg-sakit-soft text-sakit',
    ];
    $hariLabel = config('akademik.hari')[['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? ''] ?? null;
    $admin = auth()->user()->role === 'admin';
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Monitor Piket" heading="Monitor Piket" width="wide">
    @php $subtitle = $hariLabel ? $hariLabel . ', ' . $tanggal->translatedFormat('d M Y') : $tanggal->translatedFormat('d M Y') . ' — akhir pekan, tidak ada jadwal pelajaran'; @endphp

    @if ($admin)
        <x-admin.page title="Monitor Piket" :subtitle="$subtitle">
            <x-slot:action>
                <x-ui.button :href="route('piket.monitor.ekspor', ['tanggal' => $tanggal->toDateString()])" variant="secondary" icon="download">Ekspor Ringkasan</x-ui.button>
            </x-slot:action>
        </x-admin.page>
    @else
        <x-page-header title="Monitor Piket" :subtitle="$subtitle">
            <x-ui.button :href="route('piket.monitor.ekspor', ['tanggal' => $tanggal->toDateString()])" variant="secondary" icon="download">Ekspor Ringkasan</x-ui.button>
        </x-page-header>
    @endif

    <x-admin.filters :action="route('piket.monitor.index')">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <x-admin.f-date name="tanggal" label="Tanggal" :value="$tanggal->toDateString()" />
    </x-admin.filters>

    {{-- Roster shift piket hari itu — guru piket TIDAK mengajar selama shiftnya --}}
    @if ($shiftPiket->isNotEmpty())
        <div class="mb-3 rounded-xl border border-surface-alt bg-card p-3">
            <p class="mb-1.5 text-xs font-bold text-ink">Petugas Piket Hari Ini</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($shiftPiket as $p)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-surface-alt px-2.5 py-1 text-xs font-semibold text-ink">
                        <x-icon name="badge" :size="13" class="text-navy" />
                        {{ $p->guru->nama }}
                        <span class="text-muted-2">· {{ $p->mulai?->format('H:i') ?? '00:00' }}–{{ $p->selesai?->format('H:i') ?? '23:59' }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bar pilih: kelompokkan per kelas atau per guru --}}
    <div class="mb-2 flex gap-1 rounded-lg border border-surface-alt bg-card p-1">
        @foreach (['kelas' => 'Per Kelas', 'guru' => 'Per Guru'] as $key => $label)
            <a href="{{ route('piket.monitor.index', ['tanggal' => $tanggal->toDateString(), 'mode' => $key]) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap', 'bg-navy text-card' => $mode === $key, 'text-muted-2 hover:text-ink' => $mode !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Cari + filter status -- langsung filter baris yang sudah dimuat (tanpa
         reload). Pil status sekaligus nunjukin jumlahnya -- dulu ini kotak
         warna-warni terpisah di atas (isinya sama, cuma nggak bisa diklik),
         sekarang digabung jadi satu biar nggak dobel & bar-nya stretch penuh
         ngikutin lebar (bukan cuma numpuk di kiri kayak sebelumnya). --}}
    <div class="mb-4 flex flex-col gap-2">
        <x-ui.search-bar id="cari-monitor" placeholder="Cari nama guru, kelas, atau mata pelajaran..." />
        <div class="flex gap-1.5 overflow-x-auto sm:overflow-visible pb-0.5">
            <button type="button" data-status-filter=""
                data-class-aktif="bg-navy text-card" data-class-nonaktif="bg-surface-alt text-muted-2"
                class="status-filter-btn flex-shrink-0 sm:flex-1 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold whitespace-nowrap bg-navy text-card" data-active="true">
                Semua <span class="opacity-70">({{ $rekapTotal->sum() }})</span>
            </button>
            @foreach ([
                'hadir' => ['Hadir', 'bg-hadir text-card', 'bg-hadir-soft text-hadir'],
                'tugas' => ['Tugas Luar', 'bg-izin text-card', 'bg-izin-soft text-izin'],
                'tidak_hadir' => ['Tidak Hadir', 'bg-alpha text-card', 'bg-alpha-soft text-alpha'],
                'belum_diisi' => ['Belum Diisi', 'bg-sakit text-card', 'bg-sakit-soft text-sakit'],
            ] as $key => [$label, $classAktif, $classNonaktif])
                <button type="button" data-status-filter="{{ $key }}"
                    data-class-aktif="{{ $classAktif }}" data-class-nonaktif="{{ $classNonaktif }}"
                    class="status-filter-btn flex-shrink-0 sm:flex-1 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold whitespace-nowrap {{ $classNonaktif }}">
                    {{ $label }} <span class="opacity-70">({{ $rekapTotal[$key] ?? 0 }})</span>
                </button>
            @endforeach
        </div>
    </div>

    @if ($grup->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal pelajaran" desc="Tanggal ini akhir pekan, atau belum ada jadwal sama sekali." />
    @else
        <div class="flex flex-col gap-4" id="grup-monitor">
            @foreach ($grup as $g)
                @php
                    $cariGrup = str($g['label'])->lower();
                @endphp
                <div class="rounded-xl border border-surface-alt bg-card" data-grup-card data-cari="{{ $cariGrup }}">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-surface-alt p-3">
                        <div>
                            <p class="text-sm font-bold text-ink">{{ $g['label'] }}</p>
                            <p class="text-xs text-muted" data-grup-count>
                                {{ $g['rows']->count() }} jam pelajaran
                                @if ($g['rekap']['belum_diisi'] ?? 0)
                                    · <span class="font-semibold text-sakit">{{ $g['rekap']['belum_diisi'] }} belum diisi</span>
                                @endif
                            </p>
                        </div>
                        <x-ui.action-button
                            label="Ekspor Lengkap"
                            icon="download"
                            :href="route('piket.monitor.ekspor.detail', ['tipe' => $mode, 'id' => $g['id'], 'tanggal' => $tanggal->toDateString()])"
                        />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="responsive-table w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-surface-alt">
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
                                        <td class="px-3 py-2 text-muted">JP {{ $b['jadwal']->jam_ke_mulai }}–{{ $b['jadwal']->jam_ke_selesai }}</td>
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
                const cari = document.getElementById('cari-monitor');
                const filterBtns = document.querySelectorAll('.status-filter-btn');
                const grupCards = document.querySelectorAll('[data-grup-card]');
                const kosong = document.getElementById('monitor-kosong');
                let statusAktif = '';

                function terapkan() {
                    const q = (cari?.value ?? '').trim().toLowerCase();
                    let adaGrupTampil = false;

                    grupCards.forEach((card) => {
                        const rows = card.querySelectorAll('[data-baris-monitor]');
                        let tampilDiGrup = 0;

                        rows.forEach((row) => {
                            const cocokQ = !q || row.dataset.cari.includes(q);
                            const cocokStatus = !statusAktif || row.dataset.status === statusAktif;
                            const tampil = cocokQ && cocokStatus;
                            row.hidden = !tampil;
                            if (tampil) tampilDiGrup++;
                        });

                        const grupCocokNama = !q || card.dataset.cari.includes(q);
                        const tampilkanGrup = tampilDiGrup > 0 || (grupCocokNama && !statusAktif && q === '');
                        card.hidden = tampilDiGrup === 0;
                        if (!card.hidden) adaGrupTampil = true;
                    });

                    kosong.hidden = adaGrupTampil;
                }

                cari?.addEventListener('input', terapkan);
                filterBtns.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        statusAktif = btn.dataset.statusFilter;
                        filterBtns.forEach((b) => {
                            const aktif = b === btn;
                            b.dataset.active = aktif ? 'true' : 'false';
                            b.classList.remove(...(aktif ? b.dataset.classNonaktif : b.dataset.classAktif).split(' '));
                            b.classList.add(...(aktif ? b.dataset.classAktif : b.dataset.classNonaktif).split(' '));
                        });
                        terapkan();
                    });
                });
            })();
        </script>
    @endpush
</x-dynamic-component>
