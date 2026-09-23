@php
    $tabs = ['' => 'Semua', 'pending' => "Perlu diperiksa ({$jumlahPending})", 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Diminta revisi'];
    $badge = ['pending' => 'pending', 'terverifikasi' => 'terverifikasi', 'revisi' => 'revisi'];
@endphp

<x-layouts.app title="Verifikasi Jurnal" width="wide">
    <x-page-header title="Jurnal Kelas {{ $kelas->nama }}" subtitle="Periksa materi & presensi yang diisi guru">
        <x-ui.button :href="route('sekretaris.jurnal.pengganti')" variant="secondary" icon="edit_note">Isi Jurnal Pengganti</x-ui.button>
    </x-page-header>

    <x-ui.auto-refresh :url="route('sekretaris.jurnal.versi')" />

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('sekretaris.jurnal.index', array_merge(request()->except('status', 'page'), array_filter(['status' => $key]))) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap', 'bg-navy text-card' => $status === ($key ?: null), 'text-muted-2 hover:text-ink' => $status !== ($key ?: null)])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Rentang tanggal & dropdown Mapel -- server-side (auto-submit), sama
         pola kayak filter di Riwayat Jurnal Guru. Nggak ada filter Kelas di
         sini karena pengurus kelas emang cuma pegang 1 kelas. --}}
    <x-admin.filters :action="route('sekretaris.jurnal.index')" ignore="status">
        <input type="hidden" name="status" value="{{ $status }}">

        <div class="flex w-full gap-2">
            <x-admin.f-date name="dari" label="Dari tanggal" :value="$dari" onchange="this.form.submit()" />
            <x-admin.f-date name="sampai" label="Sampai tanggal" :value="$sampai" onchange="this.form.submit()" />
        </div>

        <x-ui.cari-pilihan name="mapel_id" label="Mata Pelajaran" :options="$mapelList" all="Semua mapel" />
    </x-admin.filters>

    {{-- Cari mapel/guru -- langsung filter baris yang sudah dimuat di
         halaman ini (tanpa reload), sama pola kayak Riwayat Jurnal Guru. --}}
    <div class="mb-4">
        <x-ui.search-bar id="cari-verifikasi-jurnal" placeholder="Cari mata pelajaran atau guru..." />
    </div>

    @if ($jurnals->isEmpty())
        <x-ui.empty icon="menu_book" title="Tidak ada jurnal" />
    @else
        <x-ui.card-list id="daftar-verifikasi-jurnal">
            @foreach ($jurnals as $j)
                <x-ui.list-card
                    data-baris-verifikasi-jurnal
                    data-cari="{{ strtolower($j->jadwal->mapel->nama.' '.$j->guru->nama) }}"
                    :title="$j->jadwal->mapel->nama"
                    :meta="[
                        $j->guru->nama . ($j->diisi_oleh_pengurus ? ' (diisi pengurus)' : ''),
                        $j->tanggal->translatedFormat('d M Y') . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai,
                    ]"
                >
                    <x-slot:badge>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-ui.status-badge :status="$badge[$j->status_verifikasi]">
                                {{ $j->status_verifikasi === 'terverifikasi' && $j->verifikasiAbsen() ? 'Dicatat' : ['pending' => 'Perlu diperiksa', 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Diminta revisi'][$j->status_verifikasi] }}
                            </x-ui.status-badge>
                            {{-- Beda dari verifikasi manusia beneran -- biar
                                 pengurus kelas nggak salah kira udah ada yang
                                 meriksa padahal cuma kesapu otomatis (lihat
                                 Jurnal::otomatisVerifikasiKalauLewatHari()). --}}
                            @if ($j->otomatisDiverifikasi())
                                <x-ui.status-badge status="otomatis">Otomatis</x-ui.status-badge>
                            @endif
                        </div>
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button
                            label="Periksa"
                            icon="fact_check"
                            variant="info"
                            data-modal-open="modal-jurnal-sekretaris"
                            data-modal-title="{{ $j->jadwal->mapel->nama }}"
                            data-ajax-url="{{ route('sekretaris.jurnal.show.fragment', $j) }}"
                        />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
        <p id="verifikasi-jurnal-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada jurnal yang cocok dengan pencarian.
        </p>

        <div class="mt-4">{{ $jurnals->links() }}</div>
    @endif

    {{-- Popup detail -- isinya di-fetch AJAX per baris, lihat initModals() di
         app.js. Satu modal dipakai bareng semua tombol "Periksa". --}}
    <x-ui.modal id="modal-jurnal-sekretaris" title="Detail Jurnal" size="lg">
        <div data-modal-ajax-target></div>
    </x-ui.modal>

    @if ($lihatJurnal)
        {{-- Dibuka lewat ?lihat=<id> (habis submit verifikasi/revisi, atau
             dari notifikasi JurnalPerluDiperiksa) -- tombol tersembunyi ini
             di-klik otomatis sekali lewat JS, biar popup-nya kebuka walau
             jurnalnya nggak ada di halaman pagination yang lagi tampil. --}}
        <button
            type="button"
            hidden
            data-auto-open-jurnal-sekretaris
            data-modal-open="modal-jurnal-sekretaris"
            data-modal-title="{{ $lihatJurnal->jadwal->mapel->nama }}"
            data-ajax-url="{{ route('sekretaris.jurnal.show.fragment', $lihatJurnal) }}"
        ></button>
        @push('scripts')
            <script>
                // window "load" (BUKAN cuma taruh <script> di bawah body) --
                // initModals() baru pasang event listener-nya pas DOMContentLoaded
                // dari app.js (dimuat sebagai module, ke-defer ke belakang), jadi
                // klik yang ditembak lebih awal dari itu nggak kena tangkap sama
                // sekali (modal-nya nggak kebuka).
                window.addEventListener('load', () => document.querySelector('[data-auto-open-jurnal-sekretaris]')?.click());
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            (function () {
                const cari = document.getElementById('cari-verifikasi-jurnal');
                const rows = document.querySelectorAll('[data-baris-verifikasi-jurnal]');
                const kosong = document.getElementById('verifikasi-jurnal-kosong');
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
