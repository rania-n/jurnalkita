<x-layouts.admin title="Detail Guru" :heading="$guru->nama" :subtitle="$guru->mapelUtama?->nama ?? 'Belum ada mapel utama'">
    <x-admin.page :title="$guru->nama" :subtitle="$guru->mapelUtama?->nama ?? 'Belum ada mapel utama'" :back="route('master.guru.index')" />

    {{-- ── Info utama ──────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-ui.field-static label="NIP" icon="badge">{{ $guru->nip ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Mapel Utama" icon="menu_book">{{ $guru->mapelUtama?->nama ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Mapel Tambahan" icon="library_books">
            {{ $guru->mapels->pluck('nama')->join(', ') ?: '—' }}
        </x-ui.field-static>
    </div>

    {{-- ── Wali Kelas ──────────────────────────────────────────── --}}
    @if ($kelasWali->isNotEmpty())
        <h2 class="mb-2 text-sm font-bold text-ink">Wali Kelas</h2>
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach ($kelasWali as $k)
                <a href="{{ route('master.kelas.show', $k) }}"
                   class="flex items-center gap-1.5 rounded-lg bg-navy/10 px-3 py-1.5 text-sm font-semibold text-navy hover:bg-navy/20">
                    {{ $k->nama }}
                    @if ($k->status === 'pkl')
                        <x-ui.status-badge status="pkl" />
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    {{-- ── Jadwal Mengajar ─────────────────────────────────────── --}}
    <h2 class="mb-4 text-sm font-bold text-ink">Jadwal Mengajar Seminggu</h2>

    @if ($jadwalPerHari->isEmpty())
        <x-ui.empty title="Belum ada jadwal mengajar untuk guru ini" />
    @else
        <div class="flex flex-col gap-6">
            @foreach ($hariLabel as $key => $label)
                @continue (! $jadwalPerHari->has($key))
                <div class="overflow-hidden rounded-xl border border-surface-alt">
                    {{-- Header hari --}}
                    <div class="flex items-center gap-2 border-b border-surface-alt bg-surface px-4 py-2.5">
                        <span class="text-sm font-bold text-ink">{{ $label }}</span>
                        <span class="rounded-full bg-navy/10 px-2 py-0.5 text-xs font-semibold text-navy">
                            {{ $jadwalPerHari[$key]->count() }} sesi
                        </span>
                    </div>
                    {{-- Tabel per hari tanpa border luar (sudah dibungkus card) --}}
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-alt text-xs font-bold uppercase tracking-wide text-muted-2">
                                <th class="px-4 py-2.5">JP</th>
                                <th class="px-4 py-2.5">Mata Pelajaran</th>
                                <th class="px-4 py-2.5">Kelas</th>
                                <th class="px-4 py-2.5">Ruang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-alt bg-card">
                            @foreach ($jadwalPerHari[$key]->sortBy('jam_ke_mulai') as $j)
                                <tr class="hover:bg-surface/60">
                                    <td class="px-4 py-3 text-muted">JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</td>
                                    <td class="px-4 py-3 font-semibold text-ink">{{ $j->mapel->nama }}</td>
                                    <td class="px-4 py-3">
                                        @if ($j->kelas)
                                            <a href="{{ route('master.kelas.show', $j->kelas) }}"
                                               class="inline-flex items-center rounded-lg bg-navy/10 px-2.5 py-1 text-xs font-semibold text-navy hover:bg-navy/20">
                                                {{ $j->kelas->nama }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ $j->ruang ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
