@php
    // Label per status absensi di daftar riwayat. 'dispensasi' adalah nama
    // status lama (sebelum izin keluar jadi status sendiri) -- labelnya
    // disamakan 'Izin Keluar' soalnya maknanya sama persis; tanpa baris ini,
    // riwayat lama bakal nunjukin "ucfirst('dispensasi')" = "Dispensasi".
    $labels = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'izin_keluar' => 'Izin Keluar', 'dispensasi' => 'Izin Keluar'];
@endphp

@if ($absensis->isEmpty())
    <x-ui.empty icon="event_busy" title="Belum ada data kehadiran" />
@else
    <div class="flex flex-col gap-2">
        @foreach ($absensis as $absensi)
            <div class="rounded-xl border border-surface-alt bg-card p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-ink">{{ $absensi->jurnal->jadwal->mapel->nama ?? 'Mata pelajaran tidak tersedia' }}</p>
                        <p class="text-xs text-muted-2">
                            {{ $absensi->jurnal->tanggal->translatedFormat('l, d M Y') }} · Jam ke-{{ $absensi->jurnal->jam_ke_mulai }}{{ $absensi->jurnal->jam_ke_selesai !== $absensi->jurnal->jam_ke_mulai ? '–'.$absensi->jurnal->jam_ke_selesai : '' }}
                        </p>
                    </div>
                    <x-ui.status-badge :status="$absensi->status">{{ $labels[$absensi->status] ?? ucfirst($absensi->status) }}</x-ui.status-badge>
                </div>
                @if ($absensi->status !== 'hadir')
                    <p class="mt-2 text-xs text-muted">Catatan: {{ $absensi->catatan ?: 'Tidak ada catatan.' }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif
