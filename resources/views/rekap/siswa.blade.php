@php
    $totalAlphaTinggi = $siswas->filter(fn ($s) => ($rekap[$s->id]['alpha'] ?? 0) >= $ambangAlpha)->count();
    $admin = auth()->user()->role === 'admin';
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Rekap Kehadiran Siswa" heading="Rekap Kehadiran Siswa" width="wide">
    @if ($admin)
        <x-admin.page title="Rekap Kehadiran Siswa" subtitle="Lintas kelas, buat evaluasi kedisiplinan">
            <x-slot:action>
                <x-ui.button :href="route('rekap.siswa.ekspor', request()->query())" variant="secondary" icon="download">Ekspor Ringkasan</x-ui.button>
            </x-slot:action>
        </x-admin.page>
    @else
        <x-page-header title="Rekap Kehadiran Siswa" subtitle="Lintas kelas, buat evaluasi kedisiplinan" always-row size="sm">
            <x-ui.button :href="route('rekap.siswa.ekspor', request()->query())" variant="secondary" icon="download" class="w-full sm:w-auto">Ekspor Ringkasan</x-ui.button>
        </x-page-header>
    @endif

    {{-- Tanggal di baris atas (2 kotak), Kelas & Cari nama sejajar di baris
         bawahnya (2 kotak juga) -- pola yang sama dipakai di seluruh app
         (rentang tanggal duluan, baru filter lain berpasangan 2-2).
         Kelas & tanggal: server-side, langsung submit begitu diubah. Cari
         nama: client-side langsung filter baris yang sudah dimuat (data di
         halaman ini nggak dipaginate), jadi nggak perlu reload cuma buat
         cari nama -- makanya SENGAJA di luar <form> (class="contents" di
         form Kelas bikin child-nya ikut jadi flex item wadah luar, tanpa
         form-nya sendiri ganggu layout), sama pola kayak Dispensasi Siswa. --}}
    <div class="mb-4 flex flex-col gap-2">
        <form method="GET" action="{{ route('rekap.siswa.index') }}" class="flex w-full items-end gap-2">
            @if(request('kelas_id')) <input type="hidden" name="kelas_id" value="{{ request('kelas_id') }}"> @endif
            <div class="flex-1">
                <x-admin.f-date name="dari" label="Dari tanggal" data-pasangan="sampai" />
            </div>
            <div class="flex-1">
                <x-admin.f-date name="sampai" label="Sampai tanggal" onchange="this.form.submit()" />
            </div>
            @if (request('dari') || request('sampai'))
                @php $sisaFilterTanggal = request()->except(['dari', 'sampai']); @endphp
                <a href="{{ url()->current() . ($sisaFilterTanggal ? '?' . http_build_query($sisaFilterTanggal) : '') }}"
                   class="flex h-10 shrink-0 items-center gap-1.5 rounded-lg border border-surface-alt bg-card px-3 text-sm font-semibold text-muted hover:border-alpha hover:text-alpha">
                    <x-icon name="close" :size="16" /> Reset
                </a>
            @endif
        </form>

        <div class="flex w-full gap-2">
            <form method="GET" action="{{ route('rekap.siswa.index') }}" class="contents">
                @if(request('dari')) <input type="hidden" name="dari" value="{{ request('dari') }}"> @endif
                @if(request('sampai')) <input type="hidden" name="sampai" value="{{ request('sampai') }}"> @endif
                <div class="flex-1">
                    <x-ui.cari-pilihan name="kelas_id" label="Kelas" :options="$kelasList" all="Semua kelas" />
                </div>
            </form>
            <div class="flex-1">
                <span class="mb-1 block text-xs font-semibold text-muted-2">Cari Siswa</span>
                <x-ui.search-bar id="cari-rekap" placeholder="Cari nama atau NIS siswa..." />
            </div>
        </div>
    </div>

    @if ($totalAlphaTinggi > 0)
        <x-alert type="warning" class="mb-4">
            <strong>{{ $totalAlphaTinggi }} siswa</strong> alpha {{ $ambangAlpha }}x atau lebih pada rentang ini — perlu perhatian.
        </x-alert>
    @endif

    @if ($terlaluBanyakTanpaFilter)
        {{-- Total siswa sekolah kebanyakan buat 1 halaman tanpa filter (lihat
             catatan di RekapController::siswa()) -- diminta pilih kelas dulu,
             bukan diam-diam render belasan ribu baris. --}}
        <x-ui.empty icon="filter_alt" title="Pilih kelas dulu"
            desc="Ada {{ $terlaluBanyakTanpaFilter }} siswa di sekolah ini -- terlalu banyak buat ditampilkan sekaligus. Pilih salah satu kelas lewat filter di atas." />
    @elseif ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa" />
    @else
        {{-- Desktop: tabel biasa. HP: kartu ringkas (bukan tabel) -- 9 kolom
             kalau ditumpuk per-field kepanjangan, jadi diganti badge angka
             sejajar tanpa label per kartu (lihat x-ui.rekap-chip-card). --}}
        <div class="hidden sm:block">
            <x-admin.table :head="['Kelas', 'No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi']">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); $alphaTinggi = ($r['alpha'] ?? 0) >= $ambangAlpha; @endphp
                    <tr data-baris-rekap data-cari="{{ strtolower($s->nama.' '.$s->nis) }}" @class(['bg-alpha-soft/30' => $alphaTinggi])>
                        <td class="px-4 py-2.5 text-muted">{{ $s->kelas?->nama ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="hadir">{{ $r['hadir'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="sakit">{{ $r['sakit'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="izin">{{ $r['izin'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="alpha" :class="$alphaTinggi ? 'ring-2 ring-alpha' : ''">{{ $r['alpha'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="dispensasi">{{ $r['dispensasi'] ?? 0 }}</x-ui.rekap-badge></td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="sm:hidden">
            <x-ui.rekap-legend />
            <div class="flex flex-col gap-2">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); $alphaTinggi = ($r['alpha'] ?? 0) >= $ambangAlpha; @endphp
                    <x-ui.rekap-chip-card
                        data-baris-rekap
                        data-cari="{{ strtolower($s->nama.' '.$s->nis) }}"
                        :nama="$s->nama"
                        :meta="($s->kelas?->nama ?? '—') . ' · No. ' . ($s->no_absen ?? '—')"
                        :hadir="$r['hadir'] ?? 0"
                        :sakit="$r['sakit'] ?? 0"
                        :izin="$r['izin'] ?? 0"
                        :alpha="$r['alpha'] ?? 0"
                        :dispensasi="$r['dispensasi'] ?? 0"
                        :sorot="$alphaTinggi"
                    />
                @endforeach
            </div>
        </div>
        <p id="rekap-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada siswa yang cocok dengan pencarian.
        </p>
        <p class="mt-3 text-xs text-muted-2">Merah muda = alpha {{ $ambangAlpha }}x atau lebih {{ ($dari || $sampai) ? 'pada rentang tanggal ini' : 'sepanjang riwayat' }}.</p>
    @endif

    @push('scripts')
        <script>
            (function () {
                const cari = document.getElementById('cari-rekap');
                const rows = document.querySelectorAll('[data-baris-rekap]');
                const kosong = document.getElementById('rekap-kosong');
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
</x-dynamic-component>
