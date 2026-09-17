@props([
    'absensis',
    'linkSiswa' => false,
])

{{--
    Daftar presensi RINGKAS -- cuma nama + status, dipakai buat tampilan
    VIEW-ONLY (Detail Jurnal guru, verifikasi pengurus kelas, popup Monitor
    Piket). Bukan No./Catatan/tombol Detail -- itu bagian form pas ISI
    jurnal, di sini cukup lihat sekilas siapa hadir/nggak. Satu markup buat
    semua ukuran layar (nggak perlu tabel-vs-kartu lagi, sesimpel ini).
--}}
<div class="flex flex-col divide-y divide-surface-alt overflow-hidden rounded-xl border border-surface-alt bg-card">
    @foreach ($absensis as $a)
        <div class="flex items-center justify-between gap-3 px-4 py-2.5">
            @if ($linkSiswa)
                <a href="{{ route('guru.siswa.show', $a->siswa) }}" class="min-w-0 truncate text-sm font-medium text-ink hover:text-navy hover:underline">{{ $a->siswa->nama }}</a>
            @else
                <span class="min-w-0 truncate text-sm font-medium text-ink">{{ $a->siswa->nama }}</span>
            @endif
            <x-ui.status-badge :status="$a->status" />
        </div>
    @endforeach
</div>
