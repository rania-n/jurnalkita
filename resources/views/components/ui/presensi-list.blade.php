@props([
    'absensis',
])

@php
    // Cuma yang BUKAN Hadir -- jumlah Hadir udah kelihatan dari kotak ringkasan
    // Hadir/Sakit/Izin/Alpha/Dispensasi di atas halaman ini, jadi nge-list nama
    // yang Hadir satu-satu di sini cuma bikin panjang tanpa nambah info baru
    // (kelas isi 36 siswa, biasanya yang nggak hadir cuma segelintir).
    $tidakHadir = $absensis->reject(fn ($a) => $a->status === 'hadir');
@endphp

{{--
    Daftar presensi RINGKAS -- nama + status, dipakai buat tampilan VIEW-ONLY
    (Detail Jurnal guru, verifikasi pengurus kelas, popup Monitor Piket).
    Bukan No./tombol Detail -- itu bagian form pas ISI jurnal. Nama BUKAN
    link lagi (nggak perlu ada aksi apa pun pas nama diketuk, murni info).
    Catatan CUMA muncul kalau beneran keisi (sesuai inputan aslinya di
    jurnal, termasuk yang auto-keisi dari dispensasi) -- nggak ada baris
    kosong kalau kosong.
--}}
@if ($tidakHadir->isEmpty())
    <p class="rounded-xl border border-dashed border-surface-alt bg-card px-4 py-3 text-center text-sm text-muted-2">
        Semua siswa hadir.
    </p>
@else
    <div class="flex flex-col divide-y divide-surface-alt overflow-hidden rounded-xl border border-surface-alt bg-card">
        @foreach ($tidakHadir as $a)
            <div class="flex items-center gap-3 px-4 py-2.5">
                <x-ui.avatar :label="$a->siswa->no_absen ?? '–'" :gender="$a->siswa->jenis_kelamin" class="shrink-0" />
                <div class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-ink">{{ $a->siswa->nama }}</span>
                    @if ($a->catatan)
                        <span class="block truncate text-xs text-muted-2">{{ $a->catatan }}</span>
                    @endif
                </div>
                <x-ui.status-badge :status="$a->status" />
            </div>
        @endforeach
    </div>
@endif
