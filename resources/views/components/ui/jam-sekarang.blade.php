@props(['jpSekarang' => null])

@php
    // translatedFormat('l, F') nggak beneran nerjemahin -- APP_LOCALE app ini
    // 'en', jadi "Tuesday, January" bukan "Selasa, Januari" kalau dipaksa lewat
    // situ. Sama kayak pola yang sudah dipakai di tempat lain (mis. config
    // akademik.hari), nama hari/bulan Indonesia ditulis manual di sini.
    $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggalIndo = $hariIndo[now()->dayOfWeek].', '.now()->day.' '.$bulanIndo[now()->month].' '.now()->year;

    // Nggak ada JP aktif bukan berarti udah di luar jam sekolah -- bisa juga
    // lagi istirahat (di antara 2 JP). Bedain labelnya biar guru nggak salah
    // kira udah pulang padahal cuma lagi jeda.
    $dalamJamSekolah = \App\Support\Waktu::dalamJamSekolah();
@endphp

{{--
    Widget waktu sekarang -- ditaruh di atas tiap dasbor biar nggak cuma
    kotak-kotak statistik doang, guru/waka/dll langsung lihat ini lagi jam
    berapa & lagi JP berapa tanpa harus lirik jam device sendiri. Jamnya
    (jam:menit:detik) jalan tiap detik lewat JS (initJamSekarang() di
    app.js); JP-nya dihitung sekali pas render (cukup, halaman biasa dibuka
    ulang tiap beberapa menit).
--}}
{{--
    Yang paling PENTING di sini bukan jam/tanggal (itu bisa dilihat sendiri
    dari device), tapi status "sekarang lagi JP berapa / istirahat / di luar
    jam pelajaran" -- itu info yang jelas dan harus paling menonjol. Jam &
    tanggal tetap ditampilkan (kadang berguna), tapi dikecilin jadi baris
    keterangan di atas, bukan elemen utama widget ini lagi.

    Baris tanggal+jam dibikin flex-wrap (bukan dipaksa 1 baris) biar aman di
    HP sempit (~320px) -- dulu pernah overflow pas dipaksa sejajar sama
    badge status di 1 baris yang sama, makanya sekarang statusnya emang
    dipisah ke baris sendiri (dan sekarang jadi baris UTAMA, bukan badge kecil).
--}}
<div class="mb-4 flex flex-col gap-1 rounded-2xl bg-navy px-4 py-3.5 text-card">
    <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-card/60">
        <x-icon name="schedule" :size="14" class="shrink-0" />
        <span>{{ $tanggalIndo }}</span>
        <span aria-hidden="true">·</span>
        <span class="tabular-nums" data-jam-sekarang>{{ now()->format('H:i:s') }}</span>
    </div>
    <p class="text-xl font-bold leading-tight lg:text-2xl">
        {{ $jpSekarang ? 'Sedang JP '.$jpSekarang : ($dalamJamSekolah ? 'Waktu Istirahat' : 'Di luar jam pelajaran') }}
    </p>
</div>
