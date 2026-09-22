<x-layouts.guest title="Registrasi Pengurus Kelas">
    <x-page-header
        title="Registrasi Pengurus Kelas"
        subtitle="Akses jurnal mandiri perwakilan kelas"
        :back="url()->previous(route('pilih_peran'))"
    />

    <x-alert type="warning" class="mb-6">
        Akun Anda akan diverifikasi oleh Admin sebelum dapat digunakan.
    </x-alert>

    <form method="POST" action="{{ route('register.kelas') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.select label="Kelas" name="kelas_id" required>
            <option value="" disabled selected hidden>Pilih Kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.input label="Nama Lengkap" name="nama" placeholder="Masukkan nama lengkap" :value="old('nama')" required />
        <x-ui.input label="NIS" name="nis" inputmode="numeric" placeholder="Masukkan Nomor Induk Siswa" :value="old('nis')" required />
        <x-ui.input label="Email" name="email" type="email" placeholder="Masukkan alamat email aktif" :value="old('email')" required />
        <x-ui.input label="No. WhatsApp" name="telepon" inputmode="numeric" placeholder="Masukkan nomor WhatsApp aktif" :value="old('telepon')" />

        <x-ui.input label="Password" name="password" type="password" id="pk-password" placeholder="Buat kata sandi baru" hint="Minimal 8 karakter." required />

        <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" id="pk-password-confirm" placeholder="Ulangi kata sandi" required />

        <x-ui.button type="submit" block>Daftar Akun Siswa</x-ui.button>
    </form>
</x-layouts.guest>
