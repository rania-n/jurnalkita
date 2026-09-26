<x-layouts.app title="Detail Siswa" width="wide">
    <x-page-header
        title="Detail Siswa"
        :subtitle="$siswa->nama . ' · ' . ($siswa->kelas?->nama ?? '—')"
        back="javascript:history.back()"
    />

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <x-ui.field-static label="NIS" icon="badge">{{ $siswa->nis }}</x-ui.field-static>
        <x-ui.field-static label="Kelas" icon="school">{{ $siswa->kelas?->nama ?? '—' }}</x-ui.field-static>
        <x-ui.field-static label="Jenis Kelamin" icon="wc">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</x-ui.field-static>
        <x-ui.field-static label="No. Absen" icon="tag">{{ $siswa->no_absen ?: '—' }}</x-ui.field-static>
    </div>

    <div class="mb-4 flex flex-wrap gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
        <x-ui.stat label="Hadir" tone="hadir" :value="$rekap['hadir'] ?? 0" />
        <x-ui.stat label="Sakit" tone="sakit" :value="$rekap['sakit'] ?? 0" />
        <x-ui.stat label="Izin" tone="izin" :value="$rekap['izin'] ?? 0" />
        <x-ui.stat label="Alpha" tone="alpha" :value="$rekap['alpha'] ?? 0" />
        <x-ui.stat label="Dispensasi" tone="dispen" :value="$rekap['dispensasi'] ?? 0" />
    </div>

    @if ($terlambat > 0)
        <x-alert type="warning" class="mb-4">
            Tercatat <strong>{{ $terlambat }}x</strong> terlambat masuk sekolah.
        </x-alert>
    @endif

    {{-- "Hadir" udah kehitung di ringkasan stat di atas -- di daftar detail
         ini cuma yang SELAIN hadir (sakit/izin/alpha/dispensasi/tugas) yang
         ditampilin, biar nggak kepanjangan baris "Hadir" doang yang nggak
         ada apa-apanya buat dicek. --}}
    @php $riwayat = $absensis->where('status', '!=', 'hadir'); @endphp

    <h2 class="mb-3 text-sm font-bold text-ink">Riwayat Kehadiran</h2>

    @if ($absensis->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada riwayat kehadiran" desc="Siswa ini belum pernah muncul di jurnal manapun." />
    @elseif ($riwayat->isEmpty())
        <x-ui.empty icon="event_available" title="Selalu tercatat Hadir" desc="Belum ada catatan sakit/izin/alpha/dispensasi." />
    @else
        <div class="hidden sm:block">
            <x-admin.table :head="['Tanggal', 'Mapel', 'Status', 'Catatan']">
                @foreach ($riwayat as $a)
                    <tr>
                        <td class="px-4 py-2.5 text-muted">{{ $a->jurnal->tanggal->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-2.5 text-ink">{{ $a->jurnal->jadwal?->mapel?->nama ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.status-badge :status="$a->status" />
                        </td>
                        <td class="px-4 py-2.5 text-muted">{{ $a->catatan ?: '—' }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="flex flex-col gap-2 sm:hidden">
            @foreach ($riwayat as $a)
                <x-ui.list-card
                    :title="$a->jurnal->jadwal?->mapel?->nama ?? '—'"
                    :meta="array_filter([$a->jurnal->tanggal->translatedFormat('d M Y'), $a->catatan])"
                >
                    <x-slot:badge><x-ui.status-badge :status="$a->status" /></x-slot:badge>
                </x-ui.list-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
