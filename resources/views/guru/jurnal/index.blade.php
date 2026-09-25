@php
    $tabs = ['semua' => 'Semua', 'tugas' => 'Tugas', 'pending' => 'Belum diperiksa', 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Perlu Revisi'];
@endphp

<x-layouts.app title="Riwayat Jurnal" width="wide">
    <x-page-header title="Riwayat Jurnal" subtitle="Jurnal mengajar yang sudah Anda isi" size="sm" />

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

    @if ($jurnals->isEmpty())
        <x-ui.empty icon="menu_book" title="Belum ada jurnal" desc="Mulai isi jurnal dari beranda." />
    @else
        <x-ui.card-list id="daftar-riwayat-jurnal" class="grid-fill-last">
            @foreach ($jurnals as $j)
                @php
                    $jamJurnal = \App\Support\Waktu::rentangJam($j->jam_ke_mulai, $j->jam_ke_selesai, $j->tanggal);
                    $judul = $j->jadwal->mapel->nama . ' — ' . $j->jadwal->kelas->nama;
                @endphp
                @php
                    $statusBadges = $j->statusRingkas();
                @endphp
                <x-ui.list-card
                    :title="$judul"
                    :meta="[$j->tanggal->translatedFormat('d M Y') . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai . ($jamJurnal ? ' (' . $jamJurnal . ')' : '')]"
                >
                    <x-slot:badge>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach ($statusBadges as $statusBadge)
                                <x-ui.status-badge :status="$statusBadge['status']">{{ $statusBadge['label'] }}</x-ui.status-badge>
                            @endforeach
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

</x-layouts.app>
