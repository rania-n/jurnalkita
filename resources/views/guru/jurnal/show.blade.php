@php
    $statusGuru = ['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir'];
    $statusAbsen = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $rekap = $jurnal->absensis->countBy('status');
    $bisaUbah = $jurnal->bisaDiubah();
    $vs = $jurnal->status_verifikasi;
    $jamJurnal = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
@endphp

<x-layouts.app title="Detail Jurnal">
    <x-page-header
        :title="$jurnal->jadwal->mapel->nama"
        :subtitle="$jurnal->jadwal->kelas->nama . ' · ' . $jurnal->tanggal->translatedFormat('d M Y')"
        :back="route('jurnal.index')"
    />

    {{-- Status verifikasi --}}
    <x-alert :type="$vs === 'terverifikasi' ? 'success' : ($vs === 'revisi' ? 'error' : 'info')" class="mb-4">
        @if ($vs === 'terverifikasi')
            Jurnal sudah <strong>diverifikasi</strong> oleh pengurus kelas
            @if ($jurnal->verifikator) ({{ $jurnal->verifikator->nama }}) @endif. Tidak bisa diubah lagi.
        @elseif ($vs === 'revisi')
            Pengurus kelas meminta <strong>perbaikan</strong>: {{ $jurnal->catatan_verifikasi ?: 'tidak ada catatan.' }}
            Perbaiki lalu simpan — jurnal akan diperiksa ulang.
        @else
            Menunggu verifikasi pengurus kelas. Selama menunggu, jurnal masih bisa diubah.
        @endif
    </x-alert>

    {{-- Info jurnal -- tampilan aja, ubah lewat tombol "Ubah Jurnal" di bawah (popup) --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <x-ui.field-static label="Jam Pelajaran" icon="schedule" class="sm:col-span-2">
            JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}
            @if ($jamJurnal)
                <span class="text-muted-2">· {{ $jamJurnal }}</span>
            @endif
        </x-ui.field-static>
        <x-ui.field-static label="Status Kehadiran Anda" class="sm:col-span-2">{{ $statusGuru[$jurnal->status_guru] ?? $jurnal->status_guru }}</x-ui.field-static>
        <x-ui.field-static label="Materi" class="sm:col-span-2">{{ $jurnal->materi ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Metode">{{ $jurnal->metode ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Tugas Tambahan">{{ $jurnal->tugas_tambahan ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Alasan" class="sm:col-span-2">{{ $jurnal->alasan ?: '—' }}</x-ui.field-static>
    </div>

    @if ($jurnal->foto_bukti)
        <div class="mb-4 flex flex-col gap-1.5">
            <x-ui.label>Foto Suasana Kelas</x-ui.label>
            <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas"
                     class="max-h-72 w-full rounded-xl border border-surface-alt object-cover">
            </a>
        </div>
    @endif

    {{-- Presensi -- diubah bareng jurnal lewat tombol "Ubah Jurnal" di bawah,
         satu halaman gabungan (bukan tombol terpisah lagi). --}}
    <h2 class="mb-2 text-sm font-bold text-ink">Presensi ({{ $jurnal->absensis->count() }} siswa)</h2>

    <div class="mb-3 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
        @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
            <x-ui.stat :label="$statusAbsen[$s]" :tone="$s === 'dispensasi' ? 'dispen' : $s" :value="$rekap[$s] ?? 0" />
        @endforeach
    </div>

    <x-ui.presensi-list :absensis="$jurnal->absensis->sortBy('siswa.no_absen')" link-siswa />

    {{-- Aksi -- taruh paling bawah, setelah presensi, biar urutannya: lihat dulu semuanya, baru ubah/hapus kalau perlu --}}
    @if ($bisaUbah)
        <div class="mt-6 flex gap-2">
            <x-ui.button :href="route('jurnal.edit', $jurnal)" icon="edit" class="flex-1 sm:flex-none">Ubah Jurnal</x-ui.button>
            <form method="POST" action="{{ route('jurnal.destroy', $jurnal) }}" class="flex-1 sm:flex-none"
                  data-confirm="Hapus jurnal ini beserta presensinya? Tindakan ini tidak bisa dibatalkan lewat aplikasi.">
                @csrf @method('DELETE')
                <x-ui.button type="submit" variant="danger" icon="delete" class="w-full">Hapus Jurnal</x-ui.button>
            </form>
        </div>
    @endif
</x-layouts.app>
