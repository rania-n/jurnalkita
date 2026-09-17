@php
    $totalAlphaTinggi = $siswas->filter(fn ($s) => ($rekap[$s->id]['alpha'] ?? 0) >= $ambangAlpha)->count();
    $admin = auth()->user()->role === 'admin';
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Rekap Kehadiran Siswa" heading="Rekap Kehadiran Siswa" width="wide">
    @if ($admin)
        <x-admin.page title="Rekap Kehadiran Siswa" subtitle="Lintas kelas, buat evaluasi kedisiplinan">
            <x-slot:action>
                <x-ui.button :href="route('rekap.siswa.ekspor', request()->query())" variant="secondary" icon="download">Ekspor CSV</x-ui.button>
            </x-slot:action>
        </x-admin.page>
    @else
        <x-page-header title="Rekap Kehadiran Siswa" subtitle="Lintas kelas, buat evaluasi kedisiplinan">
            <x-ui.button :href="route('rekap.siswa.ekspor', request()->query())" variant="secondary" icon="download">Ekspor CSV</x-ui.button>
        </x-page-header>
    @endif

    {{-- Kelas & tanggal: server-side, langsung submit begitu diubah (otomatis,
         nggak perlu tombol Cari lagi) -- sama kayak pola Monitor Piket.
         Cari nama: client-side langsung filter baris yang sudah dimuat (data
         di halaman ini nggak dipaginate, semua siswa yang cocok kelas/tanggal
         udah ada), jadi nggak perlu reload cuma buat cari nama. --}}
    <x-admin.filters :action="route('rekap.siswa.index')" hideButtons="true">
        {{-- f-select udah auto-submit bawaan (this.form.requestSubmit() di
             komponennya sendiri), nggak perlu ditambah apa-apa lagi. --}}
        <x-admin.f-select name="kelas_id" label="Kelas" :options="$kelasList->pluck('nama', 'id')" all="Semua kelas" />
        <x-admin.f-date name="dari" label="Dari tanggal" :value="$dari->toDateString()" onchange="this.form.submit()" />
        <x-admin.f-date name="sampai" label="Sampai tanggal" :value="$sampai->toDateString()" onchange="this.form.submit()" />
    </x-admin.filters>

    <div class="mb-4">
        <x-ui.search-bar id="cari-rekap" placeholder="Cari nama atau NIS siswa..." />
    </div>

    @if ($totalAlphaTinggi > 0)
        <x-alert type="warning" class="mb-4">
            <strong>{{ $totalAlphaTinggi }} siswa</strong> alpha {{ $ambangAlpha }}x atau lebih pada rentang ini — perlu perhatian.
        </x-alert>
    @endif

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa" />
    @else
        {{-- Desktop: tabel biasa. HP: kartu ringkas (bukan tabel) -- 9 kolom
             kalau ditumpuk per-field kepanjangan, jadi diganti badge angka
             sejajar tanpa label per kartu (lihat x-ui.rekap-chip-card). --}}
        <div class="hidden sm:block">
            <x-admin.table :head="['Kelas', 'No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi', 'Terlambat']">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); $alphaTinggi = ($r['alpha'] ?? 0) >= $ambangAlpha; @endphp
                    <tr data-baris-rekap data-cari="{{ strtolower($s->nama.' '.$s->nis) }}" @class(['bg-alpha-soft/30' => $alphaTinggi])>
                        <td class="px-4 py-2.5 text-muted">{{ $s->kelas?->nama ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                        <td class="px-4 py-2.5 text-hadir">{{ $r['hadir'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-sakit">{{ $r['sakit'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-izin">{{ $r['izin'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 font-bold {{ $alphaTinggi ? 'text-alpha' : 'text-alpha/70' }}">{{ $r['alpha'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-dispen">{{ $r['dispensasi'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 font-semibold text-navy">{{ $terlambat[$s->id] ?? 0 }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="sm:hidden">
            <x-ui.rekap-legend terlambat />
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
                        :terlambat="$terlambat[$s->id] ?? 0"
                        :sorot="$alphaTinggi"
                    />
                @endforeach
            </div>
        </div>
        <p id="rekap-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada siswa yang cocok dengan pencarian.
        </p>
        <p class="mt-3 text-xs text-muted-2">Merah muda = alpha {{ $ambangAlpha }}x atau lebih pada rentang tanggal ini.</p>
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
