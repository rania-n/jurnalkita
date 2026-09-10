@php
    $tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
    $badgeAkhir = ['pending' => 'menunggu', 'approved' => 'disetujui', 'rejected' => 'ditolak'];
@endphp

<x-layouts.app title="Dispensasi" width="wide">
    <x-page-header title="Dispensasi Siswa" subtitle="Persetujuan izin keluar / tidak mengikuti pelajaran">
        @if ($bolehAjukan)
            <x-ui.button :href="route('dispensasi.create')" icon="add">Ajukan Dispensasi</x-ui.button>
        @endif
    </x-page-header>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('dispensasi.index', ['tab' => $key]) }}"
               @class(['shrink-0 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap', 'bg-navy text-card' => $tab === $key, 'text-muted-2 hover:text-ink' => $tab !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($items->isEmpty())
        <x-ui.empty icon="fact_check" title="Belum ada dispensasi" />
    @else
        <x-ui.card-list>
            @foreach ($items as $d)
                <x-ui.list-card
                    :title="$d->siswa->nama"
                    :meta="[
                        $d->siswa->kelas?->nama . ' · ' . $d->tanggal->translatedFormat('d M Y'),
                        ($d->jam_ke_mulai ? 'JP ' . $d->jam_ke_mulai . '–' . $d->jam_ke_selesai : 'Sehari penuh') . ' · ' . str($d->alasan)->limit(40),
                    ]"
                >
                    <x-slot:badge>
                        <x-ui.status-badge :status="$badgeAkhir[$d->status_akhir]">
                            {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$d->status_akhir] }}
                        </x-ui.status-badge>
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button label="Detail" icon="badge" :href="route('dispensasi.show', $d)" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
        <div class="mt-4">{{ $items->links() }}</div>
    @endif
</x-layouts.app>
