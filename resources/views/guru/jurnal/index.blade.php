@php
    $statusLabel = ['pending' => 'menunggu', 'terverifikasi' => 'disetujui', 'revisi' => 'ditolak'];
    $tabs = ['semua' => 'Semua', 'pending' => 'Menunggu', 'terverifikasi' => 'Berhasil', 'revisi' => 'Perlu Revisi'];
@endphp

<x-layouts.app title="Riwayat Jurnal" width="wide">
    {{-- Judul size="sm" -- dikecilin (bukan dihilangin) biar halaman tetap
         ada kop, walau isinya sama kayak yang udah disorot di navbar/sidebar.
         alwaysRow -- tombol tetap di pojok kanan sejajar, nggak ikut melebar
         penuh layar pas HP sempit (dulu numpuk di bawah judul). Ikon
         disamakan sama menu "Isi Jurnal" di sidebar/navbar (edit_note),
         bukan ikon "add" generik. --}}
    <x-page-header title="Riwayat Jurnal" subtitle="Jurnal mengajar yang sudah Anda isi" size="sm" alwaysRow>
        <x-ui.button :href="route('jurnal.create')" icon="edit_note" class="!h-10 !px-4 !text-sm">Isi Jurnal</x-ui.button>
    </x-page-header>

    <x-ui.auto-refresh :url="route('jurnal.versi')" />

    <div class="mb-2 flex gap-1 overflow-x-auto rounded-lg border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('jurnal.index', array_merge(request()->except('status', 'page'), $key === 'semua' ? [] : ['status' => $key])) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $status === $key, 'text-muted-2 hover:text-ink' => $status !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Rentang tanggal & dropdown Kelas/Mapel -- server-side (auto-submit),
         sama kayak pola filter di Rekap/Monitor Piket/Dispensasi. --}}
    <x-admin.filters :action="route('jurnal.index')" ignore="status">
        <input type="hidden" name="status" value="{{ $status }}">

        {{-- f-date udah flex-1 sendiri (lihat komponennya) -- nggak perlu
             dibungkus div flex-1 lagi di sini, dobel malah nambah lebar
             minimum yang dipaksain & bikin gampang meluber di HP sempit. --}}
        {{-- max hari ini -- jurnal nggak mungkin ada buat tanggal yang belum
             kejalanin, nggak ada gunanya nawarin guru milih tanggal masa
             depan (pasti kosong). --}}
        <div class="flex w-full gap-2">
            <x-admin.f-date name="dari" label="Dari tanggal" :value="$dari" max="{{ today()->toDateString() }}" onchange="this.form.submit()" />
            <x-admin.f-date name="sampai" label="Sampai tanggal" :value="$sampai" max="{{ today()->toDateString() }}" onchange="this.form.submit()" />
        </div>

        <x-ui.cari-pilihan name="kelas_id" label="Kelas" :options="$kelasList" all="Semua kelas" />
        <x-ui.cari-pilihan name="mapel_id" label="Mata Pelajaran" :options="$mapelList" all="Semua mapel" />
    </x-admin.filters>

    {{-- Cari mapel/kelas -- langsung filter baris yang sudah dimuat di halaman
         ini (tanpa reload), sama kayak pola di Monitor Piket/Rekap. --}}
    <div class="mb-4">
        <x-ui.search-bar id="cari-riwayat-jurnal" placeholder="Cari mata pelajaran atau kelas..." />
    </div>

    @if ($jurnals->isEmpty())
        <x-ui.empty icon="menu_book" title="Belum ada jurnal" desc="Mulai isi jurnal dari beranda atau tombol di atas." />
    @else
        <x-ui.card-list id="daftar-riwayat-jurnal" class="grid-fill-last">
            @foreach ($jurnals as $j)
                @php
                    $jamJurnal = \App\Support\Waktu::rentangJam($j->jam_ke_mulai, $j->jam_ke_selesai, $j->tanggal);
                    $judul = $j->jadwal->mapel->nama . ' — ' . $j->jadwal->kelas->nama;
                @endphp
                @php
                    // Tidak Hadir cuma pernyataan "saya nggak masuk", bukan
                    // laporan yang beneran perlu "diverifikasi" isinya -- dari
                    // sudut pandang GURU, itu udah selesai begitu dikirim,
                    // bukan lagi "menggantung nunggu keputusan orang". Beda
                    // sama Verifikasi Jurnal punya pengurus kelas (TETAP ada
                    // antrean "Perlu diperiksa" di sana, nggak berubah) --
                    // cuma framing di Riwayat guru sendiri yang disesuaikan.
                    if ($j->status_verifikasi === 'pending' && $j->verifikasiAbsen()) {
                        [$badgeStatus, $badgeLabel] = ['otomatis', 'Terkirim'];
                    } elseif ($j->status_verifikasi === 'terverifikasi' && $j->verifikasiAbsen()) {
                        [$badgeStatus, $badgeLabel] = ['disetujui', 'Disetujui'];
                    } else {
                        $badgeStatus = $statusLabel[$j->status_verifikasi] ?? 'menunggu';
                        $badgeLabel = ['pending' => 'Menunggu verifikasi', 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Perlu revisi'][$j->status_verifikasi] ?? $j->status_verifikasi;
                    }
                @endphp
                <x-ui.list-card
                    data-baris-riwayat-jurnal
                    data-cari="{{ strtolower($judul) }}"
                    :title="$judul"
                    :meta="[$j->tanggal->translatedFormat('d M Y') . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai . ($jamJurnal ? ' (' . $jamJurnal . ')' : '')]"
                >
                    <x-slot:badge>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-ui.status-badge :status="$badgeStatus">{{ $badgeLabel }}</x-ui.status-badge>
                            @if ($j->otomatisDiverifikasi())
                                <x-ui.status-badge status="otomatis">Otomatis</x-ui.status-badge>
                            @endif
                        </div>
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button
                            label="Lihat"
                            icon="visibility"
                            data-modal-open="modal-jurnal-detail"
                            data-modal-title="{{ $judul }}"
                            data-ajax-url="{{ route('jurnal.show.fragment', $j) }}"
                        />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
        <p id="riwayat-jurnal-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada jurnal yang cocok dengan pencarian.
        </p>

        <div class="mt-4">{{ $jurnals->links() }}</div>
    @endif

    {{-- Popup detail -- isinya di-fetch AJAX per baris, lihat initModals() di
         app.js. Satu modal dipakai bareng semua tombol "Lihat". --}}
    <x-ui.modal id="modal-jurnal-detail" title="Detail Jurnal" size="lg">
        <div data-modal-ajax-target></div>
    </x-ui.modal>

    @if ($lihatJurnal)
        {{-- Dibuka lewat ?lihat=<id> (habis submit/redirect dari tempat lain
             -- lihat JurnalController@index) -- tombol tersembunyi ini di-klik
             otomatis sekali lewat JS, biar popup-nya kebuka walau jurnalnya
             nggak ada di halaman pagination yang lagi tampil.

             ?ubah=1 ikut nempel -> buka LANGSUNG ke fragment form ubah,
             bukan fragment lihat dulu. Dipakai JurnalController@update pas
             validasi submit ubah gagal, biar guru balik ke popup yang SAMA
             (bukan ilang begitu aja) lengkap sama pesan error & isian yang
             barusan diketik ($errors/old() otomatis ke-render fragment-nya). --}}
        @php
            $ubahJurnal = request()->boolean('ubah');
            $judulLihat = $lihatJurnal->jadwal->mapel->nama . ' — ' . $lihatJurnal->jadwal->kelas->nama;
        @endphp
        <button
            type="button"
            hidden
            data-auto-open-jurnal
            data-modal-open="modal-jurnal-detail"
            data-modal-title="{{ $judulLihat }}"
            data-ajax-url="{{ $ubahJurnal ? route('jurnal.edit.fragment', $lihatJurnal) : route('jurnal.show.fragment', $lihatJurnal) }}"
        ></button>
        @push('scripts')
            <script>
                // window "load" (BUKAN cuma taruh <script> di bawah body) --
                // initModals() baru pasang event listener-nya pas DOMContentLoaded
                // dari app.js (dimuat sebagai module, ke-defer ke belakang), jadi
                // klik yang ditembak lebih awal dari itu nggak kena tangkap sama
                // sekali (modal-nya nggak kebuka).
                window.addEventListener('load', () => document.querySelector('[data-auto-open-jurnal]')?.click());
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            (function () {
                const cari = document.getElementById('cari-riwayat-jurnal');
                const rows = document.querySelectorAll('[data-baris-riwayat-jurnal]');
                const kosong = document.getElementById('riwayat-jurnal-kosong');
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
