@php
    $statusGuru = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];
    $rekap = $jurnal->absensis->countBy('status');
    $bisaVerifikasi = $jurnal->isPending();
    $jamJurnal = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
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

    {{-- order-2/order-1 -- di HP (1 kolom), presensi (ringkasan Sakit/Izin/
         Dispensasi dulu) ditaruh DULUAN, baru field jurnal & tombol
         verifikasi -- biar nggak ketutup scroll panjang field-field dulu.
         Di desktop (xl+) balik ke urutan normal kiri-kanan. --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,28rem)_1fr] xl:gap-10">
        {{-- Kolom kiri: isi jurnal + aksi verifikasi --}}
        <div class="order-2 flex flex-col gap-3 xl:order-1">
            <x-ui.field-static label="Jam Pelajaran">
                JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}
                @if ($jamJurnal)
                    <span class="text-muted-2">· {{ $jamJurnal }}</span>
                @endif
            </x-ui.field-static>
            <x-ui.field-static label="Status Kehadiran Guru">{{ $statusGuru[$jurnal->status_guru] ?? $jurnal->status_guru }}</x-ui.field-static>
            <x-ui.field-static label="Materi">{{ $jurnal->materi ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Metode">{{ $jurnal->metode ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Tugas Tambahan">{{ $jurnal->tugas_tambahan ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Alasan">{{ $jurnal->alasan ?: '—' }}</x-ui.field-static>
            @if ($jurnal->foto_bukti)
                <div class="flex flex-col gap-1.5">
                    <x-ui.label>Foto Suasana Kelas</x-ui.label>
                    <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                        <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas"
                             class="max-h-72 w-full rounded-xl border border-surface-alt object-cover">
                    </a>
                </div>
            @endif

            @if ($bisaVerifikasi)
                <form method="POST" action="{{ route('sekretaris.jurnal.verifikasi', $jurnal) }}" class="mt-2 flex flex-col gap-3">
                    @csrf
                    <x-ui.textarea label="Catatan (wajib jika minta revisi)" name="catatan" :rows="2" placeholder="Contoh: materi tidak sesuai dengan yang diajarkan.">{{ old('catatan') }}</x-ui.textarea>
                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                        <x-ui.button type="submit" name="keputusan" value="terima" variant="success" icon="check" class="flex-1">Sesuai — Verifikasi</x-ui.button>
                        <x-ui.button type="submit" name="keputusan" value="revisi" variant="danger" icon="edit" class="flex-1">Minta Revisi</x-ui.button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Kolom kanan: presensi --}}
        <div class="order-1 xl:order-2">
            <div class="mb-3 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
                @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
                    <x-ui.stat :label="ucfirst($s === 'dispensasi' ? 'Dispen' : $s)" :tone="$s === 'dispensasi' ? 'dispen' : $s" :value="$rekap[$s] ?? 0" />
                @endforeach
            </div>

            <h2 class="mb-2 text-sm font-bold text-ink">Presensi ({{ $jurnal->absensis->count() }} siswa)</h2>
            <x-ui.presensi-list :absensis="$jurnal->absensis->sortBy('siswa.no_absen')" />
        </div>
    </div>
</x-layouts.app>
