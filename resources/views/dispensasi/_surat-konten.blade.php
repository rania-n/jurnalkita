{{--
    Isi surat dispensasi -- dipakai bareng 2 tempat: surat.blade.php (link WA
    ke SISWA, tanpa login, wajib halaman biasa -- nggak bisa popup karena
    siswa belum punya sesi aplikasi) & _surat-fragment.blade.php (popup buat
    guru piket/waka/admin yang lihat dari DALAM app, sudah login).

    Variabel yang wajib ada di scope pemanggil: $dispensasi, $qrUrl
--}}
@php
    $labelAkhir = ['pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$dispensasi->status_akhir];
    $sudahLewat = $dispensasi->status_akhir === 'approved' && ! $dispensasi->berlakuPada();
@endphp

<div class="mb-4 text-center">
    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-navy text-card">
        <x-icon name="fact_check" :size="24" fill />
    </span>
    <h1 class="mt-2 text-lg font-bold text-ink">Surat Dispensasi</h1>
</div>

<x-alert :type="$dispensasi->status_akhir === 'approved' ? 'success' : ($dispensasi->status_akhir === 'rejected' ? 'error' : 'warning')" class="mb-4">
    Status: <strong>{{ $labelAkhir }}</strong>
</x-alert>

{{-- min-w-0 + shrink di label, break-words di nilai -- nama siswa/alasan
     yang panjang dulu bisa overflow keluar card di layar sempit karena
     flex item default nggak nyempit/wrap. --}}
<div class="flex flex-col gap-3 text-sm">
    <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
        <span class="shrink-0 text-muted">Nama Siswa</span>
        <span class="min-w-0 break-words text-right font-semibold text-ink">{{ $dispensasi->siswa->nama }}</span>
    </div>
    <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
        <span class="shrink-0 text-muted">Kelas</span>
        <span class="min-w-0 break-words text-right font-semibold text-ink">{{ $dispensasi->siswa->kelas?->nama ?? '—' }}</span>
    </div>
    <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
        <span class="shrink-0 text-muted">Tanggal</span>
        <span class="min-w-0 break-words text-right font-semibold text-ink">{{ $dispensasi->labelTanggal() }}</span>
    </div>
    <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
        <span class="shrink-0 text-muted">Jam</span>
        <span class="min-w-0 break-words text-right font-semibold text-ink">{{ $dispensasi->labelJam() }}</span>
    </div>
    @if ($dispensasi->status_akhir === 'approved')
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="shrink-0 text-muted">Disetujui oleh Waka Kesiswaan</span>
            <span class="min-w-0 break-words text-right font-semibold text-ink">{{ $dispensasi->waka?->name ?? 'Nama Waka belum tercatat' }}</span>
        </div>
    @endif
    <div class="border-b border-surface-alt pb-2">
        <span class="text-muted">Alasan</span>
        <p class="mt-1 break-words font-semibold text-ink">{{ $dispensasi->alasan }}</p>
    </div>
</div>

<div class="mt-4 rounded-xl border border-surface-alt bg-card p-3">
    <p class="mb-2 text-sm font-bold text-ink">Surat / Bukti Pengajuan</p>
    @if ($dispensasi->surat_path)
        @php
            $buktiUrl = Storage::url($dispensasi->surat_path);
            $buktiPdf = str_ends_with(strtolower($dispensasi->surat_path), '.pdf');
        @endphp
        @if ($buktiPdf)
            <a href="{{ $buktiUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg bg-surface px-3 py-2 text-sm font-semibold text-navy">
                <x-icon name="picture_as_pdf" :size="20" /> Buka surat (PDF)
            </a>
        @else
            <a href="{{ $buktiUrl }}" target="_blank" rel="noopener">
                <img src="{{ $buktiUrl }}" alt="Surat / bukti pengajuan dispensasi" class="max-h-64 w-full rounded-lg border border-surface-alt object-contain">
            </a>
        @endif
    @else
        <p class="text-sm text-muted-2">Tidak ada surat atau bukti yang dilampirkan.</p>
    @endif
</div>

@if ($qrUrl)
    <div class="mt-5 flex flex-col items-center gap-2 rounded-xl border border-surface-alt bg-surface p-4">
        {{-- h-auto + max-w-full (bukan width/height attribute mentah) biar
             nggak overflow di layar yang lebih sempit dari 200px. --}}
        <img src="{{ $qrUrl }}" alt="QR dispensasi" class="h-auto w-[200px] max-w-full rounded-lg">
        <p class="text-center text-xs text-muted-2">
            Tunjukkan QR ini ke satpam saat keluar sekolah.<br>
            Kode berganti tiap 10 detik — kalau tidak terbaca, tunggu sebentar lalu coba lagi.
        </p>
    </div>
@elseif ($dispensasi->status_akhir === 'pending')
    <x-alert type="info" class="mt-5">Belum bisa dipakai keluar — masih menunggu persetujuan Waka Kesiswaan.</x-alert>
@elseif ($dispensasi->status_akhir === 'rejected')
    <x-alert type="error" class="mt-5">Pengajuan ini ditolak, tidak berlaku buat keluar sekolah.</x-alert>
@elseif ($sudahLewat)
    <x-alert type="warning" class="mt-5">Masa berlaku dispensasi ini sudah lewat, QR tidak ditampilkan lagi.</x-alert>
@endif
