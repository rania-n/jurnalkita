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
         border lebih kentara. Ikon per jenis notifikasi (bukan cuma titik
         polos) biar langsung kelihatan itu notifikasi soal apa (Jurnal,
         Dispensasi, dst), bukan sekadar tanda "ada sesuatu". --}}
    <div class="flex flex-col gap-1.5 pb-16">
        @foreach ($notifikasiTerbaru as $n)
            <a href="{{ route('notifikasi.buka', $n->id) }}" @class([
                'flex items-start gap-2.5 rounded-xl border px-3 py-2.5 transition-colors hover:border-navy/30',
                'border-alpha/20 bg-alpha-soft/40' => is_null($n->read_at),
                'border-surface-alt bg-card' => ! is_null($n->read_at),
            ])>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-navy">
                    <x-icon :name="$n->data['icon'] ?? 'notifications'" :size="18" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-ink">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                    <span class="block truncate text-xs text-muted">{{ $n->data['body'] ?? '' }}</span>
                    <span class="block text-[11px] text-muted-2">{{ $n->created_at->diffForHumans() }}</span>
                </span>
            </a>
        @endforeach
    </div>
@endif

{{-- Sticky di bawah bagian yang bisa di-scroll (lihat wrapper
     "overflow-y-auto" di app-topbar.blade.php) -- daftar notifikasi bisa
     panjang, tombolnya jangan sampai ketimbun di bawah, harus tetap
     kepegang tanpa scroll ke dasar dulu. Link "Lihat semua notifikasi"
     dihapus -- popup ini sendiri sudah isinya semua notifikasi, jadi
     nggak perlu halaman riwayat terpisah lagi.

     z-10 + posisi relatif WAJIB ada -- tanpa itu baris notifikasi (link)
     yang lagi di-scroll lewat bisa "nembus" ke atas bar sticky ini (keliatan
     tumpang tindih & link-nya yang kepencet, bukan tombolnya) karena
     dua-duanya nggak eksplisit punya urutan tumpukan (stacking order). --}}
@if ($jumlahBelumDibaca > 0)
    <div class="sticky -bottom-5 z-10 mt-4 -mx-5 -mb-5 border-t border-surface-alt bg-card px-5 py-3">
        <form method="POST" action="{{ route('notifikasi.tandai-semua-dibaca') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary" icon="done_all" class="w-full !h-10 !text-sm">Tandai Semua Dibaca</x-ui.button>
        </form>
    </div>
@endif
