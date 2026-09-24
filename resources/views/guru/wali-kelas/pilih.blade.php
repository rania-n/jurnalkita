<x-layouts.app title="Wali Kelas">
    {{-- Judul size="sm" -- dikecilin (bukan dihilangin) biar halaman tetap
         ada kop. --}}
    <x-page-header title="Wali Kelas" subtitle="Anda wali di beberapa kelas — pilih dulu" size="sm" />

    <x-ui.card-list class="grid-fill-last">
        @foreach ($kelasList as $k)
            <x-ui.list-card
                :title="$k->nama"
                :meta="[$k->siswas()->count() . ' siswa']"
            >
                <x-slot:actions>
                    <x-ui.action-button label="Rekap" icon="bar_chart" :href="route('guru.wali-kelas.rekap', $k)" />
                </x-slot:actions>
            </x-ui.list-card>
        @endforeach
    </x-ui.card-list>
</x-layouts.app>
