@php
    $statusLabel = ['pending' => 'menunggu', 'terverifikasi' => 'disetujui', 'revisi' => 'ditolak'];
    $tabs = ['semua' => 'Semua', 'pending' => 'Menunggu', 'terverifikasi' => 'Berhasil', 'revisi' => 'Perlu Revisi'];
@endphp

<x-layouts.app title="Riwayat Jurnal" width="wide">
    <x-page-header title="Riwayat Jurnal" subtitle="Jurnal mengajar yang sudah Anda isi">
        <x-ui.button :href="route('jurnal.create')" icon="add">Isi Jurnal</x-ui.button>
    </x-page-header>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('jurnal.index', $key === 'semua' ? [] : ['status' => $key]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $status === $key, 'text-muted-2 hover:text-ink' => $status !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

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
