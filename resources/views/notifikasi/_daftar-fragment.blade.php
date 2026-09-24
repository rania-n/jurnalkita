{{--
    Isi popup lonceng notifikasi -- di-fetch AJAX tiap kali modalnya dibuka
    (lihat app-topbar.blade.php + initModals() di app.js), BUKAN dirender
    sekali pas topbar-nya muncul. Soalnya kalau statis, notifikasi baru yang
    masuk selama halaman kebuka (mis. jurnal disubmit guru lain) nggak akan
    kelihatan sampai reload manual -- padahal titik merah di lonceng sendiri
    udah di-update duluan lewat polling ringan (initNotifikasiPoll()).

    Variabel yang wajib ada di scope pemanggil: -- (self-contained, ambil auth()->user() sendiri)
--}}
@php
    $notifikasiTerbaru = auth()->user()->notifications()->latest()->limit(8)->get();
    $jumlahBelumDibaca = auth()->user()->unreadNotifications()->count();
@endphp

@if ($notifikasiTerbaru->isEmpty())
    <x-ui.empty icon="notifications" title="Belum ada notifikasi" desc="Pemberitahuan tentang jurnal & dispensasi Anda akan muncul di sini." />
@else
    {{-- Baris yang UDAH dibaca dulu nggak punya border/background sama sekali
         -- nyaru sama badan modal yang putih juga, kesannya cuma teks
         ngambang tanpa batas kartu. Sekarang semua baris punya border tipis
         (rounded card), yang belum dibaca dibedain lewat aksen background +
         border lebih kentara. --}}
    <div class="flex flex-col gap-1.5">
        @foreach ($notifikasiTerbaru as $n)
            <a href="{{ route('notifikasi.buka', $n->id) }}" @class([
                'flex items-start gap-2.5 rounded-xl border px-3 py-2.5 transition-colors hover:border-navy/30',
                'border-alpha/20 bg-alpha-soft/40' => is_null($n->read_at),
                'border-surface-alt bg-card' => ! is_null($n->read_at),
            ])>
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
