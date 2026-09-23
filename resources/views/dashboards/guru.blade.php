@php
    $guru = auth()->user()->guru;
    $hari = \App\Support\HariSekolah::hariIni();
    $jadwalHariIni = $hari && $guru
        ? $guru->jadwals()->with('kelas', 'mapel')->where('hari', $hari)->orderBy('jam_ke_mulai')->get()
        : collect();
    $piketHariIni = auth()->user()->piketHariIni();
    $isWali = auth()->user()->isWali();

    // Jadwal yang jurnalnya sudah diisi hari ini
    $sudahDiisi = $guru
        ? $guru->jurnals()->whereDate('tanggal', today())->pluck('jadwal_id')->all()
        : [];

    // Pilihan awal cuma ditampilkan SEKALI per login (bukan tiap kali buka
    // dasbor) -- ditandai session (bukan localStorage) biar konsisten walau
    // guru buka dari perangkat/browser berbeda tiap login.
    $tampilkanPilihanAwal = ! session('pilihan_awal_guru_tampil');
    session(['pilihan_awal_guru_tampil' => true]);
@endphp

<x-layouts.app title="Beranda Guru" width="wide">
    <x-page-header title="Beranda" :subtitle="'Selamat mengajar, ' . auth()->user()->name" />

    <x-ui.jam-sekarang :jp-sekarang="\App\Support\Waktu::jpAktifSekarang()" />

    @if ($tampilkanPilihanAwal)
        <dialog id="modal-pilihan-awal"
                class="fixed inset-0 m-auto w-[min(26rem,calc(100vw-2rem))] rounded-2xl border-0 bg-card p-0 text-ink shadow-2xl backdrop:bg-navy/30 backdrop:backdrop-blur-sm">
            <div class="flex flex-col items-center gap-1 px-6 pb-2 pt-7 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-surface-alt text-navy">
                    <x-icon name="waving_hand" :size="28" />
                </span>
                <h3 class="mt-2 text-lg font-bold text-ink">Halo, {{ auth()->user()->name }}!</h3>
                @if ($piketHariIni)
                    <p class="text-sm text-muted">Anda bertugas piket hari ini. Mau langsung ke monitor piket, atau lihat-lihat beranda dulu?</p>
                @else
                    <p class="text-sm text-muted">Mau langsung isi jurnal, atau lihat-lihat beranda dulu?</p>
                @endif
            </div>

            <div class="flex flex-col gap-2 p-6 pt-4">
                {{-- Hari piket: guru nggak dijadwalkan mengajar (lihat catatan di
                     bawah), jadi jangan tawarin "Isi Jurnal" di sini juga --
                     tawarkan hal yang relevan sama piketnya. --}}
                @if ($piketHariIni)
                    <x-ui.button :href="route('piket.monitor.index')" icon="monitoring" class="w-full">Pantau Piket Sekarang</x-ui.button>
                @else
                    <x-ui.button :href="route('jurnal.create')" icon="edit_note" class="w-full">Isi Jurnal Sekarang</x-ui.button>
                @endif
                <x-ui.button type="button" variant="secondary" data-modal-close class="w-full">Lihat Beranda Dulu</x-ui.button>
            </div>
        </dialog>

        @push('scripts')
            <script>document.getElementById('modal-pilihan-awal')?.showModal();</script>
        @endpush
    @endif

    @if ($piketHariIni)
        <x-alert type="info" class="mb-4">Anda bertugas <strong>piket</strong> hari ini.</x-alert>

        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <a href="{{ route('piket.monitor.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="monitoring" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Monitor Piket</p>
                    <p class="text-xs text-muted">Pantau kehadiran guru & kelas yang belum diisi jurnalnya</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>

            <a href="{{ route('dispensasi.create') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                    <x-icon name="fact_check" :size="24" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-ink">Ajukan Dispensasi</p>
                    <p class="text-xs text-muted">Siswa izin keluar / tidak mengikuti pelajaran</p>
                </div>
                <x-icon name="chevron_right" :size="20" class="text-muted" />
            </a>
        </div>
    @endif

    @if ($isWali && ! $piketHariIni)
        <a href="{{ route('guru.wali-kelas.index') }}" class="press mb-6 flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="groups" :size="24" />
            </span>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">Wali Kelas</p>
                <p class="text-xs text-muted">Lihat rekap kehadiran kelas yang Anda ampu</p>
            </div>
            <x-icon name="chevron_right" :size="20" class="text-muted" />
        </a>
    @endif

    {{-- Hari piket: guru nggak dijadwalkan mengajar (lihat catatan di atas), jadi
         bagian jurnal/jadwal mengajar sengaja disembunyikan biar nggak rancu --
         fokus ke piket & dispensasi aja hari itu. --}}
    @unless ($piketHariIni)
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-ink">Jadwal Mengajar Hari Ini</h2>
            <a href="{{ route('jurnal.index') }}" class="text-sm font-semibold text-navy">Riwayat Jurnal →</a>
        </div>

        @if ($jadwalHariIni->isEmpty())
            <x-ui.empty icon="event_busy" title="Tidak ada jadwal hari ini" desc="Mau isi jurnal untuk jadwal lain? Pilih dari daftar jadwal Anda.">
                <x-ui.button :href="route('jurnal.create')" icon="edit_note" class="mt-2">Isi Jurnal</x-ui.button>
            </x-ui.empty>
        @else
            <x-ui.card-list class="grid-fill-last">
                @foreach ($jadwalHariIni as $j)
                    @php $statusJam = \App\Support\Waktu::statusJpHariIni($j->jam_ke_mulai, $j->jam_ke_selesai); @endphp
                    <x-ui.list-card
                        :title="$j->mapel->nama"
                        :meta="[$j->kelas->nama . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai, 'Ruang ' . ($j->ruang ?? '-')]"
                    >
                        @if ($statusJam)
                            <x-slot:badge>
                                <x-ui.status-badge :status="$statusJam" />
                            </x-slot:badge>
                        @endif
                        <x-slot:actions>
                            @if (in_array($j->id, $sudahDiisi))
                                <x-ui.action-button label="Sudah diisi" icon="check_circle" variant="success" href="{{ route('jurnal.index') }}" />
                            @else
                                <x-ui.action-button label="Isi Jurnal" icon="edit_note" variant="info" :href="route('jurnal.create', ['jadwal' => $j->id])" />
                            @endif
                        </x-slot:actions>
                    </x-ui.list-card>
                @endforeach
            </x-ui.card-list>
        @endif
    @endunless
</x-layouts.app>
