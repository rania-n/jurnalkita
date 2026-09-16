@php
    $tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'kadaluarsa' => 'Kadaluarsa', 'ditolak' => 'Ditolak'];
    // Admin lihat halaman ini lewat sidebar admin -- pakai shell admin (topbar,
    // sidebar) yang sama biar nggak berasa pindah ke "app lain". Guru piket & waka
    // tetap pakai shell mobile mereka sendiri.
    $admin = auth()->user()->role === 'admin';
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Dispensasi" heading="Dispensasi Siswa" width="wide">
    @if ($admin)
        <x-admin.page title="Dispensasi Siswa" subtitle="Persetujuan izin keluar / tidak mengikuti pelajaran">
            <x-slot:action>
                @if ($bolehEkspor)
                    <x-ui.button :href="route('dispensasi.ekspor', request()->query())" variant="secondary" icon="download" class="w-full sm:w-auto">Ekspor CSV</x-ui.button>
                @endif
                @if ($bolehAjukan)
                    <x-ui.button :href="route('dispensasi.create')" icon="add" class="w-full sm:w-auto">Ajukan Dispensasi</x-ui.button>
                @endif
            </x-slot:action>
        </x-admin.page>
    @else
        <x-page-header title="Dispensasi Siswa" subtitle="Persetujuan izin keluar / tidak mengikuti pelajaran">
            {{-- HP: numpuk penuh biar teksnya nggak sampe kepotong 2 baris pas
                 berdempetan. Mulai sm: baru sejajar seperlunya. --}}
            <div class="flex flex-col gap-2 sm:flex-row">
                @if ($bolehEkspor)
                    <x-ui.button :href="route('dispensasi.ekspor', request()->query())" variant="secondary" icon="download" class="w-full sm:w-auto">Ekspor CSV</x-ui.button>
                @endif
                @if ($bolehAjukan)
                    <x-ui.button :href="route('dispensasi.create')" icon="add" class="w-full sm:w-auto">Ajukan Dispensasi</x-ui.button>
                @endif
            </div>
        </x-page-header>
    @endif

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('dispensasi.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap', 'bg-navy text-card' => $tab === $key, 'text-muted-2 hover:text-ink' => $tab !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-admin.filters :action="route('dispensasi.index')">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <x-admin.f-search placeholder="Nama atau NIS siswa..." />
        <x-admin.f-select name="kelas_id" label="Kelas" :options="$kelasList->pluck('nama', 'id')" all="Semua Kelas" />
        <x-admin.f-date name="dari" label="Dari tanggal" />
        <x-admin.f-date name="sampai" label="Sampai tanggal" />
    </x-admin.filters>

    @if ($items->isEmpty())
        <x-ui.empty icon="fact_check" title="Belum ada dispensasi" desc="Coba ubah filter kalau sedang mencari data tertentu." />
    @else
        <x-ui.card-list>
            @foreach ($items as $d)
                <x-ui.list-card
                    :title="$d->siswa->nama"
                    :meta="[
                        $d->siswa->kelas?->nama . ' · ' . $d->labelTanggal(),
                        $d->labelJam() . ' · ' . str($d->alasan)->limit(40),
                        $d->surat_path ? '📎 Ada bukti terlampir' : 'Tanpa bukti',
                    ]"
                >
                    <x-slot:badge>
                        @if ($d->sudahKadaluarsa())
                            <x-ui.status-badge status="kadaluarsa">Kadaluarsa</x-ui.status-badge>
                        @else
                            <x-ui.status-badge :status="['pending' => 'menunggu', 'approved' => 'disetujui', 'rejected' => 'ditolak'][$d->status_akhir]">
                                {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$d->status_akhir] }}
                            </x-ui.status-badge>
                        @endif
                    </x-slot:badge>
                    <x-slot:actions>
                        <x-ui.action-button label="Detail" icon="badge" :href="route('dispensasi.show', $d)" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
        <div class="mt-4">{{ $items->links() }}</div>
    @endif
</x-dynamic-component>
