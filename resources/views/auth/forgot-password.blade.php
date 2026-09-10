<x-layouts.guest title="Lupa Kata Sandi">
    <a href="{{ route('login') }}" class="press mb-6 inline-flex items-center gap-1.5 rounded-lg bg-surface-alt py-2 pl-2 pr-3 text-sm font-semibold text-ink hover:bg-[#cbd5e1]">
        <x-icon name="arrow_back" :size="18" />
        <span>Kembali</span>
    </a>

    @if (session('status'))
        <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif

    <div class="mb-6 flex flex-col items-center gap-4 text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-[24px] bg-sakit-soft text-sakit">
            <x-icon name="lock" :size="32" fill />
        </div>
        <div class="flex flex-col gap-2">
            <h1 class="text-[22px] font-extrabold text-ink">Lupa Kata Sandi?</h1>
            <p class="text-sm leading-relaxed text-muted-2">
                Masukkan email Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
        @csrf
        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan email terdaftar" :value="old('email')" autofocus required />
        <x-ui.button type="submit" block>Kirim Tautan Reset</x-ui.button>
    </form>
</x-layouts.guest>
