@php
    $labelAkhir = ['pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$dispensasi->status_akhir];
@endphp

<x-layouts.guest title="Surat Dispensasi" :center="false">
    @if ($qrUrl)
        <meta http-equiv="refresh" content="{{ $detikSisa }}">
    @endif

    <div class="mb-4 text-center">
        <span class="flex h-12 w-12 items-center justify-center mx-auto rounded-xl bg-navy text-card">
            <x-icon name="fact_check" :size="24" fill />
        </span>
        <h1 class="mt-2 text-lg font-bold text-ink">Surat Dispensasi</h1>
    </div>

    <x-alert :type="$dispensasi->status_akhir === 'approved' ? 'success' : ($dispensasi->status_akhir === 'rejected' ? 'error' : 'warning')" class="mb-4">
        Status: <strong>{{ $labelAkhir }}</strong>
    </x-alert>

    <div class="flex flex-col gap-3 text-sm">
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="text-muted">Nama Siswa</span>
            <span class="font-semibold text-ink text-right">{{ $dispensasi->siswa->nama }}</span>
        </div>
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="text-muted">Kelas</span>
            <span class="font-semibold text-ink">{{ $dispensasi->siswa->kelas?->nama ?? '—' }}</span>
        </div>
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="text-muted">Tanggal</span>
            <span class="font-semibold text-ink">{{ $dispensasi->tanggal->translatedFormat('d M Y') }}</span>
        </div>
        <div class="flex justify-between gap-3 border-b border-surface-alt pb-2">
            <span class="text-muted">Jam</span>
            <span class="font-semibold text-ink">
                {{ $dispensasi->jam_ke_mulai ? "JP {$dispensasi->jam_ke_mulai}–{$dispensasi->jam_ke_selesai}" : 'Sehari penuh' }}
            </span>
        </div>
        <div class="border-b border-surface-alt pb-2">
            <span class="text-muted">Alasan</span>
            <p class="mt-1 font-semibold text-ink">{{ $dispensasi->alasan }}</p>
        </div>
    </div>

    @if ($qrUrl)
        <div class="mt-5 flex flex-col items-center gap-2 rounded-xl border border-surface-alt bg-surface p-4">
            <img src="{{ $qrUrl }}" alt="QR dispensasi" width="200" height="200" class="rounded-lg">
            <p class="text-center text-xs text-muted-2">
                Tunjukkan QR ini ke satpam saat keluar sekolah.<br>
                Kode berganti tiap 10 detik — kalau tidak terbaca, tunggu sebentar lalu coba lagi.
            </p>
        </div>
    @elseif ($dispensasi->status_akhir === 'pending')
        <x-alert type="info" class="mt-5">Belum bisa dipakai keluar — masih menunggu persetujuan Waka Kesiswaan.</x-alert>
    @elseif ($dispensasi->status_akhir === 'rejected')
        <x-alert type="error" class="mt-5">Pengajuan ini ditolak, tidak berlaku buat keluar sekolah.</x-alert>
    @endif
</x-layouts.guest>
