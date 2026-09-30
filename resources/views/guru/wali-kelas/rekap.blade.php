<x-layouts.app title="Rekap Kelas Wali" width="wide">
    <x-page-header
        title="Rekap Kehadiran Kelas"
        :subtitle="$kelas->nama . ' · Anda adalah wali kelas ini'"
        :back="$adaKelasLain ? route('guru.wali-kelas.index') : null"
        size="sm"
    />

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-lg border border-surface-alt bg-card p-1">
        <a href="{{ route('guru.wali-kelas.rekap', $kelas) }}"
           class="flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap bg-navy text-card">
            Rekap Kehadiran
        </a>
        <a href="{{ route('guru.wali-kelas.jurnal', $kelas) }}"
           class="flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap text-muted-2 hover:text-ink">
            Jurnal Harian
        </a>
    </div>

    {{-- Tanggal: server-side, langsung submit begitu diubah. Cari nama:
         client-side langsung filter baris yang sudah dimuat -- sama pola
         kayak Rekap Kehadiran Siswa (Waka). --}}
    <x-admin.filters :action="route('guru.wali-kelas.rekap', $kelas)">
        <input type="hidden" name="tipe" id="filter-tipe" value="{{ $tipe }}" />
        <x-admin.f-date name="dari" label="Dari tanggal" data-pasangan="sampai" />
        <x-admin.f-date name="sampai" label="Sampai tanggal" onchange="this.form.submit()" />
    </x-admin.filters>

    {{-- Bar Toggle Per Hari vs Per Mapel & Hint Klik Detail --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="inline-flex rounded-xl border border-surface-alt bg-card p-1 shadow-xs text-xs font-semibold">
            <button type="button"
                id="btn-tipe-hari"
                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition-all {{ $tipe === 'hari' ? 'bg-navy text-card' : 'text-muted-2 hover:text-ink' }}"
                data-switch-tipe="hari">
                <x-icon name="calendar_today" :size="16" />
                <span>Per Hari</span>
            </button>
            <button type="button"
                id="btn-tipe-mapel"
                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 transition-all {{ $tipe === 'mapel' ? 'bg-navy text-card' : 'text-muted-2 hover:text-ink' }}"
                data-switch-tipe="mapel">
                <x-icon name="menu_book" :size="16" />
                <span>Per Mapel</span>
            </button>
        </div>
        <p class="text-xs text-muted-2 flex items-center gap-1.5">
            <x-icon name="touch_app" :size="15" />
            <span>Klik baris / kartu siswa untuk melihat rincian mapel & riwayat</span>
        </p>
    </div>

    <div class="mb-4">
        <x-ui.search-bar id="cari-rekap-wali" placeholder="Cari nama siswa..." />
    </div>

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa di kelas ini" />
    @else
        <div class="hidden sm:block">
            <x-admin.table :head="['No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi']">
                @foreach ($siswas as $s)
                    @php
                        $rHari = $rekapHari[$s->id] ?? collect();
                        $rMapel = $rekapMapel[$s->id] ?? collect();
                    @endphp
                    <tr
                        data-rekap-item
                        data-cari="{{ strtolower($s->nama) }}"
                        data-modal-open="modal-rekap-siswa"
                        data-modal-title="Detail Kehadiran: {{ $s->nama }}"
                        data-ajax-url="{{ route('guru.wali-kelas.rekap.siswa.fragment', array_filter(['kelas' => $kelas->id, 'siswa' => $s->id, 'dari' => $dari?->toDateString(), 'sampai' => $sampai?->toDateString()])) }}"
                        class="cursor-pointer hover:bg-surface-alt/40 transition-colors"
                        title="Klik untuk melihat rincian mapel & riwayat"
                    >
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">
                            <div class="flex items-center justify-between gap-2">
                                <span>{{ $s->nama }}</span>
                                <x-icon name="chevron_right" :size="16" class="text-muted-2 opacity-50" />
                            </div>
                        </td>
                        <td class="px-4 py-2.5">
                            <span data-tipe="hari" class="{{ $tipe === 'hari' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="hadir">{{ $rHari['hadir'] ?? 0 }}</x-ui.rekap-badge></span>
                            <span data-tipe="mapel" class="{{ $tipe === 'mapel' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="hadir">{{ $rMapel['hadir'] ?? 0 }}</x-ui.rekap-badge></span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span data-tipe="hari" class="{{ $tipe === 'hari' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="sakit">{{ $rHari['sakit'] ?? 0 }}</x-ui.rekap-badge></span>
                            <span data-tipe="mapel" class="{{ $tipe === 'mapel' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="sakit">{{ $rMapel['sakit'] ?? 0 }}</x-ui.rekap-badge></span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span data-tipe="hari" class="{{ $tipe === 'hari' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="izin">{{ $rHari['izin'] ?? 0 }}</x-ui.rekap-badge></span>
                            <span data-tipe="mapel" class="{{ $tipe === 'mapel' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="izin">{{ $rMapel['izin'] ?? 0 }}</x-ui.rekap-badge></span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span data-tipe="hari" class="{{ $tipe === 'hari' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="alpha">{{ $rHari['alpha'] ?? 0 }}</x-ui.rekap-badge></span>
                            <span data-tipe="mapel" class="{{ $tipe === 'mapel' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="alpha">{{ $rMapel['alpha'] ?? 0 }}</x-ui.rekap-badge></span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span data-tipe="hari" class="{{ $tipe === 'hari' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="dispensasi">{{ $rHari['dispensasi'] ?? 0 }}</x-ui.rekap-badge></span>
                            <span data-tipe="mapel" class="{{ $tipe === 'mapel' ? '' : 'hidden' }}"><x-ui.rekap-badge tone="dispensasi">{{ $rMapel['dispensasi'] ?? 0 }}</x-ui.rekap-badge></span>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="sm:hidden">
            <x-ui.rekap-legend />
            <div class="flex flex-col gap-2">
                @foreach ($siswas as $s)
                    @php
                        $rHari = $rekapHari[$s->id] ?? collect();
                        $rMapel = $rekapMapel[$s->id] ?? collect();
                    @endphp
                    <div data-rekap-item data-cari="{{ strtolower($s->nama) }}">
                        <div data-tipe="hari" class="{{ $tipe === 'hari' ? '' : 'hidden' }}">
                            <x-ui.rekap-chip-card
                                data-modal-open="modal-rekap-siswa"
                                data-modal-title="Detail Kehadiran: {{ $s->nama }}"
                                data-ajax-url="{{ route('guru.wali-kelas.rekap.siswa.fragment', array_filter(['kelas' => $kelas->id, 'siswa' => $s->id, 'dari' => $dari?->toDateString(), 'sampai' => $sampai?->toDateString()])) }}"
                                class="cursor-pointer hover:border-navy/40 transition"
                                :nama="$s->nama"
                                :meta="'No. ' . ($s->no_absen ?? '—')"
                                :hadir="$rHari['hadir'] ?? 0"
                                :sakit="$rHari['sakit'] ?? 0"
                                :izin="$rHari['izin'] ?? 0"
                                :alpha="$rHari['alpha'] ?? 0"
                                :dispensasi="$rHari['dispensasi'] ?? 0"
                            />
                        </div>
                        <div data-tipe="mapel" class="{{ $tipe === 'mapel' ? '' : 'hidden' }}">
                            <x-ui.rekap-chip-card
                                data-modal-open="modal-rekap-siswa"
                                data-modal-title="Detail Kehadiran: {{ $s->nama }}"
                                data-ajax-url="{{ route('guru.wali-kelas.rekap.siswa.fragment', array_filter(['kelas' => $kelas->id, 'siswa' => $s->id, 'dari' => $dari?->toDateString(), 'sampai' => $sampai?->toDateString()])) }}"
                                class="cursor-pointer hover:border-navy/40 transition"
                                :nama="$s->nama"
                                :meta="'No. ' . ($s->no_absen ?? '—')"
                                :hadir="$rMapel['hadir'] ?? 0"
                                :sakit="$rMapel['sakit'] ?? 0"
                                :izin="$rMapel['izin'] ?? 0"
                                :alpha="$rMapel['alpha'] ?? 0"
                                :dispensasi="$rMapel['dispensasi'] ?? 0"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <p id="rekap-wali-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada siswa yang cocok dengan pencarian.
        </p>
        <p class="mt-3 text-xs text-muted-2">
            <span id="ket-tipe-hari" class="{{ $tipe === 'hari' ? '' : 'hidden' }}">
                Dihitung <strong>per hari</strong> (kehadiran harian) dari jurnal yang sudah diisi {{ ($dari || $sampai) ? 'pada rentang tanggal ini' : 'sepanjang riwayat' }}.
            </span>
            <span id="ket-tipe-mapel" class="{{ $tipe === 'mapel' ? '' : 'hidden' }}">
                Dihitung <strong>per mapel</strong> (jam pelajaran) dari jurnal yang sudah diisi {{ ($dari || $sampai) ? 'pada rentang tanggal ini' : 'sepanjang riwayat' }}.
            </span>
            Belum termasuk jam pelajaran yang jurnalnya belum diisi guru.
        </p>
    @endif

    {{-- Modal Popup Rincian Siswa --}}
    <x-ui.modal id="modal-rekap-siswa" title="Detail Rekap Kehadiran" size="lg">
        <div data-modal-ajax-target></div>
    </x-ui.modal>

    @push('scripts')
        <script>
            (function () {
                // 1. Toggle Per Hari vs Per Mapel
                const btnHari = document.getElementById('btn-tipe-hari');
                const btnMapel = document.getElementById('btn-tipe-mapel');
                const filterTipe = document.getElementById('filter-tipe');
                const ketHari = document.getElementById('ket-tipe-hari');
                const ketMapel = document.getElementById('ket-tipe-mapel');

                function switchTipe(target) {
                    if (filterTipe) filterTipe.value = target;

                    if (target === 'hari') {
                        btnHari?.classList.add('bg-navy', 'text-card');
                        btnHari?.classList.remove('text-muted-2', 'hover:text-ink');
                        btnMapel?.classList.remove('bg-navy', 'text-card');
                        btnMapel?.classList.add('text-muted-2', 'hover:text-ink');
                        if (ketHari) ketHari.classList.remove('hidden');
                        if (ketMapel) ketMapel.classList.add('hidden');
                    } else {
                        btnMapel?.classList.add('bg-navy', 'text-card');
                        btnMapel?.classList.remove('text-muted-2', 'hover:text-ink');
                        btnHari?.classList.remove('bg-navy', 'text-card');
                        btnHari?.classList.add('text-muted-2', 'hover:text-ink');
                        if (ketHari) ketHari.classList.add('hidden');
                        if (ketMapel) ketMapel.classList.remove('hidden');
                    }

                    document.querySelectorAll('[data-tipe="hari"]').forEach((el) => {
                        el.classList.toggle('hidden', target !== 'hari');
                    });
                    document.querySelectorAll('[data-tipe="mapel"]').forEach((el) => {
                        el.classList.toggle('hidden', target !== 'mapel');
                    });

                    const url = new URL(window.location);
                    url.searchParams.set('tipe', target);
                    window.history.replaceState({}, '', url);
                }

                btnHari?.addEventListener('click', () => switchTipe('hari'));
                btnMapel?.addEventListener('click', () => switchTipe('mapel'));

                // 2. Client-side search siswa
                const cari = document.getElementById('cari-rekap-wali');
                const items = document.querySelectorAll('[data-rekap-item]');
                const kosong = document.getElementById('rekap-wali-kosong');
                if (!cari) return;

                cari.addEventListener('input', () => {
                    const q = cari.value.trim().toLowerCase();
                    let ada = false;
                    items.forEach((item) => {
                        const cocok = !q || item.dataset.cari.includes(q);
                        item.hidden = !cocok;
                        if (cocok) ada = true;
                    });
                    if (kosong) kosong.hidden = ada;
                });
            })();
        </script>
    @endpush
</x-layouts.app>
