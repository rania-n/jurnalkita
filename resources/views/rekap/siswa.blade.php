@php
    $totalAlphaTinggi = $siswas->filter(fn ($s) => ($rekap[$s->id]['alpha'] ?? 0) >= $ambangAlpha)->count();
    $admin = auth()->user()->role === 'admin';
@endphp

<x-dynamic-component :component="$admin ? 'layouts.admin' : 'layouts.app'" title="Rekap Kehadiran Siswa" heading="Rekap Kehadiran Siswa" width="wide">
    @if ($admin)
        <x-admin.page title="Rekap Kehadiran Siswa" subtitle="Lintas kelas, buat evaluasi kedisiplinan">
            <x-slot:action>
                <x-ui.button :href="route('rekap.siswa.ekspor', request()->query())" variant="secondary" icon="download">Ekspor CSV</x-ui.button>
            </x-slot:action>
        </x-admin.page>
    @else
        <x-page-header title="Rekap Kehadiran Siswa" subtitle="Lintas kelas, buat evaluasi kedisiplinan">
            <x-ui.button :href="route('rekap.siswa.ekspor', request()->query())" variant="secondary" icon="download">Ekspor CSV</x-ui.button>
        </x-page-header>
    @endif

    <x-admin.filters :action="route('rekap.siswa.index')">
        <x-admin.f-search placeholder="Nama atau NIS siswa..." />
        <x-admin.f-select name="kelas_id" label="Kelas" :options="$kelasList->pluck('nama', 'id')" all="Semua kelas" />
        <x-admin.f-date name="dari" label="Dari tanggal" :value="$dari->toDateString()" />
        <x-admin.f-date name="sampai" label="Sampai tanggal" :value="$sampai->toDateString()" />
    </x-admin.filters>

    @if ($totalAlphaTinggi > 0)
        <x-alert type="warning" class="mb-4">
            <strong>{{ $totalAlphaTinggi }} siswa</strong> alpha {{ $ambangAlpha }}x atau lebih pada rentang ini — perlu perhatian.
        </x-alert>
    @endif

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa" />
    @else
        <x-admin.table :head="['Kelas', 'No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi', 'Terlambat']">
            @foreach ($siswas as $s)
                @php $r = $rekap[$s->id] ?? collect(); $alphaTinggi = ($r['alpha'] ?? 0) >= $ambangAlpha; @endphp
                <tr @class(['bg-alpha-soft/30' => $alphaTinggi])>
                    <td class="px-4 py-2.5 text-muted">{{ $s->kelas?->nama ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                    <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                    <td class="px-4 py-2.5 text-hadir">{{ $r['hadir'] ?? 0 }}</td>
                    <td class="px-4 py-2.5 text-sakit">{{ $r['sakit'] ?? 0 }}</td>
                    <td class="px-4 py-2.5 text-izin">{{ $r['izin'] ?? 0 }}</td>
                    <td class="px-4 py-2.5 font-bold {{ $alphaTinggi ? 'text-alpha' : 'text-alpha/70' }}">{{ $r['alpha'] ?? 0 }}</td>
                    <td class="px-4 py-2.5 text-dispen">{{ $r['dispensasi'] ?? 0 }}</td>
                    <td class="px-4 py-2.5 font-semibold text-navy">{{ $terlambat[$s->id] ?? 0 }}</td>
                </tr>
            @endforeach
        </x-admin.table>
        <p class="mt-3 text-xs text-muted-2">Baris merah muda = alpha {{ $ambangAlpha }}x atau lebih pada rentang tanggal ini.</p>
    @endif
</x-dynamic-component>
