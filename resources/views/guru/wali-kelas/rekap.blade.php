<x-layouts.app title="Rekap Kelas Wali" width="wide">
    <x-page-header
        title="Rekap Kehadiran Kelas"
        :subtitle="$kelas->nama . ' · ' . now()->translatedFormat('F Y') . ' · Anda wali kelas ini'"
        :back="$adaKelasLain ? route('guru.wali-kelas.index') : null"
    />

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa di kelas ini" />
    @else
        <div class="overflow-x-auto">
            <x-admin.table :head="['No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi']">
                @foreach ($siswas as $s)
                    @php $r = $rekap[$s->id] ?? collect(); @endphp
                    <tr>
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                        <td class="px-4 py-2.5 text-hadir">{{ $r['hadir'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-sakit">{{ $r['sakit'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-izin">{{ $r['izin'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-alpha">{{ $r['alpha'] ?? 0 }}</td>
                        <td class="px-4 py-2.5 text-dispen">{{ $r['dispensasi'] ?? 0 }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>
        <p class="mt-3 text-xs text-muted-2">Dihitung dari jurnal yang sudah diisi bulan ini. Belum termasuk jam pelajaran yang jurnalnya belum diisi guru.</p>
    @endif
</x-layouts.app>
