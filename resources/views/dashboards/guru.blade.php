@php
    $guru = auth()->user()->guru;
    $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? null;
    $jadwalHariIni = $hari && $guru
        ? $guru->jadwals()->with('kelas', 'mapel')->where('hari', $hari)->orderBy('jam_ke_mulai')->get()
        : collect();
    $piketHariIni = $guru && $guru->jadwalPikets()->where('hari', $hari)->exists();

    // Jadwal yang jurnalnya sudah diisi hari ini
    $sudahDiisi = $guru
        ? $guru->jurnals()->whereDate('tanggal', today())->pluck('jadwal_id')->all()
        : [];
@endphp

<x-layouts.app title="Beranda Guru" width="wide">
    <x-page-header title="Beranda" :subtitle="'Selamat mengajar, ' . auth()->user()->name" />

    @if ($piketHariIni)
        <x-alert type="info" class="mb-4">Anda bertugas <strong>piket</strong> hari ini.</x-alert>

        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <a href="{{ route('piket.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="event_available" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Piket Hari Ini</p>
                    <p class="text-xs text-muted">Lihat jadwal piket Anda</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>

            <a href="{{ route('dispensasi.create') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="fact_check" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Ajukan Dispensasi</p>
                    <p class="text-xs text-muted">Siswa izin keluar / tidak mengikuti pelajaran</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>
        </div>
    @endif

    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-sm font-bold text-ink">Jadwal Mengajar Hari Ini</h2>
        <a href="{{ route('jurnal.index') }}" class="text-sm font-semibold text-navy">Riwayat Jurnal →</a>
    </div>

    @if ($jadwalHariIni->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal hari ini" />
    @else
        <x-ui.card-list>
            @foreach ($jadwalHariIni as $j)
                <x-ui.list-card
                    :title="$j->mapel->nama"
                    :meta="[$j->kelas->nama . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai, 'Ruang ' . ($j->ruang ?? '-')]"
                >
                    <x-slot:actions>
                        @if (in_array($j->id, $sudahDiisi))
                            <x-ui.action-button label="Sudah diisi" icon="check_circle" variant="success" href="{{ route('jurnal.index') }}" />
                        @else
                            <x-ui.action-button label="Isi Jurnal" icon="edit_note" variant="info" :href="route('jurnal.create', ['jadwal' => $j->id])" />
                        @endif
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    @endif
</x-layouts.app>
