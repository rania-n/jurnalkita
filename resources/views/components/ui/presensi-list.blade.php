@props([
    'absensis',
    'linkSiswa' => false,
])

{{--
    Daftar presensi RINGKAS -- nama + status, dipakai buat tampilan VIEW-ONLY
    (Detail Jurnal guru, verifikasi pengurus kelas, popup Monitor Piket).
    Bukan No./tombol Detail -- itu bagian form pas ISI jurnal. Catatan CUMA
    muncul kalau beneran keisi (sesuai inputan aslinya di jurnal, termasuk
    yang auto-keisi dari dispensasi) -- nggak ada baris kosong kalau kosong.
--}}
<div class="flex flex-col divide-y divide-surface-alt overflow-hidden rounded-xl border border-surface-alt bg-card">
    @foreach ($absensis as $a)
        <div class="flex items-center gap-3 px-4 py-2.5">
            <x-ui.avatar :label="$a->siswa->no_absen ?? '–'" :gender="$a->siswa->jenis_kelamin" class="shrink-0" />
            <div class="min-w-0 flex-1">
                @if ($linkSiswa)
                    <a href="{{ route('guru.siswa.show', $a->siswa) }}" class="block truncate text-sm font-medium text-ink hover:text-navy hover:underline">{{ $a->siswa->nama }}</a>
                @else
                    <span class="block truncate text-sm font-medium text-ink">{{ $a->siswa->nama }}</span>
                @endif
                @if ($a->catatan)
                    <span class="block truncate text-xs text-muted-2">{{ $a->catatan }}</span>
                @endif
            </div>
            <x-ui.status-badge :status="$a->status" />
        </div>
    @endforeach
</div>
