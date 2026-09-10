<x-layouts.app title="Form Pengajuan Dispensasi">
    <x-page-header
        title="Form Pengajuan Dispensasi"
        subtitle="Ajukan dispensasi siswa"
        :back="route('dispensasi.index')"
    />

    <form method="POST" action="{{ route('dispensasi.store') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.select label="Kelas" name="kelas">
            <option value="" disabled selected hidden>Pilih Kelas</option>
            <option value="x-rpl-1">X RPL 1</option>
            <option value="xi-rpl-2">XI RPL 2</option>
        </x-ui.select>

        <x-ui.select label="Nama Siswa" name="siswa">
            <option value="" disabled selected hidden>Pilih Siswa</option>
            <option value="1">Ahmad Fauzi</option>
            <option value="2">Dewi Lestari</option>
        </x-ui.select>

        <x-ui.input label="No. Telepon" name="telepon" inputmode="numeric" placeholder="Masukkan nomor WhatsApp aktif" />

        <x-ui.input label="Tanggal" name="tanggal" type="date" icon="calendar_month" :value="now()->format('Y-m-d')" />

        <x-ui.textarea label="Alasan Dispensasi / Lomba / Kegiatan" name="alasan" :rows="4"
            placeholder="Contoh: Mengikuti Lomba Informatika" />

        <x-ui.upload
            label="Surat Dispensasi / Izin"
            name="surat"
            accept="image/*,application/pdf"
            title="Lampirkan Foto / File Bukti Pendukung"
            hint="Foto / file bukti pendukung dispensasi"
        />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="send">Kirim Dispensasi</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
