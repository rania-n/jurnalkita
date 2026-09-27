@props([
    'jadwal', // Jadwal|null -- null berarti komponen ini nggak nge-render apa-apa
])

{{--
    Kartu sorotan jadwal BERIKUTNYA hari ini -- dipakai di dashboard Guru,
    Waka, dan Pengurus Kelas biar langsung kelihatan pelajaran apa yang
    akan datang tanpa harus scroll ke daftar jadwal lengkap. Yang lagi
    BENERAN berlangsung sengaja tidak disorot di sini -- itu sudah cukup
    kelihatan & bisa diisi dari daftar jadwal biasa di bawah kartu ini.
    Sumber datanya dari Waktu::jadwalSorotan().

    Murni informasi, SENGAJA TIDAK ada tombol aksi -- jadwal berikutnya
    belum bisa diisi jurnalnya sama sekali (yang bisa diisi cuma jadwal
    yang SEDANG berlangsung), jadi nggak ada tombol yang bisa ditawarkan
    di sini tanpa menyesatkan.

    Kelas ditulis besar (bukan mata pelajaran) -- itu yang paling berubah-
    ubah dan perlu langsung kelihatan sekilas; mata pelajaran biasanya
    sudah otomatis diketahui guru yang bersangkutan.
--}}
@if ($jadwal)
    @php
        $jamOpsi = \App\Support\Waktu::rentangJam($jadwal->jam_ke_mulai, $jadwal->jam_ke_selesai);
    @endphp
    <div {{ $attributes->class('mb-4 rounded-2xl bg-navy p-4 text-card shadow-[var(--shadow-soft)] sm:p-5') }}>
        <div class="flex items-center gap-2">
            <x-icon name="schedule" :size="14" class="shrink-0 text-card/80" />
            <span class="text-xs font-bold tracking-wide text-card/80 uppercase">Jadwal Berikutnya</span>
        </div>

        <div class="mt-3 min-w-0">
            <h3 class="truncate text-lg font-extrabold text-card">{{ $jadwal->kelas->nama }}</h3>
            <p class="mt-0.5 truncate text-base font-semibold text-card">{{ $jadwal->mapel->nama }}</p>
            <p class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium text-card/90">
                <span class="flex items-center gap-1">
                    <x-icon name="schedule" :size="14" />
                    JP {{ $jadwal->jam_ke_mulai }}–{{ $jadwal->jam_ke_selesai }}{{ $jamOpsi ? " · {$jamOpsi}" : '' }}
                </span>
                <span class="flex items-center gap-1">
                    <x-icon name="location_on" :size="14" />
                    Ruang {{ $jadwal->ruang ?? '-' }}
                </span>
            </p>
        </div>
    </div>
@endif
