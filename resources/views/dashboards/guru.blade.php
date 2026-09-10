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
