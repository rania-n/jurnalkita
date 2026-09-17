{{--
    Isi popup "Detail" di Riwayat Dispensasi -- sama persis kontennya kayak
    dispensasi/show.blade.php (halaman penuh, tetap ada buat akses langsung/
    fallback), cuma tanpa layout & page-header karena ini nempel di dalam
    modal (judulnya udah dari data-modal-title). Nggak ada logic auto-kirim
    WA di sini -- itu udah kejadian sekali pas baru ngajuin, ditrigger dari
    Riwayat (lihat DispensasiController::index()), bukan pas buka detail.

    Variabel yang wajib ada di scope pemanggil:
      $dispensasi, $bisaWaka, $bisaBatal, $waLinkWaka, $waLinkSiswa
--}}
@php
    [$waIcon, $waColor, $waText] = match ($dispensasi->status_waka) {
        'approved' => ['check_circle', 'text-hadir', 'Disetujui'],
        'rejected' => ['cancel', 'text-alpha', 'Ditolak'],
        default => ['schedule', 'text-sakit', 'Menunggu keputusan'],
    };
@endphp

<div class="flex flex-col gap-4">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <x-ui.field-static label="Jam">{{ $dispensasi->labelJam() }}</x-ui.field-static>
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
    <div class="flex items-start gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
        <x-icon :name="$waIcon" :size="22" class="{{ $waColor }}" />
        <div>
            <p class="text-sm font-bold text-ink">
                Waka Kesiswaan: {{ $waText }}
                @if ($dispensasi->sudahKadaluarsa())
                    <x-ui.status-badge status="kadaluarsa" class="ml-1 align-middle">Kadaluarsa</x-ui.status-badge>
                @endif
            </p>
            @if ($dispensasi->waka)<p class="text-xs text-muted">Oleh {{ $dispensasi->waka->name }}</p>@endif
            @if ($dispensasi->catatan_waka)<p class="mt-0.5 text-xs text-muted">"{{ $dispensasi->catatan_waka }}"</p>@endif
        </div>
    </div>

    {{-- Link WhatsApp — versi hemat biaya, tinggal tekan kirim --}}
    <div class="flex flex-col gap-2">
        @if ($waLinkWaka && auth()->user()->role !== 'waka')
            <a href="{{ $waLinkWaka }}" target="_blank" rel="noopener"
               class="press flex h-11 items-center justify-center gap-2 rounded-xl bg-hadir-soft text-sm font-bold text-hadir">
                <x-icon name="chat" :size="18" /> Kirim Link Persetujuan ke Waka (WA)
            </a>
        @endif
        @if ($waLinkSiswa)
            <a href="{{ $waLinkSiswa }}" target="_blank" rel="noopener"
               class="press flex h-11 items-center justify-center gap-2 rounded-xl bg-izin-soft text-sm font-bold text-izin">
                <x-icon name="chat" :size="18" /> Kirim Surat ke Siswa (WA)
            </a>
        @endif
        @if ($dispensasi->status_akhir === 'approved')
            <button type="button"
                data-modal-open="modal-surat-dispensasi"
                data-modal-title="Surat Dispensasi"
                data-ajax-url="{{ route('dispensasi.surat.fragment', $dispensasi) }}"
                class="press flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-surface-alt bg-card text-sm font-bold text-ink">
                <x-icon name="qr_code_2" :size="18" /> Lihat Surat + QR
            </button>
        @endif
    </div>

    @if ($bisaWaka)
        <form method="POST" action="{{ route('dispensasi.waka', $dispensasi) }}" class="flex flex-col gap-3">
            @csrf
            <x-ui.input label="Catatan (opsional)" name="catatan" :value="old('catatan')" />
            <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                <x-ui.button type="submit" name="keputusan" value="approved" variant="success" icon="check" class="flex-1">Setujui</x-ui.button>
                <x-ui.button type="submit" name="keputusan" value="rejected" variant="danger" icon="close" data-confirm="Yakin tolak dispensasi ini?" class="flex-1">Tolak</x-ui.button>
            </div>
        </form>
    @endif

    {{-- Batalkan pengajuan — hanya pengaju, selama Waka belum memutuskan --}}
    @if ($bisaBatal)
        <form method="POST" action="{{ route('dispensasi.destroy', $dispensasi) }}"
              data-confirm="Batalkan pengajuan dispensasi ini?">
            @csrf @method('DELETE')
            <x-ui.button type="submit" variant="danger" icon="delete">Batalkan Pengajuan</x-ui.button>
        </form>
    @endif
</div>
