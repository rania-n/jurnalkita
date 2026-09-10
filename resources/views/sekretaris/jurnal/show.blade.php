@php
    $statusGuru = ['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir'];
    $rekap = $jurnal->absensis->countBy('status');
    $bisaVerifikasi = $jurnal->isPending();
@endphp

<x-layouts.app title="Periksa Jurnal">
    <x-page-header
        :title="$jurnal->jadwal->mapel->nama"
        :subtitle="'Oleh ' . $jurnal->guru->nama . ' · ' . $jurnal->tanggal->translatedFormat('d M Y')"
        :back="route('sekretaris.jurnal.index')"
    />

    @if (! $bisaVerifikasi)
        <x-alert :type="$jurnal->status_verifikasi === 'terverifikasi' ? 'success' : 'error'" class="mb-4">
            {{ $jurnal->status_verifikasi === 'terverifikasi' ? 'Sudah kamu verifikasi.' : 'Sudah kamu minta revisi: ' . $jurnal->catatan_verifikasi }}
        </x-alert>
    @endif

    <div class="flex flex-col gap-3">
        <x-ui.field-static label="Jam Pelajaran">JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}</x-ui.field-static>
        <x-ui.field-static label="Status Kehadiran Guru">{{ $statusGuru[$jurnal->status_guru] ?? $jurnal->status_guru }}</x-ui.field-static>
        <x-ui.field-static label="Materi">{{ $jurnal->materi }}</x-ui.field-static>
        <x-ui.field-static label="Metode">{{ $jurnal->metode ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Tugas Tambahan">{{ $jurnal->tugas_tambahan ?: '—' }}</x-ui.field-static>
        @if ($jurnal->foto_bukti)
            <div class="flex flex-col gap-1.5">
                <x-ui.label>Foto Suasana Kelas</x-ui.label>
                <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                    <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas"
                         class="max-h-72 w-full rounded-xl border border-surface-alt object-cover">
                </a>
            </div>
        @endif
    </div>

    {{-- Rekap presensi --}}
    <div class="mt-5 flex gap-1.5 overflow-x-auto rounded-xl border border-surface-alt bg-card p-2">
        @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
            <x-ui.stat :label="ucfirst($s === 'dispensasi' ? 'Dispen' : $s)" :tone="$s === 'dispensasi' ? 'dispen' : $s" :value="$rekap[$s] ?? 0" />
        @endforeach
    </div>

    @if ($bisaVerifikasi)
        <form method="POST" action="{{ route('sekretaris.jurnal.verifikasi', $jurnal) }}" class="mt-5 flex flex-col gap-3">
            @csrf
            <x-ui.textarea label="Catatan (wajib jika minta revisi)" name="catatan" :rows="2" placeholder="Contoh: materi tidak sesuai dengan yang diajarkan.">{{ old('catatan') }}</x-ui.textarea>
            <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                <button type="submit" name="keputusan" value="terima"
                    class="press flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-hadir/25 bg-hadir-soft text-sm font-bold text-hadir">
                    <x-icon name="check" :size="18" /> Sesuai — Verifikasi
                </button>
                <button type="submit" name="keputusan" value="revisi"
                    class="press flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-alpha/25 bg-alpha-soft text-sm font-bold text-alpha">
                    <x-icon name="edit" :size="18" /> Minta Revisi
                </button>
            </div>
        </form>
    @endif

    {{-- Daftar presensi --}}
    <div class="mt-6">
        <h2 class="mb-2 text-sm font-bold text-ink">Presensi ({{ $jurnal->absensis->count() }} siswa)</h2>
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
