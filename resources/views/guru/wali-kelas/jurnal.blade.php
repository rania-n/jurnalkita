<x-layouts.app title="Jurnal Kelas Wali" width="wide">
    <x-page-header
        title="Jurnal Kelas {{ $kelas->nama }}"
        subtitle="Lihat materi dan presensi yang diisi guru — Anda adalah wali kelas ini"
        :back="$adaKelasLain ? route('guru.wali-kelas.index') : null"
        size="sm"
    />

    <div class="mb-4 flex flex-wrap gap-1 rounded-lg border border-surface-alt bg-card p-1">
        <a href="{{ route('guru.wali-kelas.rekap', $kelas) }}"
           class="flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap bg-surface text-muted-2 hover:bg-surface-alt hover:text-ink">
            Rekap Kehadiran
        </a>
        <a href="{{ route('guru.wali-kelas.jurnal', $kelas) }}"
           class="flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap bg-navy text-card">
            Jurnal Harian
        </a>
    </div>

    {{-- Filter status kehadiran guru -- pola sama kayak tab Riwayat Jurnal
         (server-side lewat query string, tetap di tab yang sama begitu
         tanggal diganti), jumlah disembunyikan kalau 0. --}}
    <div class="mb-4 flex flex-wrap gap-1 rounded-lg border border-surface-alt bg-card p-1">
        @foreach (['' => 'Semua', 'hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'] as $key => $label)
            <a href="{{ route('guru.wali-kelas.jurnal', array_merge(['kelas' => $kelas], request()->except('status_guru', 'page'), $key === '' ? [] : ['status_guru' => $key])) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $statusGuru === $key, 'bg-surface text-muted-2 hover:bg-surface-alt hover:text-ink' => $statusGuru !== $key])>
                {{ $label }}
                @if (($jumlahTab[$key === '' ? 'semua' : $key] ?? 0) > 0)
                    <span class="opacity-70">({{ $jumlahTab[$key === '' ? 'semua' : $key] }})</span>
                @endif
            </a>
        @endforeach
    </div>

    <x-admin.filters :action="route('guru.wali-kelas.jurnal', $kelas)">
        <input type="hidden" name="status_guru" value="{{ $statusGuru }}">
        <x-admin.f-date name="dari" label="Dari tanggal" max="{{ today()->toDateString() }}" data-pasangan="sampai" />
        <x-admin.f-date name="sampai" label="Sampai tanggal" max="{{ today()->toDateString() }}" onchange="this.form.submit()" />
    </x-admin.filters>

    <div class="mb-4 mt-4">
        <x-ui.search-bar id="cari-jurnal-wali" placeholder="Cari mata pelajaran atau guru..." />
    </div>

    @if ($jurnals->isEmpty())
        <x-ui.empty icon="menu_book" title="Belum ada jurnal" desc="Belum ada jurnal yang diisi guru pada rentang tanggal ini." />
    @else
        <x-ui.card-list id="daftar-jurnal-wali" class="grid-fill-last">
            @foreach ($jurnals as $j)
                @php
                    $statusTampilan = $j->verifikasiAbsen() ? 'terverifikasi' : ($j->menungguPemeriksaan() ? 'pending' : $j->status_verifikasi);
                    $badge = ['pending' => 'pending', 'terverifikasi' => 'terverifikasi', 'revisi' => 'revisi'];
                @endphp
                <x-ui.list-card
                    data-baris-jurnal-wali
                    data-cari="{{ strtolower($j->jadwal->mapel->nama.' '.$j->guru->nama) }}"
                    :title="$j->jadwal->mapel->nama"
                    :meta="[
                        $j->guru->nama . ($j->diisi_oleh_pengurus ? ' (diisi pengurus)' : ''),
                        $j->tanggal->translatedFormat('d M Y') . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai,
                    ]"
                >
                    <x-slot:badge>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-ui.status-badge :status="$badge[$statusTampilan]">
                                {{ $statusTampilan === 'terverifikasi' && $j->verifikasiAbsen() ? 'Disetujui' : ['pending' => 'Perlu diperiksa', 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Diminta revisi'][$statusTampilan] }}
                            </x-ui.status-badge>
                            @if ($j->verifikasiAbsen())
                                <x-ui.status-badge status="tugas">Tugas</x-ui.status-badge>
                            @endif
                            @if ($j->terlambat)
                                <x-ui.status-badge status="alpha">Terlambat</x-ui.status-badge>
                            @endif
                        </div>
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button
                            label="Lihat"
                            icon="visibility"
                            variant="info"
                            data-modal-open="modal-jurnal-wali"
                            data-modal-title="{{ $j->jadwal->mapel->nama }}"
                            data-ajax-url="{{ route('guru.wali-kelas.jurnal.fragment', $j) }}"
                        />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
        <p id="jurnal-wali-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada jurnal yang cocok dengan pencarian.
        </p>

        <div class="mt-4">{{ $jurnals->links() }}</div>
    @endif

    <x-ui.modal id="modal-jurnal-wali" title="Detail Jurnal" size="lg">
        <div data-modal-ajax-target></div>
    </x-ui.modal>

    @push('scripts')
        <script>
            (function () {
                const cari = document.getElementById('cari-jurnal-wali');
                const rows = document.querySelectorAll('[data-baris-jurnal-wali]');
                const kosong = document.getElementById('jurnal-wali-kosong');
                if (!cari) return;

                cari.addEventListener('input', () => {
                    const q = cari.value.trim().toLowerCase();
                    let ada = false;
                    rows.forEach((row) => {
                        const cocok = !q || row.dataset.cari.includes(q);
                        row.hidden = !cocok;
                        if (cocok) ada = true;
                    });
                    if (kosong) kosong.hidden = ada;
                });
            })();
        </script>
    @endpush
</x-layouts.app>
