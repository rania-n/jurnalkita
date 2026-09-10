<x-layouts.guest title="Registrasi Pengurus Kelas" :center="false">
    <x-page-header
        title="Registrasi Pengurus Kelas"
        subtitle="Akses jurnal mandiri perwakilan kelas"
        :back="route('pilih_peran')"
    />

    <x-alert type="warning" bleed class="mb-6">
        Akun Anda akan diverifikasi oleh Admin sebelum dapat digunakan.
    </x-alert>

    <form method="POST" action="{{ route('register_pengurus_kelas') }}" class="flex flex-col gap-4 pb-28">
        @csrf

        <x-ui.select label="Tingkat" name="tingkat">
            <option value="" disabled selected hidden>Pilih Tingkat (X, XI, XII)</option>
            <option value="X">X</option>
            <option value="XI">XI</option>
            <option value="XII">XII</option>
        </x-ui.select>

        <x-ui.select label="Jurusan" name="jurusan">
            <option value="" disabled selected hidden>Pilih Jurusan (e.g. Rekayasa Perangkat Lunak)</option>
            <option value="RPL">Rekayasa Perangkat Lunak</option>
            <option value="TKJ">Teknik Komputer dan Jaringan</option>
            <option value="MM">Multimedia</option>
        </x-ui.select>

        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Masukkan nama lengkap" :value="old('nama')" />
        <x-ui.input label="NIS" name="nis" inputmode="numeric" placeholder="Masukkan Nomor Induk Siswa" :value="old('nis')" />
        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan alamat email aktif" :value="old('email')" />
        <x-ui.input label="No. WhatsApp" name="telepon" inputmode="numeric" placeholder="Masukkan nomor WhatsApp aktif" :value="old('telepon')" />

        <x-ui.input label="Password" name="password" type="password" id="pk-password" placeholder="Buat kata sandi baru">
            <button type="button" data-toggle-password="#pk-password" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" id="pk-password-confirm" placeholder="Ulangi kata sandi">
            <button type="button" data-toggle-password="#pk-password-confirm" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan kata sandi">
                <x-icon name="visibility" :size="20" />
            </button>
        </x-ui.input>

        <x-ui.sticky-bar :above-nav="false">
            <x-ui.button type="submit" block>Daftar Akun Siswa</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.guest>
