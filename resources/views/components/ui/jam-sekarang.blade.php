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
    Dulu tanggal/jam & badge status disatuin dalam 1 baris (flex justify-between)
    -- di HP sempit (~320px) badge-nya kepotong/ke luar layar & tanggalnya
    numpuk jadi 3 baris berantakan (teksnya ketekan sama badge di sisi kanan).
    Sekarang dipisah: baris atas cuma ikon+tanggal+jam (nggak ada yang
    berebut lebar), baris bawah badge status full-width sendiri -- nggak
    akan overflow di lebar layar berapa pun.
--}}
<div class="mb-4 flex flex-col gap-2.5 rounded-2xl bg-navy px-4 py-3.5 text-card">
    <div class="flex items-center gap-2.5">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15">
            <x-icon name="schedule" :size="20" />
        </span>
        <div class="min-w-0">
            <p class="truncate text-xs text-card/70">{{ $tanggalIndo }}</p>
            <p class="text-lg font-bold tabular-nums leading-tight" data-jam-sekarang>{{ now()->format('H:i:s') }}</p>
        </div>
    </div>
    <span class="w-full rounded-lg bg-white/15 px-3 py-1.5 text-center text-xs font-bold">
        {{ $jpSekarang ? 'Sedang JP '.$jpSekarang : ($dalamJamSekolah ? 'Waktu Istirahat' : 'Di luar jam pelajaran') }}
    </span>
</div>
