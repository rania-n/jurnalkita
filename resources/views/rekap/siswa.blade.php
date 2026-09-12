@php
    $totalAlphaTinggi = $siswas->filter(fn ($s) => ($rekap[$s->id]['alpha'] ?? 0) >= $ambangAlpha)->count();
@endphp

<x-layouts.app title="Rekap Kehadiran Siswa" width="wide">
    <x-page-header title="Rekap Kehadiran Siswa" subtitle="Lintas kelas, buat evaluasi kedisiplinan">
        <x-ui.button :href="route('rekap.siswa.ekspor', request()->query())" variant="secondary" icon="download">Ekspor CSV</x-ui.button>
    </x-page-header>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-surface-alt bg-card p-4">
        <x-ui.input label="Dari tanggal" name="dari" type="date" :value="$dari->toDateString()" />
        <x-ui.input label="Sampai tanggal" name="sampai" type="date" :value="$sampai->toDateString()" />
        <x-ui.select label="Kelas" name="kelas_id" class="min-w-40">
            <option value="">Semua kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.button type="submit" icon="search">Tampilkan</x-ui.button>
    </form>

    @if ($totalAlphaTinggi > 0)
        <x-alert type="warning" class="mb-4">
            <strong>{{ $totalAlphaTinggi }} siswa</strong> alpha {{ $ambangAlpha }}x atau lebih pada rentang ini — perlu perhatian.
        </x-alert>
    @endif

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa" />
    @else
        <x-admin.table :head="['Kelas', 'No.', 'Nama', 'Hadir', 'Sakit', 'Izin', 'Alpha', 'Dispensasi']">
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
                </tr>
            @endforeach
        </x-admin.table>
        <p class="mt-3 text-xs text-muted-2">Baris merah muda = alpha {{ $ambangAlpha }}x atau lebih pada rentang tanggal ini.</p>
    @endif
</x-layouts.app>
