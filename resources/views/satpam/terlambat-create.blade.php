<x-layouts.app title="Catat Siswa Terlambat">
    <x-page-header
        title="Catat Siswa Terlambat"
        subtitle="Siswa telat masuk gerbang pagi"
        :back="route('satpam.dashboard')"
    />

    <form method="POST" action="{{ route('satpam.terlambat.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-ui.cari-siswa name="siswa_id" :siswas="$siswaList" class="sm:col-span-2" required />

            <x-ui.input label="Jam Datang" name="jam_datang" type="time" :value="old('jam_datang', now()->format('H:i'))" required />

            <x-ui.input label="Catatan (opsional)" name="catatan" :value="old('catatan')" placeholder="Contoh: ban bocor" />
        </div>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
