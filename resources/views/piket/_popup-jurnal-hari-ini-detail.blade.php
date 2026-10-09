{{-- Detail publik hari ini; presensi siswa hanya tampil jika Admin mengaktifkan fitur. --}}
@php
    $rentangJam = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
@endphp

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <x-ui.field-static label="Guru" icon="person" class="sm:col-span-2">{{ $jurnal->guru->nama ?? '—' }}</x-ui.field-static>
    <x-ui.field-static label="Kelas">{{ $jurnal->jadwal->kelas->nama ?? '—' }}</x-ui.field-static>
    <x-ui.field-static label="Mata Pelajaran">{{ $jurnal->jadwal->mapel->nama ?? '—' }}</x-ui.field-static>
    <x-ui.field-static label="Jam Pelajaran" icon="schedule" class="sm:col-span-2">
        JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}
        @if ($rentangJam)
            <span class="text-muted-2">· {{ $rentangJam }}</span>
        @endif
    </x-ui.field-static>
    <x-ui.field-static label="Status Kehadiran Guru" class="sm:col-span-2">
        {{ $jurnal->status_guru === 'hadir' ? 'Hadir' : 'Tidak Hadir' }}
        @if ($jurnal->terlambat) <span class="text-alpha ml-1">(Terlambat)</span> @endif
    </x-ui.field-static>
    @if ($jurnal->status_guru === 'hadir')
        <x-ui.field-static label="Materi" class="sm:col-span-2">{{ $jurnal->materi ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Metode" class="sm:col-span-2">{{ $jurnal->metode ?: '—' }}</x-ui.field-static>
    @else
        <x-ui.field-static label="Alasan" class="sm:col-span-2">{{ $jurnal->alasan ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Tugas untuk Siswa" class="sm:col-span-2">{{ $jurnal->tugas_tambahan ?: '—' }}</x-ui.field-static>
    @endif
    @if ($jurnal->foto_bukti)
        <div class="flex flex-col gap-1.5 sm:col-span-2">
            <x-ui.label>Foto Suasana Kelas</x-ui.label>
            <a href="{{ \Illuminate\Support\Facades\Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas" class="max-h-56 w-full rounded-xl border border-surface-alt object-cover">
            </a>
        </div>
    @endif
</div>

@php
    $rekapPresensi = $jurnal->absensis->countBy('status');
    $labelPresensi = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispen'];
@endphp
<section class="mt-5">
    <h4 class="mb-2 text-sm font-bold text-ink">Presensi Siswa ({{ $jurnal->absensis->count() }})</h4>
    <div class="mb-3 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
        @foreach ($labelPresensi as $status => $label)
            <x-ui.stat :label="$label" :tone="$status === 'dispensasi' ? 'dispen' : $status" :value="$rekapPresensi[$status] ?? 0" />
        @endforeach
    </div>
    <x-ui.presensi-list :absensis="$jurnal->absensis->sortBy('siswa.no_absen')" :jurnal="$jurnal" />
</section>
