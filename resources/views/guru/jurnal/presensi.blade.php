@php
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];
    $rekap = $jurnal->absensis->countBy('status');
@endphp

<x-layouts.app title="Presensi Siswa">
    <x-page-header
        title="Presensi Siswa"
        :subtitle="$jurnal->jadwal->kelas->nama . ' · ' . $jurnal->jadwal->mapel->nama"
        :back="route('jurnal.show', $jurnal)"
    />

    @if ($jurnal->status_verifikasi === 'revisi')
        <x-alert type="error" class="mb-4">
            Pengurus kelas meminta revisi: <strong>{{ $jurnal->catatan_verifikasi ?: 'tidak ada catatan' }}</strong>.
            Perbaiki lalu simpan — jurnal akan diperiksa ulang.
        </x-alert>
    @endif

    <form method="POST" action="{{ route('jurnal.presensi.save', $jurnal) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
        @csrf

        {{-- Rekap (dihitung ulang tiap simpan) --}}
        <div class="flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
            @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
                <x-ui.stat :label="$statuses[$s]" :tone="$tones[$s]" :value="$rekap[$s] ?? 0" />
            @endforeach
        </div>

        <p class="text-xs text-muted-2">Semua siswa awalnya <strong>Hadir</strong>. Ketuk status siswa yang berhalangan, lalu simpan.</p>

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-3">
            @foreach ($jurnal->absensis->sortBy('siswa.no_absen') as $a)
                @php $terkunciDispen = $a->status === 'dispensasi' && str_starts_with((string) $a->catatan, 'Dispensasi'); @endphp
                <div class="flex flex-col gap-3 rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]">
                    <div class="flex items-center gap-2.5">
                        <x-ui.avatar :label="$a->siswa->no_absen ?? '–'" :gender="$a->siswa->jenis_kelamin" />
                        <div class="flex min-w-0 flex-col">
                            <span class="truncate text-sm font-semibold text-ink">{{ $a->siswa->nama }}</span>
                            <span class="text-[11px] font-semibold text-muted-2">NIS: {{ $a->siswa->nis }}</span>
                        </div>
                    </div>

                    @if ($terkunciDispen)
                        <input type="hidden" name="presensi[{{ $a->id }}][status]" value="dispensasi">
                        <input type="hidden" name="presensi[{{ $a->id }}][catatan]" value="{{ $a->catatan }}">
                        <div class="flex items-center gap-2 rounded-lg bg-dispen-soft px-3 py-2 text-[13px] font-semibold text-dispen">
                            <x-icon name="verified" :size="16" />
                            <span>Dispensasi disetujui — {{ $a->catatan }}</span>
                        </div>
                    @else
                        <x-ui.choice
                            :name="'presensi[' . $a->id . '][status]'"
                            :options="$statuses"
                            :tones="$tones"
                            :value="$a->status"
                            size="sm"
                        />
                        <x-ui.input
                            :name="'presensi[' . $a->id . '][catatan]'"
                            placeholder="Catatan (opsional)"
                            :value="$a->catatan"
                        />
                    @endif
                </div>
            @endforeach
        </div>

        @if ($jurnal->foto_bukti)
            <div class="flex flex-col gap-1.5">
                <x-ui.label>Foto Suasana Kelas (sudah diunggah)</x-ui.label>
                <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                    <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas"
                         class="max-h-56 w-full rounded-xl border border-surface-alt object-cover">
                </a>
            </div>
        @endif

        <x-ui.upload
            :label="$jurnal->foto_bukti ? 'Ganti Foto Suasana Kelas (opsional)' : 'Foto Suasana Kelas (opsional)'"
            name="foto_bukti"
            title="Lampirkan Foto Suasana Kelas"
            hint="Bukti pembelajaran sedang berlangsung"
        />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan Jurnal &amp; Absensi</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
