@php
    $tone = [
        'hadir' => 'bg-hadir-soft text-hadir',
        'tugas' => 'bg-izin-soft text-izin',
        'tidak_hadir' => 'bg-alpha-soft text-alpha',
        'belum_diisi' => 'bg-sakit-soft text-sakit',
    ];
    $hariLabel = config('akademik.hari')[['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? ''] ?? null;
@endphp

<x-layouts.app title="Monitor Piket" width="wide">
    <x-page-header title="Monitor Piket" :subtitle="$hariLabel ? $hariLabel . ', ' . $tanggal->translatedFormat('d M Y') : $tanggal->translatedFormat('d M Y') . ' — akhir pekan, tidak ada jadwal pelajaran'">
        <x-ui.button :href="route('piket.monitor.ekspor', ['tanggal' => $tanggal->toDateString()])" variant="secondary" icon="download">Ekspor Ringkasan</x-ui.button>
    </x-page-header>

    {{-- Ganti tanggal --}}
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-surface-alt bg-card p-4">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <x-ui.input label="Tanggal" name="tanggal" type="date" :value="$tanggal->toDateString()" />
        <x-ui.button type="submit" icon="search">Tampilkan</x-ui.button>
    </form>

    {{-- Rekap total hari itu --}}
    <div class="mb-4 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
        <x-ui.stat label="Hadir" tone="hadir" :value="$rekapTotal['hadir'] ?? 0" />
        <x-ui.stat label="Tugas Luar" tone="izin" :value="$rekapTotal['tugas'] ?? 0" />
        <x-ui.stat label="Tidak Hadir" tone="alpha" :value="$rekapTotal['tidak_hadir'] ?? 0" />
        <x-ui.stat label="Belum Diisi" tone="sakit" :value="$rekapTotal['belum_diisi'] ?? 0" />
    </div>

    {{-- Bar pilih: kelompokkan per kelas atau per guru --}}
    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach (['kelas' => 'Per Kelas', 'guru' => 'Per Guru'] as $key => $label)
            <a href="{{ route('piket.monitor.index', ['tanggal' => $tanggal->toDateString(), 'mode' => $key]) }}"
               @class(['shrink-0 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap', 'bg-navy text-card' => $mode === $key, 'text-muted-2 hover:text-ink' => $mode !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($grup->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal pelajaran" desc="Tanggal ini akhir pekan, atau belum ada jadwal sama sekali." />
    @else
        <div class="flex flex-col gap-4">
            @foreach ($grup as $g)
                <div class="rounded-xl border border-surface-alt bg-card">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-surface-alt p-4">
                        <div>
                            <p class="font-bold text-ink">{{ $g['label'] }}</p>
                            <p class="text-xs text-muted">
                                {{ $g['rows']->count() }} jam pelajaran
                                @if ($g['rekap']['belum_diisi'] ?? 0)
                                    · <span class="font-semibold text-sakit">{{ $g['rekap']['belum_diisi'] }} belum diisi</span>
                                @endif
                            </p>
                        </div>
                        <x-ui.action-button
                            label="Ekspor Lengkap"
                            icon="download"
                            :href="route('piket.monitor.ekspor.detail', ['tipe' => $mode, 'id' => $g['id'], 'tanggal' => $tanggal->toDateString()])"
                        />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[32rem] text-left text-sm">
                            <thead>
                                <tr class="border-b border-surface-alt">
                                    <th class="px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-muted-2">Jam</th>
                                    <th class="px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-muted-2">Mapel</th>
                                    <th class="px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-muted-2">{{ $mode === 'kelas' ? 'Guru' : 'Kelas' }}</th>
                                    <th class="px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-muted-2">Status</th>
                                    <th class="px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-muted-2">Materi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-alt">
                                @foreach ($g['rows'] as $b)
                                    <tr @class(['bg-sakit-soft/30' => $b['status'] === 'belum_diisi'])>
                                        <td class="px-4 py-2.5 text-muted">JP {{ $b['jadwal']->jam_ke_mulai }}–{{ $b['jadwal']->jam_ke_selesai }}</td>
                                        <td class="px-4 py-2.5 text-ink">{{ $b['jadwal']->mapel->nama }}</td>
                                        <td class="px-4 py-2.5 text-muted">{{ $mode === 'kelas' ? $b['jadwal']->guru->nama : $b['jadwal']->kelas->nama }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold {{ $tone[$b['status']] }}">
                                                {{ $b['statusLabel'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-muted">{{ $b['jurnal']->materi ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
