<x-layouts.guest title="Atur Ulang Kata Sandi">
    <div class="mb-6 flex flex-col gap-1">
        <h1 class="text-[22px] font-extrabold text-ink">Atur Ulang Kata Sandi</h1>
        <p class="text-sm text-muted-2">Buat kata sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.input label="Email" name="email" type="email" :value="old('email', $request->email)" required autofocus />

        <x-ui.input label="Kata Sandi Baru" name="password" type="password" id="rp-password" placeholder="Buat kata sandi baru" required>
            <button type="button" data-toggle-password="#rp-password" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <x-ui.input label="Konfirmasi Kata Sandi" name="password_confirmation" type="password" placeholder="Ulangi kata sandi" required />

        <x-ui.button type="submit" block>Simpan Kata Sandi</x-ui.button>
    </form>
</x-layouts.guest>
