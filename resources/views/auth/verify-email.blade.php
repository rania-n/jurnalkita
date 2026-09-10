<x-layouts.guest title="Verifikasi Email">
    <div class="flex flex-col items-center gap-4 text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-[24px] bg-izin-soft text-izin">
            <x-icon name="mark_email_read" :size="32" fill />
        </div>
        <div class="flex flex-col gap-2">
            <h1 class="text-[22px] font-extrabold text-ink">Verifikasi Email Anda</h1>
            <p class="text-sm leading-relaxed text-muted-2">
                Kami telah mengirim tautan verifikasi ke email Anda. Klik tautan tersebut untuk melanjutkan.
                Belum menerima? Kirim ulang di bawah.
            </p>
        </div>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-alert type="success" class="mt-4">Tautan verifikasi baru sudah dikirim ke email Anda.</x-alert>
    @endif

    <div class="mt-6 flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit" block icon="send">Kirim Ulang Tautan</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" block>Keluar</x-ui.button>
        </form>
    </div>
</x-layouts.guest>
