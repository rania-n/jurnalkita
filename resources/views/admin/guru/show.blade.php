<x-layouts.admin title="Detail Guru" :heading="$guru->nama">
    <x-admin.page :title="$guru->nama" :subtitle="$guru->mapelUtama?->nama ?? 'Belum ada mapel utama'" :back="route('master.guru.index')" />

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-ui.field-static label="NIP" icon="badge">{{ $guru->nip ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Mapel Utama" icon="menu_book">{{ $guru->mapelUtama?->nama ?: '—' }}</x-ui.field-static>
        <x-ui.field-static label="Mapel Tambahan" icon="library_books">{{ $guru->mapels->pluck('nama')->join(', ') ?: '—' }}</x-ui.field-static>
    </div>

    @if ($kelasWali->isNotEmpty())
        <h2 class="mb-2 text-sm font-bold text-ink">Wali Kelas</h2>
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach ($kelasWali as $k)
                <a href="{{ route('master.kelas.show', $k) }}" class="flex items-center gap-1.5 rounded-lg bg-navy/10 px-3 py-1.5 text-sm font-semibold text-navy hover:bg-navy/20">
                    {{ $k->nama }}
                    @if ($k->status === 'pkl')
                        <x-ui.status-badge status="pkl" />
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <h2 class="mb-2 text-sm font-bold text-ink">Jadwal Mengajar Seminggu</h2>
    @if ($jadwalPerHari->isEmpty())
        <x-ui.empty title="Belum ada jadwal mengajar untuk guru ini" />
    @else
        <div class="flex flex-col gap-4">
            @foreach ($hariLabel as $key => $label)
                @continue (! $jadwalPerHari->has($key))
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-muted-2">{{ $label }}</p>
                    <x-admin.table :head="['JP', 'Mata Pelajaran', 'Kelas', 'Ruang']">
                        @foreach ($jadwalPerHari[$key]->sortBy('jam_ke_mulai') as $j)
                            <tr>
                                <td class="px-4 py-2.5 text-muted">JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</td>
                                <td class="px-4 py-2.5 font-semibold text-ink">{{ $j->mapel->nama }}</td>
                                <td class="px-4 py-2.5 text-muted">
                                    @if ($j->kelas)
                                        <a href="{{ route('master.kelas.show', $j->kelas) }}" class="text-navy hover:underline">{{ $j->kelas->nama }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-muted">{{ $j->ruang ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </x-admin.table>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
