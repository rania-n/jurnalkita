<x-layouts.app title="Tambah Data Guru">
    <x-page-header
        title="Tambah Data Guru"
        subtitle="Tambah akun guru sebagai staff piket"
        :back="route('piket.guru.index')"
    />

    <form method="POST" action="{{ route('piket.guru.index') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Masukkan nama lengkap guru" />
        <x-ui.input label="NIK" name="nik" inputmode="numeric" placeholder="Masukkan Nomor Induk Kependudukan" />
        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan alamat email aktif" />
        <x-ui.input label="No. Telepon" name="telepon" inputmode="numeric" placeholder="Masukkan nomor WhatsApp aktif" />

        <x-ui.select label="Mata Pelajaran Utama" name="mapel">
            <option value="" disabled selected hidden>Pilih Mata Pelajaran</option>
            <option value="matematika">Matematika</option>
            <option value="bahasa_inggris">Bahasa Inggris</option>
        </x-ui.select>

        <x-ui.input label="Password" name="password" type="password" id="guru-password" placeholder="Buat kata sandi baru">
            <button type="button" data-toggle-password="#guru-password" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" id="guru-password-confirm" placeholder="Ulangi kata sandi">
            <button type="button" data-toggle-password="#guru-password-confirm" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="add">Tambah Guru</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
