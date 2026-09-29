<x-layouts.guest title="Masuk">
    <div class="flex flex-col items-center gap-2 pb-6 text-center">
        <div class="mb-1 flex h-16 w-16 items-center justify-center rounded-[18px] bg-navy text-card">
            <x-icon name="menu_book" :size="30" fill />
        </div>
        <h1 class="text-2xl font-extrabold leading-tight text-navy">jurnalkita</h1>
        <p class="text-[13px] font-medium text-muted-2">Sistem Jurnal &amp; Absensi Guru SMKN 1 Boyolangu</p>
    </div>

    @if (session('status') || session('info'))
        <x-alert type="info" class="mb-4">{{ session('status') ?? session('info') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan email" :value="old('email')" autofocus required />

        <x-ui.input label="Kata Sandi" name="password" type="password" id="password" placeholder="Masukkan kata sandi" required />

        <div class="flex items-center justify-end">
            <a href="{{ route('password.request') }}" class="text-[13px] font-bold text-navy">Lupa Sandi?</a>
        </div>

        <x-ui.button type="submit" block class="mt-1">Masuk</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-muted-2">
        Belum punya akun?
        <a href="{{ route('pilih_peran') }}" class="font-bold text-navy">Daftar Akun Baru</a>
    </p>

    @if (\App\Models\PengaturanJurnal::tampilkanDiLogin())
        {{-- Referensi jurnal ditampilkan hanya jika diaktifkan Admin. --}}
        <button type="button" data-modal-open="modal-jurnal-hari-ini" data-ajax-url="{{ route('piket.popup-hari-ini') }}"
            class="mt-4 flex w-full items-center justify-center gap-1.5 text-[13px] font-bold text-navy">
            <x-icon name="visibility" :size="16" />
            Lihat Jurnal Hari Ini
        </button>

        <x-ui.modal id="modal-jurnal-hari-ini" title="Jurnal Hari Ini" errorBag="tidak-dipakai">
            <div data-modal-ajax-target></div>
        </x-ui.modal>
    @endif

</x-layouts.guest>
