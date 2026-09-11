@php
    $tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
    $badgeAkhir = ['pending' => 'menunggu', 'approved' => 'disetujui', 'rejected' => 'ditolak'];
    $adaFilter = request()->filled('dari') || request()->filled('sampai') || request()->filled('guru_id') || request()->filled('kelas_id');
@endphp

<x-layouts.app title="Dispensasi" width="wide">
    <x-page-header title="Dispensasi Siswa" subtitle="Persetujuan izin keluar / tidak mengikuti pelajaran">
        <div class="flex gap-2">
            @if ($bolehEkspor)
                <x-ui.button :href="route('dispensasi.ekspor', request()->query())" variant="secondary" icon="download">Ekspor CSV</x-ui.button>
            @endif
            @if ($bolehAjukan)
                <x-ui.button :href="route('dispensasi.create')" icon="add">Ajukan Dispensasi</x-ui.button>
            @endif
        </div>
    </x-page-header>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('dispensasi.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
               @class(['shrink-0 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap', 'bg-navy text-card' => $tab === $key, 'text-muted-2 hover:text-ink' => $tab !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Filter — buat laporan piket (per hari/guru/kelas), tertutup secara default --}}
    <details class="mb-4 rounded-xl border border-surface-alt bg-card" @if ($adaFilter) open @endif>
        <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-ink">
            <span class="inline-flex items-center gap-1.5">
                <x-icon name="filter_alt" :size="18" class="text-muted" />
                Filter laporan
                @if ($adaFilter)<span class="rounded-full bg-navy px-1.5 py-0.5 text-[10px] text-card">aktif</span>@endif
            </span>
        </summary>

        <form method="GET" class="grid grid-cols-1 gap-3 border-t border-surface-alt p-4 sm:grid-cols-2 lg:grid-cols-4">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <x-ui.input label="Dari tanggal" name="dari" type="date" :value="request('dari')" />
            <x-ui.input label="Sampai tanggal" name="sampai" type="date" :value="request('sampai')" />

            @if ($guruPiketList->isNotEmpty())
                <x-ui.select label="Guru piket" name="guru_id">
                    <option value="">Semua guru piket</option>
                    @foreach ($guruPiketList as $g)
                        <option value="{{ $g->id }}" @selected(request('guru_id') == $g->id)>{{ $g->name }}</option>
                    @endforeach
                </x-ui.select>
            @endif

            <x-ui.select label="Kelas" name="kelas_id">
                <option value="">Semua kelas</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </x-ui.select>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                <x-ui.button type="submit" icon="filter_alt">Terapkan</x-ui.button>
                @if ($adaFilter)
                    <x-ui.button :href="route('dispensasi.index', ['tab' => $tab])" variant="secondary" icon="close">Bersihkan</x-ui.button>
                @endif
            </div>
        </form>
    </details>

    @if ($items->isEmpty())
        <x-ui.empty icon="fact_check" title="Belum ada dispensasi" desc="Coba ubah filter kalau sedang mencari data tertentu." />
    @else
        <x-ui.card-list>
            @foreach ($items as $d)
                <x-ui.list-card
                    :title="$d->siswa->nama"
                    :meta="[
                        $d->siswa->kelas?->nama . ' · ' . $d->tanggal->translatedFormat('d M Y'),
                        ($d->jam_ke_mulai ? 'JP ' . $d->jam_ke_mulai . '–' . $d->jam_ke_selesai : 'Sehari penuh') . ' · ' . str($d->alasan)->limit(40),
                        $d->surat_path ? '📎 Ada bukti terlampir' : 'Tanpa bukti',
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
