{{--
    Isi popup "Detail" di Riwayat Dispensasi -- ini SATU-SATUNYA cara lihat
    detail dispensasi sekarang, nggak ada lagi halaman penuh terpisah
    (dispensasi.show) buat ini -- sengaja dihapus, dulu bikin bingung karena
    bisa diakses langsung padahal harusnya cuma popup. Nggak ada layout &
    page-header di sini karena ini nempel di dalam modal (judulnya udah dari
    data-modal-title). Nggak ada logic auto-kirim WA di sini -- itu udah
    kejadian sekali pas baru ngajuin, ditrigger dari Riwayat (lihat
    DispensasiController::index()), bukan pas buka detail.

    Variabel yang wajib ada di scope pemanggil:
      $dispensasi, $bisaWaka, $bisaBatal, $bisaUbahNoHp, $waLinkWaka, $waLinkSiswa
--}}
@php
    $anggota = $dispensasi->anggotaKelompok();
    [$waIcon, $waColor, $waText] = match ($dispensasi->status_waka) {
        'approved' => ['check_circle', 'text-hadir', 'Disetujui'],
        'rejected' => ['cancel', 'text-alpha', 'Ditolak'],
        default => ['schedule', 'text-sakit', 'Menunggu keputusan'],
    };
@endphp

<div class="flex flex-col gap-4">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <x-ui.field-static label="Siswa" class="sm:col-span-2">
            <ol class="list-inside list-decimal space-y-1">
                @foreach ($anggota as $item)
                    <li>{{ $item->siswa->nama }} <span class="text-muted-2">({{ $item->siswa->kelas?->nama ?? '—' }})</span></li>
                @endforeach
            </ol>
        </x-ui.field-static>
        <x-ui.field-static label="Jam">{{ $dispensasi->labelJam() }}</x-ui.field-static>
        <x-ui.field-static label="Diajukan oleh (guru piket)">{{ $dispensasi->pengaju->name }}</x-ui.field-static>
        <x-ui.field-static label="Alasan" class="sm:col-span-2">{{ $dispensasi->alasan }}</x-ui.field-static>
        @if ($bisaUbahNoHp)
            <form method="POST" action="{{ route('dispensasi.no-hp.update', $dispensasi) }}" class="flex flex-col gap-2 rounded-xl border border-surface-alt bg-surface p-3 sm:col-span-2">
                @csrf
                <p class="text-sm font-bold text-ink">Kirim bukti melalui WhatsApp</p>
                <p class="text-xs leading-relaxed text-muted-2">Masukkan nomor siswa, orang tua, atau wali yang akan menerima surat izin ini.</p>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                    <x-ui.input
                        label="Nomor WhatsApp penerima"
                        name="no_hp"
                        inputmode="tel"
                        :value="old('no_hp', $dispensasi->no_hp)"
                        placeholder="08xxxxxxxxxx"
                        class="min-w-0 flex-1"
                        required
                    />
                    <x-ui.button type="submit" variant="secondary" icon="save" class="w-full sm:w-auto">Simpan Nomor</x-ui.button>
                </div>
            </form>
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
                        <img src="{{ $suratUrl }}" alt="Surat / bukti izin"
                             class="max-h-72 w-full rounded-xl border border-surface-alt object-cover">
                    </a>
                @endif
            </div>
        @endif
    </div>

    @if ($dispensasi->jenis === 'lomba')
        <div class="flex items-start gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <x-icon name="check_circle" :size="22" class="text-hadir" />
            <div>
                <p class="text-sm font-bold text-ink">
                    Otomatis Disetujui
                    @if ($dispensasi->sudahKadaluarsa())
                        <x-ui.status-badge status="kadaluarsa" class="ml-1 align-middle">Kedaluwarsa</x-ui.status-badge>
                    @endif
                </p>
                <p class="mt-0.5 text-xs text-muted">Pengajuan lomba/dinas tidak memerlukan persetujuan Waka.</p>
            </div>
        </div>
    @else
        {{-- Keputusan Waka --}}
        <div class="flex items-start gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <x-icon :name="$waIcon" :size="22" class="{{ $waColor }}" />
            <div>
                <p class="text-sm font-bold text-ink">
                    Waka: {{ $waText }}
                    @if ($dispensasi->sudahKadaluarsa())
                        <x-ui.status-badge status="kadaluarsa" class="ml-1 align-middle">Kedaluwarsa</x-ui.status-badge>
                    @endif
                </p>
                @if ($dispensasi->waka)<p class="text-xs text-muted">Oleh {{ $dispensasi->waka->name }}</p>@endif
                @if ($dispensasi->catatan_waka)<p class="mt-0.5 text-xs text-muted">"{{ $dispensasi->catatan_waka }}"</p>@endif
            </div>
        </div>
    @endif

    {{-- Link WhatsApp — versi hemat biaya, tinggal tekan kirim --}}
    <div class="flex flex-col gap-2">
        @if ($waLinkWaka && auth()->user()->role !== 'waka')
            <a href="{{ $waLinkWaka }}" target="_blank" rel="noopener"
               class="press flex h-11 items-center justify-center gap-2 rounded-xl bg-hadir-soft text-sm font-bold text-hadir">
                <x-icon name="chat" :size="18" /> Kirim Tautan Persetujuan melalui WhatsApp
            </a>
        @endif
        @if ($waLinkSiswa)
            <a href="{{ $waLinkSiswa }}" target="_blank" rel="noopener"
               class="press flex h-11 items-center justify-center gap-2 rounded-xl bg-izin-soft text-sm font-bold text-izin">
                <x-icon name="chat" :size="18" /> Kirim Bukti melalui WhatsApp
            </a>
        @endif
        @if ($dispensasi->status_akhir === 'approved')
            <button type="button"
                data-modal-open="modal-surat-dispensasi"
                data-modal-title="Surat {{ $dispensasi->jenis === 'lomba' ? 'Lomba' : 'Izin Keluar' }}"
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
            {{-- flex gap-2 langsung (bukan flex-col sm:flex-row) -- sejajar
                 kanan-kiri di semua ukuran layar, samain sama pola tombol
                 berpasangan lain di app. --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" name="keputusan" value="approved" variant="success" icon="check" class="min-w-0 flex-1 px-2">Setujui</x-ui.button>
                <x-ui.button type="submit" name="keputusan" value="rejected" variant="danger" icon="close" data-confirm="Yakin ingin menolak pengajuan dispensasi untuk {{ $anggota->count() }} siswa?" class="min-w-0 flex-1 px-2">Tolak</x-ui.button>
            </div>
        </form>
    @endif

    {{-- Batalkan pengajuan — hanya pengaju, selama Waka belum memutuskan --}}
    @if ($bisaBatal)
        <form method="POST" action="{{ route('dispensasi.destroy', $dispensasi) }}"
              data-confirm="Batalkan pengajuan dispensasi ini?">
            @csrf @method('DELETE')
            <x-ui.button type="submit" variant="danger" icon="delete" block>Batalkan Pengajuan</x-ui.button>
        </form>
    @endif
</div>
