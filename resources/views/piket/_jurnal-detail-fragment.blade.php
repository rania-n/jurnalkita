@php
    $statusGuru = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];
    $statusAbsen = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $rekap = $jurnal->absensis->countBy('status');
    $jamJurnal = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
@endphp

<div class="flex flex-col gap-3">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm font-bold text-ink">{{ $jurnal->jadwal->mapel->nama }}</span>
        <span class="text-xs text-muted-2">· {{ $jurnal->jadwal->kelas->nama }} · JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}{{ $jamJurnal ? " · {$jamJurnal}" : '' }}</span>
    </div>

    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
        <x-ui.field-static label="Guru" icon="badge">{{ $jurnal->guru->nama ?? '—' }}</x-ui.field-static>
        <x-ui.field-static label="Status Kehadiran" icon="how_to_reg">{{ $statusGuru[$jurnal->status_guru] ?? $jurnal->status_guru }}</x-ui.field-static>

        @if ($jurnal->status_guru === 'hadir')
            <x-ui.field-static label="Materi" icon="menu_book" class="sm:col-span-2">{{ $jurnal->materi ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Metode" icon="school">{{ $jurnal->metode ?: '—' }}</x-ui.field-static>
        @else
            <x-ui.field-static label="Tugas Tambahan" icon="assignment" class="sm:col-span-2">{{ $jurnal->tugas_tambahan ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Alasan" icon="info" class="sm:col-span-2">{{ $jurnal->alasan ?: '—' }}</x-ui.field-static>
        @endif
    </div>

    @if ($jurnal->foto_bukti)
        <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
            <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas" class="max-h-56 w-full rounded-xl border border-surface-alt object-cover">
        </a>
    @endif

    <div>
        <p class="mb-1.5 text-xs font-bold uppercase tracking-wide text-muted-2">Presensi ({{ $jurnal->absensis->count() }} siswa)</p>
        <div class="mb-2 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
            @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
                <x-ui.stat :label="$statusAbsen[$s]" :tone="$s === 'dispensasi' ? 'dispen' : $s" :value="$rekap[$s] ?? 0" />
            @endforeach
        </div>

        @if ($jurnal->absensis->isNotEmpty())
            <div class="max-h-[40vh] overflow-y-auto rounded-xl border border-surface-alt sm:border-0">
                <x-ui.presensi-list :absensis="$jurnal->absensis->sortBy('siswa.no_absen')" />
            </div>
        @endif
    </div>
</div>
