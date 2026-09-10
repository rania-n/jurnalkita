<x-layouts.guest title="Registrasi Guru" :center="false">
    <x-page-header
        title="Registrasi Guru"
        subtitle="Lengkapi biodata pengajar Anda"
        :back="route('pilih_peran')"
    />

    <x-alert type="warning" bleed class="mb-6">
        Akun Anda akan diverifikasi oleh Admin sebelum dapat digunakan.
    </x-alert>

    <form method="POST" action="{{ route('register_guru') }}" class="flex flex-col gap-4 pb-28">
        @csrf

        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Masukkan nama lengkap guru" :value="old('nama')" />
        <x-ui.input label="NIK" name="nik" inputmode="numeric" placeholder="Masukkan Nomor Induk Kependudukan" :value="old('nik')" />
        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan alamat email aktif" :value="old('email')" />
        <x-ui.input label="No. Telepon" name="telepon" inputmode="numeric" placeholder="Masukkan nomor WhatsApp aktif" :value="old('telepon')" />

        <x-ui.select label="Mata Pelajaran Utama" name="mapel">
            <option value="" disabled selected hidden>Pilih Mata Pelajaran</option>
            <option value="matematika">Matematika</option>
            <option value="bahasa_indonesia">Bahasa Indonesia</option>
            <option value="rpl">Kejuruan RPL</option>
        </x-ui.select>

        <x-ui.input label="Password" name="password" type="password" id="reg-password" placeholder="Buat kata sandi baru">
            <button type="button" data-toggle-password="#reg-password" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" id="reg-password-confirm" placeholder="Ulangi kata sandi">
            <button type="button" data-toggle-password="#reg-password-confirm" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <x-ui.sticky-bar :above-nav="false">
            <x-ui.button type="submit" block>Daftar Akun Guru</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.guest>
