@php
    $tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'kadaluarsa' => 'Kedaluwarsa', 'ditolak' => 'Ditolak'];
    // Admin lihat halaman ini lewat sidebar admin -- pakai shell admin (topbar,
    // sidebar) yang sama biar nggak berasa pindah ke "app lain". Guru piket & waka
    // tetap pakai shell mobile mereka sendiri.
    $admin = auth()->user()->role === 'admin';
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Dispensasi" heading="Dispensasi Siswa" width="wide">
    @php $urlEkspor = route('dispensasi.ekspor', request()->query()); @endphp

    @if ($waLinkAutoKirim)
        {{-- Baru diajukan -> langsung dibukakan WhatsApp ke Waka lewat tautan
             ini (di-klik otomatis via JS), biar piket nggak perlu tap "Kirim
             Link" lagi. Tetap tampilkan tautannya kelihatan (bukan disembunyikan)
             buat jaga-jaga kalau browser blokir auto-open-nya -- tinggal tap
             manual. Sengaja DIBUKA DI SINI (Riwayat), BUKAN di halaman detail
             -- biar kalau kirim WA-nya dibatalkan, baliknya ke daftar (netral),
             bukan nyangkut di halaman form/detail. --}}
        <x-alert type="info" class="mb-4">
            Dispensasi diajukan —
            <a href="{{ $waLinkAutoKirim }}" id="link-wa-auto-kirim" target="_blank" rel="noopener" class="font-bold underline">
                buka WhatsApp untuk mengirim ke Waka
            </a>
            jika tidak terbuka secara otomatis.
        </x-alert>
        @push('scripts')
            <script>document.getElementById('link-wa-auto-kirim')?.click();</script>
        @endpush
    @endif

    @if ($admin)
        <x-admin.page title="Dispensasi Siswa" subtitle="Persetujuan izin keluar / tidak mengikuti pelajaran">
            <x-slot:action>
                {{-- Desain tombol kecil dari Fitra dipertahankan -- ditambah
                     w-full sm:w-auto biar tetap stretch penuh di HP, sama kayak
                     pola tombol header lain di seluruh app. --}}
                @if ($bolehEkspor)
                    <a href="{{ $urlEkspor }}"
                       class="press inline-flex h-7 w-full shrink-0 items-center justify-center gap-2.5 rounded-md bg-surface-alt px-2.5 text-xs font-semibold text-ink hover:bg-[#cbd5e1] sm:w-auto">
                        <x-icon name="download" :size="13" class="shrink-0" />
                        Ekspor Ringkasan
                    </a>
                @endif
                @if ($bolehAjukan)
                    <a href="{{ route('dispensasi.create') }}"
                       class="press inline-flex h-7 w-full shrink-0 items-center justify-center gap-1 rounded-md bg-navy px-2.5 text-xs font-semibold text-card hover:bg-navy-hover sm:w-auto">
                        <x-icon name="add" :size="13" class="shrink-0" />
                        Buat Dispen
                    </a>
                @endif
            </x-slot:action>
        </x-admin.page>
    @else
        {{-- Ukuran tombol disamakan sama pola header lain di app (x-ui.button)
             -- !h-10 dkk override h-12 bawaan komponen (pola yang sama kayak
             notifikasi/index.blade.php), biar nggak sebesar tombol form biasa
             tapi tetap lebih jelas dari desain kecil kustom sebelumnya. --}}
        <x-page-header title="Dispensasi Siswa" subtitle="Persetujuan izin keluar / tidak mengikuti pelajaran" always-row size="sm">
            @if ($bolehEkspor)
                <x-ui.button :href="$urlEkspor" variant="secondary" icon="download" class="w-full !h-10 !px-4 !text-sm sm:w-auto">Ekspor Ringkasan</x-ui.button>
            @endif
            @if ($bolehAjukan)
                <x-ui.button :href="route('dispensasi.create')" icon="add" class="w-full !h-10 !px-4 !text-sm sm:w-auto">Ajukan Dispensasi</x-ui.button>
            @endif
        </x-page-header>
    @endif

    <x-ui.auto-refresh :url="route('dispensasi.versi')" />

    {{-- Filter status -- paling atas, gaya tab disamakan dengan Riwayat
         Jurnal/Monitor Piket (bg-navy pas aktif), bukan warna per-status
         kayak sebelumnya. Jumlah disembunyikan kalau 0. --}}
    <div class="mb-4 flex gap-1 overflow-x-auto rounded-lg border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            @php $jumlah = $jumlahTab[$key] ?? 0; @endphp
            <a href="{{ route('dispensasi.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $tab === $key, 'text-muted-2 hover:text-ink' => $tab !== $key])>
                {{ $label }}
                @if ($jumlah > 0)
                    <span class="opacity-70">({{ $jumlah }})</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Tanggal di baris atas (2 kotak), Kelas & Cari sejajar di baris
         bawahnya (2 kotak juga) -- rentang tanggal duluan, baru filter lain
         berpasangan 2-2. Search bar SENGAJA di luar <form> (class="contents"
         di form bikin child-nya ikut jadi flex item wadah luar, tanpa
         form-nya sendiri ganggu layout) -- soalnya x-ui.search-bar
         defaultnya punya atribut name="q" yang nggak dipakai controller ini
         (filternya client-side, lihat script bawah); kalau dia di DALAM form,
         "q" bakal ikut kekirim & numpuk jadi query string nggak berguna
         tiap Kelas/Tanggal diganti. Teks cari tetap dibawa lewat hidden
         input "cari" yang sudah ada. --}}
    <div class="mb-4 flex flex-col gap-2">
        <form method="GET" action="{{ route('dispensasi.index') }}" class="flex w-full items-end gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if(request('cari')) <input type="hidden" name="cari" value="{{ request('cari') }}"> @endif
            @if(request('kelas_id')) <input type="hidden" name="kelas_id" value="{{ request('kelas_id') }}"> @endif
            <div class="flex-1">
                <x-admin.f-date name="dari" label="Dari tanggal" data-pasangan="sampai" />
            </div>
            <div class="flex-1">
                <x-admin.f-date name="sampai" label="Sampai tanggal" onchange="this.form.submit()" />
            </div>
            @if (request('dari') || request('sampai'))
                @php
                    $sisaFilterTanggal = request()->except(['dari', 'sampai']);
                @endphp
                <a href="{{ url()->current() . ($sisaFilterTanggal ? '?' . http_build_query($sisaFilterTanggal) : '') }}"
                   class="flex h-10 shrink-0 items-center gap-1.5 rounded-lg border border-surface-alt bg-card px-3 text-sm font-semibold text-muted hover:border-alpha hover:text-alpha">
                    <x-icon name="close" :size="16" /> Reset
                </a>
            @endif
        </form>

        <div class="flex w-full gap-2">
            <div class="flex-1">
                {{-- Label ditambah manual (x-ui.search-bar nggak punya prop
                     label) -- gaya disamain persis kayak label "Kelas"/"Dari
                     tanggal" di sebelahnya (x-admin.f-date), biar nggak
                     keliatan beda sendiri kosong tanpa keterangan. --}}
                <span class="mb-1 block text-xs font-semibold text-muted-2">Cari Siswa</span>
                <x-ui.search-bar id="input-cari-dispen" value="{{ request('cari') }}" placeholder="Nama atau NIS siswa..." autocomplete="off" />
            </div>
            <form method="GET" action="{{ route('dispensasi.index') }}" class="contents">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if(request('cari')) <input type="hidden" name="cari" value="{{ request('cari') }}"> @endif
                @if(request('dari')) <input type="hidden" name="dari" value="{{ request('dari') }}"> @endif
                @if(request('sampai')) <input type="hidden" name="sampai" value="{{ request('sampai') }}"> @endif
                <div class="flex-1">
                    <x-ui.cari-pilihan name="kelas_id" label="Kelas" :options="$kelasList" all="Semua Kelas" />
                </div>
            </form>
        </div>
    </div>

    @if ($items->isEmpty())
        <x-ui.empty icon="fact_check" title="Belum ada dispensasi" desc="Coba ubah filter jika Anda sedang mencari data tertentu." />
    @else
        <x-ui.card-list class="grid-fill-last">
            @foreach ($items as $d)
                @php
                    $anggota = $d->anggotaKelompok();
                    $namaAnggota = $anggota->pluck('siswa.nama')->join(', ');
                    $cariStr = strtolower(
                        $namaAnggota . ' ' .
                        ($d->siswa->nis ?? '') . ' ' .
                        ($d->siswa->kelas?->nama ?? '') . ' ' .
                        $d->alasan
                    );
                @endphp
                <x-ui.list-card
                    data-dispen-card
                    data-cari="{{ $cariStr }}"
                    :title="$anggota->count() > 1 ? $anggota->count().' siswa' : $d->siswa->nama"
                    :meta="[
                        $namaAnggota,
                        $anggota->pluck('siswa.kelas.nama')->filter()->unique()->join(', ') . ' · ' . $d->labelTanggal(),
                        $d->labelJam() . ' · ' . str($d->alasan)->limit(40),
                        $d->surat_path ? '📎 Ada bukti terlampir' : 'Tanpa bukti',
                    ]"
                >
                    <x-slot:badge>
                        @if ($d->sudahKadaluarsa())
                            <x-ui.status-badge status="kadaluarsa">Kedaluwarsa</x-ui.status-badge>
                        @else
                            <x-ui.status-badge :status="['pending' => 'menunggu', 'approved' => 'disetujui', 'rejected' => 'ditolak'][$d->status_akhir]">
                                {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$d->status_akhir] }}
                            </x-ui.status-badge>
                        @endif
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button
                            label="Detail"
                            icon="badge"
                            data-modal-open="modal-dispensasi-detail"
                            data-modal-title="{{ $anggota->count() > 1 ? 'Dispensasi '.$anggota->count().' Siswa' : $d->siswa->nama }}"
                            data-ajax-url="{{ route('dispensasi.show.fragment', $d) }}"
                        />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>

        {{-- Popup detail -- isinya di-fetch AJAX per baris, lihat initModals()
             di app.js. Satu modal dipakai bareng semua tombol "Detail". --}}
        <x-ui.modal id="modal-dispensasi-detail" title="Detail Dispensasi" size="lg">
            <div data-modal-ajax-target></div>
        </x-ui.modal>

        {{-- Popup Surat + QR -- dipicu dari TOMBOL DI DALAM popup Detail di
             atas (tombol "Lihat Surat + QR" ada di fragment yang di-inject ke
             situ), tapi modalnya sendiri harus ada di sini (bukan ikut fragment). --}}
        <x-ui.modal id="modal-surat-dispensasi" title="Surat Dispensasi">
            <div data-modal-ajax-target></div>
        </x-ui.modal>

        @if ($lihatDispensasi)
            {{-- Dibuka lewat ?lihat=<id> (habis keputusan Waka/notifikasi/dll --
                 lihat DispensasiController@index) -- tombol tersembunyi ini
                 di-klik otomatis sekali lewat JS, biar popup-nya kebuka walau
                 dispensasinya nggak ada di halaman pagination yang lagi tampil. --}}
            <button
                type="button"
                hidden
                data-auto-open-dispensasi
                data-modal-open="modal-dispensasi-detail"
                data-modal-title="{{ $lihatDispensasi->jumlahAnggota() > 1 ? 'Dispensasi '.$lihatDispensasi->jumlahAnggota().' Siswa' : $lihatDispensasi->siswa->nama }}"
                data-ajax-url="{{ route('dispensasi.show.fragment', $lihatDispensasi) }}"
            ></button>
            @push('scripts')
                <script>
                    // window "load" (BUKAN cuma taruh <script> di bawah body) --
                    // initModals() baru pasang event listener-nya pas DOMContentLoaded
                    // dari app.js (dimuat sebagai module, ke-defer ke belakang), jadi
                    // klik yang ditembak lebih awal dari itu nggak kena tangkap sama
                    // sekali (modal-nya nggak kebuka).
                    window.addEventListener('load', () => document.querySelector('[data-auto-open-dispensasi]')?.click());
                </script>
            @endpush
        @endif

        <p id="dispen-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada dispensasi yang cocok dengan pencarian.
        </p>
        <div class="mt-4">{{ $items->links() }}</div>
    @endif

    @push('scripts')
        <script>
            (function () {
                const input    = document.getElementById('input-cari-dispen');
                const kartuList = document.querySelectorAll('[data-dispen-card]');
                const kosong   = document.getElementById('dispen-kosong');
                if (!input) return;

                // Kotak cari ini sekarang nempel di dalam <form> (biar sejajar
                // sama Kelas) -- cegah Enter ikut nge-submit form (reload),
                // soalnya filternya emang cuma client-side, nggak perlu reload.
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') e.preventDefault();
                });

                function terapkan() {
                    const q = input.value.trim().toLowerCase();
                    let ada = false;
                    kartuList.forEach((kartu) => {
                        const cocok = !q || kartu.dataset.cari.includes(q);
                        kartu.hidden = !cocok;
                        if (cocok) ada = true;
                    });
                    if (kosong) kosong.hidden = ada;
                }

                input.addEventListener('input', terapkan);

                // Jalankan sekali saat load (kalau ada nilai dari server)
                terapkan();
            })();
        </script>
    @endpush
</x-dynamic-component>
