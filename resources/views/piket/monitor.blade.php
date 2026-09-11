@php
    $tone = [
        'hadir' => 'bg-hadir-soft text-hadir',
        'tugas' => 'bg-izin-soft text-izin',
        'tidak_hadir' => 'bg-alpha-soft text-alpha',
        'belum_diisi' => 'bg-sakit-soft text-sakit',
    ];
    $rekap = $baris->countBy('status');
    $adaFilter = request()->filled('kelas_id') || request()->filled('guru_id');
    $hariLabel = config('akademik.hari')[['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? ''] ?? null;
@endphp

<x-layouts.app title="Monitor Piket" width="wide">
    <x-page-header title="Monitor Piket" :subtitle="$hariLabel ? $hariLabel . ', ' . $tanggal->translatedFormat('d M Y') : $tanggal->translatedFormat('d M Y') . ' — akhir pekan, tidak ada jadwal pelajaran'">
        <x-ui.button :href="route('piket.monitor.ekspor', request()->query())" variant="secondary" icon="download">Ekspor CSV</x-ui.button>
    </x-page-header>

    {{-- Ganti tanggal + filter kelas/guru --}}
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-surface-alt bg-card p-4">
        <x-ui.input label="Tanggal" name="tanggal" type="date" :value="$tanggal->toDateString()" />

        <x-ui.select label="Kelas" name="kelas_id" class="min-w-40">
            <option value="">Semua kelas</option>
            @foreach ($kelasList as $k)
                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.select label="Guru" name="guru_id" class="min-w-40">
            <option value="">Semua guru</option>
            @foreach ($guruList as $g)
                <option value="{{ $g->id }}" @selected(request('guru_id') == $g->id)>{{ $g->nama }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.button type="submit" icon="search">Tampilkan</x-ui.button>
        @if ($adaFilter)
            <x-ui.button :href="route('piket.monitor.index', ['tanggal' => $tanggal->toDateString()])" variant="secondary" icon="close">Bersihkan</x-ui.button>
        @endif
    </form>

    {{-- Rekap --}}
    <div class="mb-4 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
        <x-ui.stat label="Hadir" tone="hadir" :value="$rekap['hadir'] ?? 0" />
        <x-ui.stat label="Tugas Luar" tone="izin" :value="$rekap['tugas'] ?? 0" />
        <x-ui.stat label="Tidak Hadir" tone="alpha" :value="$rekap['tidak_hadir'] ?? 0" />
        <x-ui.stat label="Belum Diisi" tone="sakit" :value="$rekap['belum_diisi'] ?? 0" />
    </div>

    @if ($baris->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal pelajaran" desc="Tanggal ini akhir pekan, atau belum ada jadwal yang cocok dengan filter." />
    @else
        <x-admin.table :head="['Kelas', 'Jam', 'Mata Pelajaran', 'Guru', 'Status', 'Materi']">
            @foreach ($baris as $b)
                <tr @class(['bg-sakit-soft/30' => $b['status'] === 'belum_diisi'])>
                    <td class="px-4 py-2.5 font-semibold text-ink">{{ $b['jadwal']->kelas->nama }}</td>
                    <td class="px-4 py-2.5 text-muted">JP {{ $b['jadwal']->jam_ke_mulai }}–{{ $b['jadwal']->jam_ke_selesai }}</td>
                    <td class="px-4 py-2.5 text-muted">{{ $b['jadwal']->mapel->nama }}</td>
                    <td class="px-4 py-2.5 text-ink">{{ $b['jadwal']->guru->nama }}</td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold {{ $tone[$b['status']] }}">
                            {{ $b['statusLabel'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-muted">{{ $b['jurnal']->materi ?? '—' }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.app>
