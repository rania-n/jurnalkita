@php
    $statusLabel = ['pending' => 'menunggu', 'terverifikasi' => 'disetujui', 'revisi' => 'ditolak'];
    $tabs = ['semua' => 'Semua', 'pending' => 'Menunggu', 'terverifikasi' => 'Berhasil', 'revisi' => 'Perlu Revisi'];
@endphp

<x-layouts.app title="Riwayat Jurnal" width="wide">
    <x-page-header title="Riwayat Jurnal" subtitle="Jurnal mengajar yang sudah Anda isi">
        <x-ui.button :href="route('jurnal.create')" icon="add">Isi Jurnal</x-ui.button>
    </x-page-header>

    <div class="mb-2 flex gap-1 overflow-x-auto rounded-lg border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('jurnal.index', $key === 'semua' ? [] : ['status' => $key]) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $status === $key, 'text-muted-2 hover:text-ink' => $status !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Cari mapel/kelas -- langsung filter baris yang sudah dimuat di halaman
         ini (tanpa reload), sama kayak pola di Monitor Piket/Rekap. --}}
    <div class="mb-4">
        <x-ui.search-bar id="cari-riwayat-jurnal" placeholder="Cari mata pelajaran atau kelas..." />
    </div>

    @if ($jurnals->isEmpty())
        <x-ui.empty icon="menu_book" title="Belum ada jurnal" desc="Mulai isi jurnal dari beranda atau tombol di atas." />
    @else
        <x-ui.card-list id="daftar-riwayat-jurnal">
            @foreach ($jurnals as $j)
                @php
                    $jamJurnal = \App\Support\Waktu::rentangJam($j->jam_ke_mulai, $j->jam_ke_selesai, $j->tanggal);
                    $judul = $j->jadwal->mapel->nama . ' — ' . $j->jadwal->kelas->nama;
                @endphp
                <x-ui.list-card
                    data-baris-riwayat-jurnal
                    data-cari="{{ strtolower($judul) }}"
                    :title="$judul"
                    :meta="[$j->tanggal->translatedFormat('d M Y') . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai . ($jamJurnal ? ' (' . $jamJurnal . ')' : '')]"
                >
                    <x-slot:badge>
                        <x-ui.status-badge :status="$statusLabel[$j->status_verifikasi] ?? 'menunggu'">
                            {{ ['pending' => 'Menunggu verifikasi', 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Perlu revisi'][$j->status_verifikasi] }}
                        </x-ui.status-badge>
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button label="Lihat" icon="visibility" :href="route('jurnal.show', $j)" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
        <p id="riwayat-jurnal-kosong" hidden class="rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada jurnal yang cocok dengan pencarian.
        </p>

        <div class="mt-4">{{ $jurnals->links() }}</div>
    @endif

    @push('scripts')
        <script>
            (function () {
                const cari = document.getElementById('cari-riwayat-jurnal');
                const rows = document.querySelectorAll('[data-baris-riwayat-jurnal]');
                const kosong = document.getElementById('riwayat-jurnal-kosong');
                if (!cari) return;

                cari.addEventListener('input', () => {
                    const q = cari.value.trim().toLowerCase();
                    let ada = false;
                    rows.forEach((row) => {
                        const cocok = !q || row.dataset.cari.includes(q);
                        row.hidden = !cocok;
                        if (cocok) ada = true;
                    });
                    if (kosong) kosong.hidden = ada;
                });
            })();
        </script>
    @endpush
</x-layouts.app>
