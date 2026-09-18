<x-layouts.app title="Rekap Kelas Wali" width="wide">
    <x-page-header
        title="Rekap Kehadiran Kelas"
        :subtitle="$kelas->nama . ' · ' . now()->translatedFormat('F Y') . ' · Anda wali kelas ini'"
        :back="$adaKelasLain ? route('guru.wali-kelas.index') : null"
    />

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa di kelas ini" />
    @else
        <div class="hidden sm:block">
            <x-admin.table :head="['No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi', 'Terlambat']">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); @endphp
                    <tr>
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="hadir">{{ $r['hadir'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="sakit">{{ $r['sakit'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="izin">{{ $r['izin'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="alpha">{{ $r['alpha'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="dispensasi">{{ $r['dispensasi'] ?? 0 }}</x-ui.rekap-badge></td>
                        <td class="px-4 py-2.5"><x-ui.rekap-badge tone="terlambat">{{ $terlambat[$s->id] ?? 0 }}</x-ui.rekap-badge></td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="sm:hidden">
            <x-ui.rekap-legend terlambat />
            <div class="flex flex-col gap-2">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); @endphp
                    <x-ui.rekap-chip-card
                        :nama="$s->nama"
                        :meta="'No. ' . ($s->no_absen ?? '—')"
                        :hadir="$r['hadir'] ?? 0"
                        :sakit="$r['sakit'] ?? 0"
                        :izin="$r['izin'] ?? 0"
                        :alpha="$r['alpha'] ?? 0"
                        :dispensasi="$r['dispensasi'] ?? 0"
                        :terlambat="$terlambat[$s->id] ?? 0"
                    />
                @endforeach
            </div>
        </div>
        <p class="mt-3 text-xs text-muted-2">Dihitung dari jurnal yang sudah diisi bulan ini. Belum termasuk jam pelajaran yang jurnalnya belum diisi guru.</p>
    @endif
</x-layouts.app>
