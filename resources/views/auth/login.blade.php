<x-layouts.guest title="Masuk">
    <div class="flex flex-col items-center gap-2 pt-10 pb-9 text-center">
        <div class="mb-2 flex h-[72px] w-[72px] items-center justify-center rounded-[20px] bg-navy text-card">
            <x-icon name="menu_book" :size="34" fill />
        </div>
        <h1 class="text-[28px] font-extrabold leading-tight text-navy">jurnalkita</h1>
        <p class="text-[13px] font-medium text-muted-2">Sistem Jurnal &amp; Absensi Guru SMKN 1 Boyolangu</p>
    </div>

    @if (session('status') || session('info'))
        <x-alert type="info" class="mb-4">{{ session('status') ?? session('info') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan email" :value="old('email')" autofocus />

        <x-ui.input label="Kata Sandi" name="password" type="password" id="password" placeholder="Masukkan kata sandi">
            <button type="button" data-toggle-password="#password" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <div class="flex items-center justify-between">
            <label class="flex cursor-pointer items-center gap-2">
                <input type="checkbox" name="remember" class="h-5 w-5 rounded accent-navy">
                <span class="text-[13px] text-muted-2">Tetap masuk selama 30 hari</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-[13px] font-bold text-navy">Lupa Sandi?</a>
        </div>

        <x-ui.button type="submit" block class="mt-2">Masuk</x-ui.button>

        <x-ui.button href="#" variant="success" block icon="headset_mic">Hubungi Pusat Bantuan</x-ui.button>
    </form>

    <p class="mt-10 text-center text-sm text-muted-2">
        Belum punya akun?
        <a href="{{ route('pilih_peran') }}" class="ml-1 font-bold text-navy">Daftar Akun Baru</a>
    </p>
</x-layouts.guest>
