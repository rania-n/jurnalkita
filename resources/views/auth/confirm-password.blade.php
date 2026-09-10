<x-layouts.guest title="Konfirmasi Kata Sandi">
    <div class="mb-6 flex flex-col gap-1">
        <h1 class="text-[22px] font-extrabold text-ink">Konfirmasi Kata Sandi</h1>
        <p class="text-sm text-muted-2">Ini area aman. Masukkan kata sandi Anda untuk melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="flex flex-col gap-4">
        @csrf
        <x-ui.input label="Kata Sandi" name="password" type="password" placeholder="Masukkan kata sandi" required autofocus />
        <x-ui.button type="submit" block>Konfirmasi</x-ui.button>
    </form>
</x-layouts.guest>
