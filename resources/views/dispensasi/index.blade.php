@php
    $tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'kadaluarsa' => 'Kadaluarsa', 'ditolak' => 'Ditolak'];
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
                buka WhatsApp buat kirim ke Waka
            </a>
            kalau nggak otomatis kebuka.
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
                        Ajukan Dispensasi
                    </a>
                @endif
            </x-slot:action>
        </x-admin.page>
    @else
        <x-page-header title="Dispensasi Siswa" subtitle="Persetujuan izin keluar / tidak mengikuti pelajaran" always-row>
        <div class="flex shrink-0 flex-col items-end gap-1.5">
                @if ($bolehEkspor)
                    <a href="{{ $urlEkspor }}"
                       class="press inline-flex h-7 w-full shrink-0 items-center justify-center gap-2.5 rounded-md bg-surface-alt px-2.5 text-xs font-semibold text-ink hover:bg-[#cbd5e1]">
                        <x-icon name="download" :size="13" class="shrink-0" />
                        Ekspor Ringkasan
                    </a>
                @endif
                @if ($bolehAjukan)
                    <a href="{{ route('dispensasi.create') }}"
                       class="press inline-flex h-7 w-full shrink-0 items-center justify-center gap-1 rounded-md bg-navy px-2.5 text-xs font-semibold text-card hover:bg-navy-hover">
                        <x-icon name="add" :size="13" class="shrink-0" />
                        Ajukan Dispensasi
                    </a>
                @endif
            </div>
        </x-page-header>
    @endif

    {{-- Filter & status -- mirip monitor piket --}}
    <div class="mb-4 flex flex-col gap-2">
        {{-- Search bar -- filter langsung di DOM, tanpa reload (seperti monitor) --}}
        <div class="flex h-10 items-center gap-2 rounded-lg border border-surface-alt bg-card px-3">
            <x-icon name="search" :size="16" class="shrink-0 text-muted" />
            <input
                id="input-cari-dispen"
                type="text"
                value="{{ request('cari') }}"
                placeholder="Nama atau NIS siswa..."
                class="w-full bg-transparent text-sm text-ink outline-none placeholder:text-muted"
                autocomplete="off"
            >
            <button
                id="btn-clear-dispen"
                type="button"
                class="{{ request('cari') ? '' : 'hidden' }} shrink-0 text-muted hover:text-ink"
                aria-label="Hapus pencarian"
            >
                <x-icon name="close" :size="16" />
            </button>
        </div>

        {{-- Tombol filter status (berwarna) --}}
        @php
            $tabConfig = [
                'semua'     => ['Semua', 'bg-navy text-card',            'bg-surface-alt text-muted-2',     'bg-navy text-card',            'bg-surface-alt text-muted-2'],
                'menunggu'  => ['Menunggu', 'bg-sakit text-card',        'bg-sakit-soft text-sakit',        'bg-sakit text-card',           'bg-sakit-soft text-sakit'],
                'disetujui' => ['Disetujui', 'bg-hadir text-card',       'bg-hadir-soft text-hadir',        'bg-hadir text-card',           'bg-hadir-soft text-hadir'],
                'kadaluarsa'=> ['Kadaluarsa', 'bg-muted text-card', 'bg-surface-alt text-muted', 'bg-muted text-card', 'bg-surface-alt text-muted'],                'ditolak'   => ['Ditolak', 'bg-alpha text-card',         'bg-alpha-soft text-alpha',        'bg-alpha text-card',           'bg-alpha-soft text-alpha'],
            ];
        @endphp
        <div class="flex flex-wrap sm:flex-nowrap gap-1 sm:gap-1.5">
            @foreach ($tabConfig as $key => [$label, $aktif, $nonAktif])
                <a href="{{ route('dispensasi.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
                   class="flex-auto sm:flex-1 rounded-md sm:rounded-lg px-1.5 sm:px-2.5 py-1 sm:py-1.5 text-center text-[10.5px] sm:text-xs font-bold whitespace-nowrap {{ $tab === $key ? $aktif : $nonAktif }}">
                    {{ $label }}
                    @if(isset($jumlahTab[$key]))
                        <span class="opacity-70">({{ $jumlahTab[$key] }})</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Filter kelas & tanggal --}}
        <form method="GET" action="{{ route('dispensasi.index') }}" class="flex flex-wrap items-end gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if(request('cari')) <input type="hidden" name="cari" value="{{ request('cari') }}"> @endif

            <div class="w-full">
                <x-admin.f-select name="kelas_id" label="Kelas" :options="$kelasList->pluck('nama', 'id')" all="Semua Kelas" onchange="this.form.submit()" />
            </div>

            <div class="flex w-full gap-2">
                <div class="flex-1">
                    <x-admin.f-date name="dari" label="Dari tanggal" onchange="this.form.submit()" />
                </div>
                <div class="flex-1">
                    <x-admin.f-date name="sampai" label="Sampai tanggal" onchange="this.form.submit()" />
                </div>
            </div>
        </form>
    </div>

    @if ($items->isEmpty())
        <x-ui.empty icon="fact_check" title="Belum ada dispensasi" desc="Coba ubah filter kalau sedang mencari data tertentu." />
    @else
        <x-ui.card-list>
            @foreach ($items as $d)
                @php
                    $cariStr = strtolower(
                        $d->siswa->nama . ' ' .
                        ($d->siswa->nis ?? '') . ' ' .
                        ($d->siswa->kelas?->nama ?? '') . ' ' .
                        $d->alasan
                    );
                @endphp
                <x-ui.list-card
                    data-dispen-card
                    data-cari="{{ $cariStr }}"
                    :title="$d->siswa->nama"
                    :meta="[
                        $d->siswa->kelas?->nama . ' · ' . $d->labelTanggal(),
                        $d->labelJam() . ' · ' . str($d->alasan)->limit(40),
                        $d->surat_path ? '📎 Ada bukti terlampir' : 'Tanpa bukti',
                    ]"
                >
                    <x-slot:badge>
                        @if ($d->sudahKadaluarsa())
                            <x-ui.status-badge status="kadaluarsa">Kadaluarsa</x-ui.status-badge>
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
                            data-modal-title="{{ $d->siswa->nama }}"
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

        <p id="dispen-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada dispensasi yang cocok dengan pencarian.
        </p>
        <div class="mt-4">{{ $items->links() }}</div>
    @endif

    @push('scripts')
        <script>
            (function () {
                const input    = document.getElementById('input-cari-dispen');
                const btnClear = document.getElementById('btn-clear-dispen');
                const kartuList = document.querySelectorAll('[data-dispen-card]');
                const kosong   = document.getElementById('dispen-kosong');
                if (!input) return;

                function terapkan() {
                    const q = input.value.trim().toLowerCase();
                    let ada = false;
                    kartuList.forEach((kartu) => {
                        const cocok = !q || kartu.dataset.cari.includes(q);
                        kartu.hidden = !cocok;
                        if (cocok) ada = true;
                    });
                    if (kosong) kosong.hidden = ada;
                    btnClear?.classList.toggle('hidden', !input.value);
                }

                input.addEventListener('input', terapkan);

                // Tombol X: kosongkan dan filter ulang
                btnClear?.addEventListener('click', function () {
                    input.value = '';
                    terapkan();
                    input.focus();
                });

                // Jalankan sekali saat load (kalau ada nilai dari server)
                terapkan();
            })();
        </script>
    @endpush
</x-dynamic-component>
