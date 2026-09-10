<x-layouts.app title="Tambah Mata Pelajaran">
    <x-page-header title="Tambah Mata Pelajaran" subtitle="Kelola mata pelajaran yang tersedia" :back="route('master.mapel.index')" />

    <form method="POST" action="{{ route('master.store', 'mapel') }}" class="flex flex-col gap-4">
        @csrf

        <x-ui.input label="Mata Pelajaran" name="nama" placeholder="Contoh: Bahasa Indonesia" />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="add">Tambah Mata Pelajaran</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
