@props(['jpSekarang' => null])

@php
    // translatedFormat('l, F') nggak beneran nerjemahin -- APP_LOCALE app ini
    // 'en', jadi "Tuesday, January" bukan "Selasa, Januari" kalau dipaksa lewat
    // situ. Sama kayak pola yang sudah dipakai di tempat lain (mis. config
    // akademik.hari), nama hari/bulan Indonesia ditulis manual di sini.
    $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggalIndo = $hariIndo[now()->dayOfWeek].', '.now()->day.' '.$bulanIndo[now()->month].' '.now()->year;
@endphp

{{--
    Widget waktu sekarang -- ditaruh di atas tiap dasbor biar nggak cuma
    kotak-kotak statistik doang, guru/waka/dll langsung lihat ini lagi jam
    berapa & lagi JP berapa tanpa harus lirik jam device sendiri. Jamnya
    jalan tiap detik lewat JS (initJamSekarang() di app.js); JP-nya dihitung
    sekali pas render (cukup, halaman biasa dibuka ulang tiap beberapa menit).
--}}
<div class="mb-4 flex items-center justify-between gap-3 rounded-2xl bg-navy px-4 py-3.5 text-card">
    <div class="flex items-center gap-2.5">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15">
            <x-icon name="schedule" :size="20" />
        </span>
        <div>
            <p class="text-xs text-card/70">{{ $tanggalIndo }}</p>
            <p class="text-lg font-bold tabular-nums leading-tight" data-jam-sekarang>{{ now()->format('H:i') }}</p>
        </div>
    </div>
    <span class="shrink-0 rounded-lg bg-white/15 px-3 py-1.5 text-right text-xs font-bold">
        {{ $jpSekarang ? 'Sedang JP '.$jpSekarang : 'Di luar jam pelajaran' }}
    </span>
</div>
