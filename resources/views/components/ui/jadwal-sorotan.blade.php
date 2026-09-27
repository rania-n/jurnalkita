@props([
    'jadwal',           // Jadwal|null -- null berarti komponen ini nggak nge-render apa-apa
    'subtitle' => null, // default: nama kelas jadwal ini -- override kalau kelasnya sudah
                        // jelas dari konteks (mis. dashboard Pengurus Kelas), diganti nama guru
])

{{--
    Kartu sorotan jadwal BERIKUTNYA hari ini -- dipakai di dashboard Guru,
    Waka, dan Pengurus Kelas biar langsung kelihatan pelajaran apa yang
    akan datang tanpa harus scroll ke daftar jadwal lengkap. Yang lagi
    BENERAN berlangsung sengaja tidak disorot di sini -- itu sudah cukup
    kelihatan & bisa diisi dari daftar jadwal biasa di bawah kartu ini.
    Sumber datanya dari Waktu::jadwalSorotan().

    Slot:
      - actions : tombol aksi di kanan (mis. <x-ui.button> "Isi Jurnal")
--}}
@if ($jadwal)
    @php
        $jamOpsi = \App\Support\Waktu::rentangJam($jadwal->jam_ke_mulai, $jadwal->jam_ke_selesai);
        $subtitle ??= $jadwal->kelas->nama;
    @endphp
    <div {{ $attributes->class('mb-4 rounded-2xl bg-navy p-4 text-card shadow-[var(--shadow-soft)] sm:p-5') }}>
        <div class="flex items-center gap-2">
            <x-icon name="schedule" :size="14" class="shrink-0 text-card/70" />
            <span class="text-xs font-bold tracking-wide text-card/70 uppercase">Jadwal Berikutnya</span>
        </div>

        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h3 class="truncate text-lg font-extrabold text-card">{{ $jadwal->mapel->nama }}</h3>
                <p class="mt-0.5 truncate text-sm text-card/80">{{ $subtitle }}</p>
                <p class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-card/70">
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

            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    </div>
@endif
