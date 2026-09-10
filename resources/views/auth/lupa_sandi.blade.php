<x-layouts.guest title="Lupa Kata Sandi">
    <a href="{{ route('login') }}" class="press mb-6 inline-flex items-center gap-1.5 rounded-lg bg-surface-alt py-2 pl-2 pr-3 text-sm font-semibold text-ink hover:bg-[#cbd5e1]">
        <x-icon name="arrow_back" :size="18" />
        <span>Kembali</span>
    </a>

    <form method="POST" action="{{ route('lupa_sandi') }}" class="flex flex-col gap-6">
        @csrf

        <div class="flex flex-col items-center gap-4 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-[24px] bg-sakit-soft text-sakit">
                <x-icon name="lock" :size="32" fill />
            </div>
            <div class="flex flex-col gap-2">
                <h1 class="text-[22px] font-extrabold text-ink">Lupa Kata Sandi?</h1>
                <p class="text-sm leading-relaxed text-muted-2">
                    Masukkan email atau nomor WhatsApp Anda. Admin sekolah akan membantu mereset kata sandi Anda.
                </p>
            </div>
        </div>

        <x-ui.input label="Email / No. WhatsApp" name="kontak" placeholder="Contoh: 081234567890" :value="old('kontak')" required />

        <x-ui.button type="submit" block>Kirim Permintaan Reset</x-ui.button>
        <x-ui.button href="#" variant="success" block icon="headset_mic">Hubungi Pusat Bantuan</x-ui.button>
    </form>
</x-layouts.guest>
