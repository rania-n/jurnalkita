@php
    $tabs = ['' => 'Semua', 'pending' => "Perlu diperiksa ({$jumlahPending})", 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Diminta revisi'];
    $badge = ['pending' => 'pending', 'terverifikasi' => 'terverifikasi', 'revisi' => 'revisi'];
@endphp

<x-layouts.app title="Verifikasi Jurnal" width="wide">
    <x-page-header title="Jurnal Kelas {{ $kelas->nama }}" subtitle="Periksa materi & presensi yang diisi guru">
        <x-ui.button :href="route('sekretaris.jurnal.pengganti')" variant="secondary" icon="edit_note">Isi Jurnal Pengganti</x-ui.button>
    </x-page-header>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('sekretaris.jurnal.index', array_filter(['status' => $key])) }}"
               @class(['shrink-0 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap', 'bg-navy text-card' => $status === ($key ?: null), 'text-muted-2 hover:text-ink' => $status !== ($key ?: null)])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($jurnals->isEmpty())
        <x-ui.empty icon="menu_book" title="Tidak ada jurnal" />
    @else
        <x-ui.card-list>
            @foreach ($jurnals as $j)
                <x-ui.list-card
                    :title="$j->jadwal->mapel->nama"
                    :meta="[
                        $j->guru->nama . ($j->diisi_oleh_pengurus ? ' (diisi pengurus)' : ''),
                        $j->tanggal->translatedFormat('d M Y') . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai,
                    ]"
                >
                    <x-slot:badge>
                        <x-ui.status-badge :status="$badge[$j->status_verifikasi]">
                            {{ ['pending' => 'Perlu diperiksa', 'terverifikasi' => 'Terverifikasi', 'revisi' => 'Diminta revisi'][$j->status_verifikasi] }}
                        </x-ui.status-badge>
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button label="Periksa" icon="fact_check" variant="info" :href="route('sekretaris.jurnal.show', $j)" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
        <div class="mt-4">{{ $jurnals->links() }}</div>
    @endif
</x-layouts.app>
