@php
    [$waIcon, $waColor, $waText] = match ($dispensasi->status_waka) {
        'approved' => ['check_circle', 'text-hadir', 'Disetujui'],
        'rejected' => ['cancel', 'text-alpha', 'Ditolak'],
        default => ['schedule', 'text-sakit', 'Menunggu keputusan'],
    };
@endphp

<x-layouts.app title="Detail Dispensasi">
    <x-page-header
        :title="$dispensasi->siswa->nama"
        :subtitle="$dispensasi->siswa->kelas?->nama . ' · ' . $dispensasi->tanggal->translatedFormat('d M Y')"
        :back="route('dispensasi.index')"
    />

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <x-ui.field-static label="Jam">
            {{ $dispensasi->jam_ke_mulai ? "JP {$dispensasi->jam_ke_mulai}–{$dispensasi->jam_ke_selesai}" : 'Sehari penuh' }}
        </x-ui.field-static>
        <x-ui.field-static label="Diajukan oleh (guru piket)">{{ $dispensasi->pengaju->name }}</x-ui.field-static>
        <x-ui.field-static label="Alasan" class="sm:col-span-2">{{ $dispensasi->alasan }}</x-ui.field-static>
        @if ($dispensasi->no_hp)
            <x-ui.field-static label="No. HP" icon="call">{{ $dispensasi->no_hp }}</x-ui.field-static>
        @endif
        @if ($dispensasi->surat_path)
            @php $suratUrl = Storage::url($dispensasi->surat_path); $isPdf = str_ends_with(strtolower($dispensasi->surat_path), '.pdf'); @endphp
            <div class="flex flex-col gap-1.5 sm:col-span-2">
                <x-ui.label>Surat / Bukti</x-ui.label>
                @if ($isPdf)
                    <a href="{{ $suratUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-xl border border-surface-alt bg-card px-4 py-3 text-sm font-semibold text-navy">
                        <x-icon name="picture_as_pdf" :size="20" /> Buka surat (PDF)
                    </a>
                @else
                    <a href="{{ $suratUrl }}" target="_blank" rel="noopener">
                        <img src="{{ $suratUrl }}" alt="Surat / bukti dispensasi"
                             class="max-h-72 w-full rounded-xl border border-surface-alt object-cover">
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Keputusan Waka Kesiswaan --}}
    <div class="mt-5 flex max-w-xl items-start gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
        <x-icon :name="$waIcon" :size="22" class="{{ $waColor }}" />
        <div>
            <p class="text-sm font-bold text-ink">Waka Kesiswaan: {{ $waText }}</p>
            @if ($dispensasi->waka)<p class="text-xs text-muted">Oleh {{ $dispensasi->waka->name }}</p>@endif
            @if ($dispensasi->catatan_waka)<p class="mt-0.5 text-xs text-muted">"{{ $dispensasi->catatan_waka }}"</p>@endif
        </div>
    </div>

    @if ($bisaWaka)
        <form method="POST" action="{{ route('dispensasi.waka', $dispensasi) }}" class="mt-5 flex max-w-xl flex-col gap-3">
            @csrf
            <x-ui.input label="Catatan (opsional)" name="catatan" :value="old('catatan')" />
            <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                <button type="submit" name="keputusan" value="approved"
                    class="press flex h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-hadir/25 bg-hadir-soft text-base font-bold text-hadir">
                    <x-icon name="check" :size="20" /> Setujui
                </button>
                <button type="submit" name="keputusan" value="rejected" data-confirm="Yakin tolak dispensasi ini?"
                    class="press flex h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-alpha/25 bg-alpha-soft text-base font-bold text-alpha">
                    <x-icon name="close" :size="20" /> Tolak
                </button>
            </div>
        </form>
    @endif

    {{-- Batalkan pengajuan — hanya pengaju, selama Waka belum memutuskan --}}
    @if ($bisaBatal)
        <form method="POST" action="{{ route('dispensasi.destroy', $dispensasi) }}" class="mt-5"
              data-confirm="Batalkan pengajuan dispensasi ini?">
            @csrf @method('DELETE')
            <x-ui.button type="submit" variant="danger" icon="delete">Batalkan Pengajuan</x-ui.button>
        </form>
    @endif
</x-layouts.app>
