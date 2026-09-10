@php
    $statusLabel = ['pending' => 'menunggu', 'terverifikasi' => 'disetujui', 'revisi' => 'ditolak'];
@endphp

<x-layouts.app title="Riwayat Jurnal" width="wide">
    <x-page-header title="Riwayat Jurnal" subtitle="Jurnal mengajar yang sudah Anda isi">
        <x-ui.button :href="route('jurnal.create')" icon="add">Isi Jurnal</x-ui.button>
    </x-page-header>

    @if ($jurnals->isEmpty())
        <x-ui.empty icon="menu_book" title="Belum ada jurnal" desc="Mulai isi jurnal dari beranda atau tombol di atas." />
    @else
        <x-ui.card-list>
            @foreach ($jurnals as $j)
                <x-ui.list-card
                    :title="$j->jadwal->mapel->nama . ' — ' . $j->jadwal->kelas->nama"
                    :meta="[$j->tanggal->translatedFormat('d M Y') . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai]"
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

        <div class="mt-4">{{ $jurnals->links() }}</div>
    @endif
</x-layouts.app>
