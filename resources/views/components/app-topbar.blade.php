@php
    $user = auth()->user();
    $nama = $user?->name ?? 'Pengguna';
    $roleLabel = $user?->roleLabel() ?? '';
    $inisial = collect(explode(' ', $nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');

    // Hubungi Admin -- ditaruh di topbar biar bisa dijangkau dari halaman MANA PUN,
    // bukan cuma pas kebetulan lagi di beranda. Berlaku semua peran non-admin yang
    // pakai shell ini (admin sendiri pakai layouts.admin, nggak lewat sini).
    $admin = $user && $user->role !== 'admin'
        ? \App\Models\User::where('role', 'admin')->whereNotNull('no_hp')->first()
        : null;
    $waLinkAdmin = $admin ? \App\Support\WaLink::url($admin->no_hp, "Halo Admin jurnalkita, saya {$nama} ({$roleLabel}), mau tanya soal akun/jadwal.") : null;

    $jumlahBelumDibaca = $user?->unreadNotifications()->count() ?? 0;

    $isPiketHariIni = $user && $user->role === 'guru' && $user->piketHariIni();
    $tampilkanStatusBar = $user && $user->role !== 'admin';

    // Satu bar waktu untuk semua role non-admin. Admin memakai layout sendiri.
    if ($tampilkanStatusBar) {
        $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bulanPendek = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $tanggalIndo = $hariIndo[now()->dayOfWeek].', '.now()->day.' '.$bulanIndo[now()->month].' '.now()->year;
        $tanggalSingkat = $hariIndo[now()->dayOfWeek].', '.now()->day.' '.$bulanPendek[now()->month];
        $jpAktif = \App\Support\Waktu::jpAktifSekarang();
        $dalamJamSekolah = \App\Support\Waktu::dalamJamSekolah();
        $statusWaktu = $jpAktif ? 'Sedang JP '.$jpAktif : ($dalamJamSekolah ? 'Waktu Istirahat' : 'Di luar jam pelajaran');
    }

    if ($isPiketHariIni) {
        $jpTerakhirHariIni = \App\Models\JamPelajaran::where('kategori', \App\Support\Waktu::kategori())
            ->orderByDesc('jam_ke')->first();
        $subHeader = 'Bertugas Piket'.($jpTerakhirHariIni ? ' s/d '.$jpTerakhirHariIni->selesai->format('H.i') : ' Hari Ini');
    } else {
        $subHeader = $roleLabel;
    }
@endphp

<div class="sticky top-0 z-30">
    <header class="flex items-center justify-between gap-3 border-b border-surface-alt bg-card/95 px-4 py-2.5 backdrop-blur sm:px-6 lg:px-10">
        {{-- Identitas: avatar + nama + role, format SAMA di semua ukuran layar & semua
             peran -- dulu nama cuma kelihatan di desktop, role chip cuma di >= sm,
             jadi di HP sempit nggak ada identitas sama sekali selain avatar polos. --}}
        <a href="{{ route('profil') }}" class="flex min-w-0 items-center gap-2.5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy text-xs font-bold text-card">{{ $inisial }}</span>
            <span class="flex min-w-0 flex-col leading-tight">
                <span class="truncate text-sm font-semibold text-ink">{{ $nama }}</span>
                <span class="truncate text-[11px] font-bold text-muted">{{ $subHeader }}</span>
            </span>
        </a>

        <div class="flex shrink-0 items-center gap-1.5">
            @if ($waLinkAdmin)
                <a href="{{ $waLinkAdmin }}" target="_blank" rel="noopener"
                   class="flex h-9 w-9 items-center justify-center rounded-full bg-surface-alt text-muted transition-colors hover:text-navy"
                   aria-label="Hubungi Admin">
                    <x-icon name="support_agent" :size="20" />
                </a>
            @endif

            {{-- data-notif-titik: titik merahnya di-toggle otomatis tiap ~20 detik
                 lewat polling ringan (initNotifikasiPoll() di app.js), jadi kalau
                 ada notifikasi baru masuk selagi halaman ini kebuka (mis. guru
                 lain submit jurnal), guru/sekre nggak perlu reload manual buat
                 lihat ada yang baru. --}}
            <button type="button" data-modal-open="modal-notifikasi" data-ajax-url="{{ route('notifikasi.fragment') }}" data-notif-jumlah-url="{{ route('notifikasi.jumlah') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full bg-surface-alt text-muted transition-colors hover:text-navy" aria-label="Notifikasi">
                <x-icon name="notifications" :size="20" />
                <span data-notif-titik class="absolute right-1 top-1 flex h-2.5 w-2.5 rounded-full bg-alpha ring-2 ring-card" @unless($jumlahBelumDibaca > 0) hidden @endunless></span>
            </button>

            {{-- Logout langsung di header -- dulu cuma bisa lewat Profil (guru bingung
                 nyarinya), sekarang bisa dari halaman mana pun tanpa muter dulu.
                 Di desktop disembunyikan karena sidebar sudah punya tombol logout. --}}
            <span class="lg:hidden">
                <x-logout-button variant="icon" />
            </span>
        </div>
    </header>

    @if ($tampilkanStatusBar)
        {{-- Status waktu konsisten untuk semua peran non-admin; admin menggunakan header khususnya. --}}
        <div class="px-3 sm:px-6 lg:px-10">
            <div class="-mt-px flex items-center justify-between gap-2 rounded-b-xl bg-navy px-3.5 py-2 text-card shadow-sm sm:rounded-b-2xl sm:px-5 sm:py-1.5">

                {{-- Kiri: Status Waktu — paling menonjol --}}
                <p class="text-sm font-bold leading-tight text-card sm:text-base">
                    {{ $statusWaktu }}
                </p>

                {{-- Kanan: tanggal + jam berjalan --}}
                <div class="flex shrink-0 items-center gap-1 text-[11px] text-card sm:text-xs">
                    <x-icon name="schedule" :size="11" class="shrink-0 text-card/80" />
                    <span class="hidden sm:inline">{{ $tanggalIndo }}</span>
                    <span class="sm:hidden">{{ $tanggalSingkat }}</span>
                    <span class="text-card/60">·</span>
                    <span class="tabular-nums font-mono" data-jam-sekarang>{{ now()->format('H:i:s') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- errorBag khusus yang nggak pernah dipakai form manapun -- form "tandai
     semua dibaca" di dalam modal ini nggak divalidasi jadi nggak pernah
     gagal, tapi kalau pakai bag default ($errors->any()), modal ini ikut
     kebuka nggak diundang tiap kali ADA form lain (mis. Buat Akun) yang
     gagal validasi di halaman yang sama. --}}
<x-ui.modal id="modal-notifikasi" title="Notifikasi" size="lg" errorBag="tidak-dipakai">
    <div data-modal-ajax-target></div>
</x-ui.modal>
