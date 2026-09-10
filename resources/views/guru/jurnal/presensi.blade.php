@php
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $rekap = $jurnal->absensis->countBy('status');
@endphp

<x-layouts.app title="Presensi Siswa">
    <x-page-header
        title="Presensi Siswa"
        :subtitle="$jurnal->jadwal->kelas->nama . ' · ' . $jurnal->jadwal->mapel->nama"
        :back="route('jurnal.index')"
    />

    <form method="POST" action="{{ route('jurnal.presensi.save', $jurnal) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
        @csrf

        {{-- Rekap (dihitung ulang tiap simpan) --}}
        <div class="flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
            @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
                <x-ui.stat :label="$statuses[$s]" :tone="$s === 'dispensasi' ? 'dispen' : $s" :value="$rekap[$s] ?? 0" />
            @endforeach
        </div>

        <p class="text-xs text-muted-2">Semua siswa default <strong>Hadir</strong>. Ubah yang berhalangan lalu simpan.</p>

        <div class="flex flex-col gap-3">
            @foreach ($jurnal->absensis->sortBy('siswa.no_absen') as $a)
                <div class="flex flex-col gap-3 rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]">
                    <div class="flex items-center gap-2.5">
                        <x-ui.avatar :label="$a->siswa->no_absen ?? '–'" :gender="$a->siswa->jenis_kelamin" />
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-ink">{{ $a->siswa->nama }}</span>
                            <span class="text-[11px] font-semibold text-muted-2">NIS: {{ $a->siswa->nis }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.select name="presensi[{{ $a->id }}][status]" class="flex-1">
                            @foreach ($statuses as $v => $l)
                                <option value="{{ $v }}" @selected($a->status === $v)>{{ $l }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input name="presensi[{{ $a->id }}][catatan]" placeholder="Catatan (opsional)" :value="$a->catatan" class="flex-1" />
                    </div>
                </div>
            @endforeach
        </div>

        <x-ui.upload
            label="Foto Suasana Kelas (opsional)"
            name="foto_bukti"
            title="Lampirkan Foto Suasana Kelas"
            hint="Bukti pembelajaran sedang berlangsung"
        />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan Jurnal &amp; Absensi</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
