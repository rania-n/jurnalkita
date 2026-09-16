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

    $notifikasiTerbaru = $user?->notifications()->latest()->limit(8)->get() ?? collect();
    $jumlahBelumDibaca = $user?->unreadNotifications->count() ?? 0;
@endphp

<header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-surface-alt bg-card/95 px-4 py-2.5 backdrop-blur sm:px-6 lg:px-10">
    {{-- Identitas: avatar + nama + role, format SAMA di semua ukuran layar & semua
         peran -- dulu nama cuma kelihatan di desktop, role chip cuma di >= sm,
         jadi di HP sempit nggak ada identitas sama sekali selain avatar polos. --}}
    <a href="{{ route('profil') }}" class="flex min-w-0 items-center gap-2.5">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy text-xs font-bold text-card">{{ $inisial }}</span>
        <span class="flex min-w-0 flex-col leading-tight">
            <span class="truncate text-sm font-semibold text-ink">{{ $nama }}</span>
            <span class="truncate text-[11px] font-bold text-muted">{{ $roleLabel }}</span>
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

        <button type="button" data-modal-open="modal-notifikasi" class="relative flex h-9 w-9 items-center justify-center rounded-full bg-surface-alt text-muted transition-colors hover:text-navy" aria-label="Notifikasi">
            <x-icon name="notifications" :size="20" />
            @if ($jumlahBelumDibaca > 0)
                <span class="absolute right-1 top-1 flex h-2.5 w-2.5 rounded-full bg-alpha ring-2 ring-card"></span>
            @endif
        </button>

        {{-- Logout langsung di header -- dulu cuma bisa lewat Profil (guru bingung
             nyarinya), sekarang bisa dari halaman mana pun tanpa muter dulu.
             Di desktop disembunyikan karena sidebar sudah punya tombol logout. --}}
        <span class="lg:hidden">
            <x-logout-button variant="icon" />
        </span>
    </div>
</header>

{{-- errorBag khusus yang nggak pernah dipakai form manapun -- form "tandai
     semua dibaca" di dalam modal ini nggak divalidasi jadi nggak pernah
     gagal, tapi kalau pakai bag default ($errors->any()), modal ini ikut
     kebuka nggak diundang tiap kali ADA form lain (mis. Buat Akun) yang
     gagal validasi di halaman yang sama. --}}
<x-ui.modal id="modal-notifikasi" title="Notifikasi" size="lg" errorBag="tidak-dipakai">
    @if ($notifikasiTerbaru->isEmpty())
        <x-ui.empty icon="notifications" title="Belum ada notifikasi" desc="Pemberitahuan tentang jurnal & dispensasi Anda akan muncul di sini." />
    @else
        <div class="flex flex-col gap-1.5">
            @foreach ($notifikasiTerbaru as $n)
                <a href="{{ route('notifikasi.buka', $n->id) }}" @class(['flex items-start gap-2.5 rounded-xl px-3 py-2.5 transition-colors hover:bg-surface-alt', 'bg-surface-alt' => is_null($n->read_at)])>
                    @if (is_null($n->read_at))
                        <span class="mt-1.5 flex h-2 w-2 shrink-0 rounded-full bg-alpha"></span>
                    @else
                        <span class="mt-1.5 h-2 w-2 shrink-0"></span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-ink">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                        <span class="block truncate text-xs text-muted">{{ $n->data['body'] ?? '' }}</span>
                        <span class="block text-[11px] text-muted-2">{{ $n->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        @if ($jumlahBelumDibaca > 0)
            <form method="POST" action="{{ route('notifikasi.tandai-semua-dibaca') }}" class="mt-3 border-t border-surface-alt pt-3">
                @csrf
                <x-ui.button type="submit" variant="secondary" icon="done_all" class="w-full !h-10 !text-sm">Tandai Semua Dibaca</x-ui.button>
            </form>
        @endif

        <a href="{{ route('notifikasi.index') }}" class="mt-2 block text-center text-sm font-semibold text-navy hover:underline">Lihat semua notifikasi</a>
    @endif
</x-ui.modal>
