@php
    $statusGuru = ['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir'];
    $statusAbsen = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $rekap = $jurnal->absensis->countBy('status');
    $bisaUbah = $jurnal->isPending();
@endphp

<x-layouts.app title="Detail Jurnal">
    <x-page-header
        :title="$jurnal->jadwal->mapel->nama"
        :subtitle="$jurnal->jadwal->kelas->nama . ' · ' . $jurnal->tanggal->translatedFormat('d M Y')"
        :back="route('jurnal.index')"
    />

    {{-- Status verifikasi --}}
    <div class="mb-4">
        @php $vs = $jurnal->status_verifikasi; @endphp
        <x-alert :type="$vs === 'terverifikasi' ? 'success' : ($vs === 'revisi' ? 'error' : 'info')">
            @if ($vs === 'terverifikasi')
                Jurnal sudah <strong>diverifikasi</strong> oleh pengurus kelas
                @if ($jurnal->verifikator) ({{ $jurnal->verifikator->nama }}) @endif.
            @elseif ($vs === 'revisi')
                Pengurus kelas meminta <strong>perbaikan</strong>: {{ $jurnal->catatan_verifikasi ?: 'tidak ada catatan.' }}
            @else
                Menunggu verifikasi pengurus kelas. Selama menunggu, jurnal masih bisa diubah.
            @endif
        </x-alert>
    </div>

    {{-- Ringkasan / form ubah --}}
    <form method="POST" action="{{ route('jurnal.update', $jurnal) }}" class="flex flex-col gap-4">
        @csrf

        <div class="grid grid-cols-2 gap-3">
            <x-ui.field-static label="Jam ke- (mulai)">{{ $jurnal->jam_ke_mulai }}</x-ui.field-static>
            @if ($bisaUbah)
                <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai">
                    @for ($i = 1; $i <= 13; $i++)
                        <option value="{{ $i }}" @selected($jurnal->jam_ke_selesai == $i)>Jam ke-{{ $i }}</option>
                    @endfor
                </x-ui.select>
            @else
                <x-ui.field-static label="Jam ke- (selesai)">{{ $jurnal->jam_ke_selesai }}</x-ui.field-static>
            @endif
        </div>

        @if ($bisaUbah)
            <x-ui.select label="Status Kehadiran Anda" name="status_guru">
                @foreach ($statusGuru as $v => $l)
                    <option value="{{ $v }}" @selected($jurnal->status_guru === $v)>{{ $l }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.textarea label="Materi" name="materi" :rows="3">{{ $jurnal->materi }}</x-ui.textarea>
            <x-ui.textarea label="Metode Pembelajaran" name="metode" :rows="2">{{ $jurnal->metode }}</x-ui.textarea>
            <x-ui.textarea label="Tugas Tambahan" name="tugas_tambahan" :rows="2">{{ $jurnal->tugas_tambahan }}</x-ui.textarea>
            <x-ui.button type="submit" icon="save" class="self-start">Simpan Perubahan</x-ui.button>
        @else
            <x-ui.field-static label="Status Kehadiran Anda">{{ $statusGuru[$jurnal->status_guru] ?? $jurnal->status_guru }}</x-ui.field-static>
            <x-ui.field-static label="Materi">{{ $jurnal->materi }}</x-ui.field-static>
            <x-ui.field-static label="Metode">{{ $jurnal->metode ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Tugas Tambahan">{{ $jurnal->tugas_tambahan ?: '—' }}</x-ui.field-static>
        @endif
    </form>

    {{-- Presensi --}}
    <div class="mt-6">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm font-bold text-ink">Presensi ({{ $jurnal->absensis->count() }} siswa)</h2>
            @if ($bisaUbah)
                <x-ui.button :href="route('jurnal.presensi', $jurnal)" variant="secondary" icon="edit" class="!h-9 !px-3 !text-sm">Ubah Presensi</x-ui.button>
            @endif
        </div>

        <div class="mb-3 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
            @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
                <x-ui.stat :label="$statusAbsen[$s]" :tone="$s === 'dispensasi' ? 'dispen' : $s" :value="$rekap[$s] ?? 0" />
            @endforeach
        </div>

        <x-admin.table :head="['No', 'Nama', 'Status', 'Catatan']">
            @foreach ($jurnal->absensis->sortBy('siswa.no_absen') as $a)
                <tr>
                    <td class="px-4 py-2.5 text-muted">{{ $a->siswa->no_absen ?? '–' }}</td>
                    <td class="px-4 py-2.5 font-semibold text-ink">{{ $a->siswa->nama }}</td>
                    <td class="px-4 py-2.5"><x-ui.status-badge :status="$a->status" /></td>
                    <td class="px-4 py-2.5 text-muted">{{ $a->catatan ?: '—' }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    </div>
</x-layouts.app>
