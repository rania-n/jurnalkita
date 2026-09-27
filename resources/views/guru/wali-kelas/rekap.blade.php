<x-layouts.app title="Rekap Kelas Wali" width="wide">
    <x-page-header
        title="Rekap Kehadiran Kelas"
        :subtitle="$kelas->nama . ' · Anda wali kelas ini'"
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
        <x-admin.f-date name="dari" label="Dari tanggal" data-pasangan="sampai" />
        <x-admin.f-date name="sampai" label="Sampai tanggal" onchange="this.form.submit()" />
    </x-admin.filters>

    <div class="mb-4">
        <x-ui.search-bar id="cari-rekap-wali" placeholder="Cari nama siswa..." />
    </div>

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa di kelas ini" />
    @else
        <div class="hidden sm:block">
            <x-admin.table :head="['No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi']">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); @endphp
                    <tr data-baris-rekap-wali data-cari="{{ strtolower($s->nama) }}">
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="hadir">{{ $r['hadir'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="sakit">{{ $r['sakit'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="izin">{{ $r['izin'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="alpha">{{ $r['alpha'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="dispensasi">{{ $r['dispensasi'] ?? 0 }}</x-ui.rekap-badge></td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="sm:hidden">
            <x-ui.rekap-legend />
            <div class="flex flex-col gap-2">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); @endphp
                    <x-ui.rekap-chip-card
                        data-baris-rekap-wali
                        data-cari="{{ strtolower($s->nama) }}"
                        :nama="$s->nama"
                        :meta="'No. ' . ($s->no_absen ?? '—')"
                        :hadir="$r['hadir'] ?? 0"
                        :sakit="$r['sakit'] ?? 0"
                        :izin="$r['izin'] ?? 0"
                        :alpha="$r['alpha'] ?? 0"
                        :dispensasi="$r['dispensasi'] ?? 0"
                    />
                @endforeach
            </div>
        </div>
        <p id="rekap-wali-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada siswa yang cocok dengan pencarian.
        </p>
        <p class="mt-3 text-xs text-muted-2">Dihitung dari jurnal yang sudah diisi pada rentang tanggal ini. Belum termasuk jam pelajaran yang jurnalnya belum diisi guru.</p>
    @endif

    @push('scripts')
        <script>
            (function () {
                const cari = document.getElementById('cari-rekap-wali');
                const rows = document.querySelectorAll('[data-baris-rekap-wali]');
                const kosong = document.getElementById('rekap-wali-kosong');
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
