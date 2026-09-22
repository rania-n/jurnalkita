<x-layouts.guest title="Registrasi Guru">
    <x-page-header
        title="Registrasi Guru"
        subtitle="Lengkapi biodata pengajar Anda"
        :back="url()->previous(route('pilih_peran'))"
    />

    <x-alert type="warning" class="mb-6">
        Akun Anda akan diverifikasi oleh Admin sebelum dapat digunakan.
    </x-alert>

    <form method="POST" action="{{ route('register.guru') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Masukkan nama lengkap guru" :value="old('nama')" required />
        <x-ui.input label="NIP (opsional)" name="nip" inputmode="numeric" placeholder="Masukkan NIP jika ada" :value="old('nip')" />
        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan alamat email aktif" :value="old('email')" required />
        <x-ui.input label="No. Telepon" name="telepon" inputmode="numeric" placeholder="Masukkan nomor WhatsApp aktif" :value="old('telepon')" />

        <x-ui.input label="Password" name="password" type="password" id="reg-password" placeholder="Buat kata sandi baru" hint="Minimal 8 karakter." required />

        <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" id="reg-password-confirm" placeholder="Ulangi kata sandi" required />

        <x-ui.button type="submit" block>Daftar Akun Guru</x-ui.button>
    </form>
</x-layouts.guest>
