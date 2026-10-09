@php
    $periodeLabel = ($dari || $sampai)
        ? (($dari ? $dari->translatedFormat('d M Y') : 'Awal') . ' – ' . ($sampai ? $sampai->translatedFormat('d M Y') : 'Sekarang'))
        : 'Seluruh Riwayat';
    // 'dispensasi' = nama status lama (sebelum izin keluar jadi status
    // sendiri); labelnya disamakan karena maknanya sama persis.
    // 'izin_terlambat' tidak digabung ke 'izin' -- datang terlambat tetap
    // dihitung hadir setengah hari, bukan absen (lihat RekapKehadiran).
    $statusMap = [
        'hadir' => ['label' => 'Hadir', 'tone' => 'bg-hadir-soft text-hadir'],
        'sakit' => ['label' => 'Sakit', 'tone' => 'bg-sakit-soft text-sakit'],
        'izin' => ['label' => 'Izin', 'tone' => 'bg-izin-soft text-izin'],
        'izin_terlambat' => ['label' => 'Terlambat', 'tone' => 'bg-alpha-soft text-alpha'],
        'alpha' => ['label' => 'Alpha', 'tone' => 'bg-alpha-soft text-alpha'],
        'izin_keluar' => ['label' => 'Izin Keluar', 'tone' => 'bg-dispen-soft text-dispen'],
        'dispensasi' => ['label' => 'Izin Keluar', 'tone' => 'bg-dispen-soft text-dispen'],
    ];
    $ringkasanRekap = \App\Support\RekapKehadiran::ringkasan();
@endphp

<div class="flex flex-col gap-4 text-ink">
    {{-- Header Profil Siswa --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl bg-surface-alt/60 p-3.5 border border-surface-alt">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-navy text-xs font-bold text-card">
                    {{ $siswa->no_absen ?? '—' }}
                </span>
                <h4 class="text-base font-bold text-ink">{{ $siswa->nama }}</h4>
            </div>
            <p class="text-xs text-muted-2 mt-0.5">
                NIS: <span class="font-medium text-ink">{{ $siswa->nis ?? '—' }}</span>
                · Kelas: <span class="font-medium text-ink">{{ $siswa->kelas?->nama ?? '—' }}</span>
            </p>
        </div>
        <div class="text-xs font-medium text-muted-2 self-start sm:self-auto bg-card px-2.5 py-1 rounded-md border border-surface-alt">
            Periode: <span class="font-semibold text-ink">{{ $periodeLabel }}</span>
        </div>
    </div>

    {{-- Ringkasan Kehadiran --}}
    <div class="flex flex-col gap-2">
        <div>
            <p class="text-xs font-semibold text-muted-2 mb-1.5 flex items-center justify-between">
                <span>Rekap Kehadiran Harian ({{ $totalHari['total'] }} Hari Aktif)</span>
            </p>
            <div class="flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
                @foreach ($ringkasanRekap as $status => [$label, $nada])
                    <x-ui.stat :label="$label" :tone="$nada" :value="$totalHari[$status] ?? 0" />
                @endforeach
            </div>
        </div>

        <div>
            <p class="text-xs font-semibold text-muted-2 mb-1.5 flex items-center justify-between">
                <span>Rekap Jam Pelajaran / Mapel ({{ $totalMapel['total'] }} Pertemuan)</span>
            </p>
            <div class="flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
                @foreach ($ringkasanRekap as $status => [$label, $nada])
                    <x-ui.stat :label="$label" :tone="$nada" :value="$totalMapel[$status] ?? 0" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tab Pilihan: Rekap Per Mapel vs Riwayat Log --}}
    <div class="mt-2">
        <div class="flex border-b border-surface-alt gap-2 mb-3">
            <button type="button"
                id="btn-subtab-mapel"
                class="pb-2 text-xs font-bold border-b-2 transition-colors border-navy text-navy"
                onclick="document.getElementById('view-subtab-mapel').hidden = false; document.getElementById('view-subtab-riwayat').hidden = true; this.className = 'pb-2 text-xs font-bold border-b-2 transition-colors border-navy text-navy'; document.getElementById('btn-subtab-riwayat').className = 'pb-2 text-xs font-medium border-b-2 transition-colors border-transparent text-muted-2 hover:text-ink';">
                Rekap Per Mata Pelajaran ({{ $rekapPerMapel->count() }})
            </button>
            <button type="button"
                id="btn-subtab-riwayat"
                class="pb-2 text-xs font-medium border-b-2 transition-colors border-transparent text-muted-2 hover:text-ink"
                onclick="document.getElementById('view-subtab-mapel').hidden = true; document.getElementById('view-subtab-riwayat').hidden = false; this.className = 'pb-2 text-xs font-bold border-b-2 transition-colors border-navy text-navy'; document.getElementById('btn-subtab-mapel').className = 'pb-2 text-xs font-medium border-b-2 transition-colors border-transparent text-muted-2 hover:text-ink';">
                Riwayat Lengkap Pertemuan ({{ $riwayat->count() }})
            </button>
        </div>

        {{-- Sub-View 1: Rekap Per Mapel --}}
        <div id="view-subtab-mapel">
            @if ($rekapPerMapel->isEmpty())
                <div class="py-8 text-center text-xs text-muted-2 border border-dashed border-surface-alt rounded-xl">
                    Belum ada rekapan mata pelajaran pada periode ini.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-surface-alt">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-surface-alt text-muted-2 font-semibold border-b border-surface-alt">
                            <tr>
                                <th class="px-3 py-2">Mata Pelajaran</th>
                                <th class="px-2 py-2 text-center">Total</th>
                                @foreach ($ringkasanRekap as $label)
                                    <th class="px-2 py-2 text-center whitespace-nowrap">{{ $label[0] }}</th>
                                @endforeach
                                <th class="px-2 py-2 text-center">% Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-alt bg-card">
                            @foreach ($rekapPerMapel as $m)
                                <tr>
                                    <td class="px-3 py-2.5">
                                        <p class="font-bold text-ink">{{ $m['mapel'] }}</p>
                                        <p class="text-[11px] text-muted-2">Guru: {{ $m['guru'] }}</p>
                                    </td>
                                    <td class="px-2 py-2.5 text-center font-semibold text-muted">{{ $m['total'] }}</td>
                                    @foreach ($ringkasanRekap as $status => [$label, $nada])
                                        <td class="px-2 py-2.5 text-center"><x-ui.rekap-badge tone="{{ $status }}">{{ $m[$status] }}</x-ui.rekap-badge></td>
                                    @endforeach
                                    <td class="px-2 py-2.5 text-center font-bold {{ $m['persentase'] < 80 ? 'text-alpha' : 'text-hadir' }}">
                                        {{ $m['persentase'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Sub-View 2: Riwayat Log Lengkap --}}
        <div id="view-subtab-riwayat" hidden>
            @if ($riwayat->isEmpty())
                <div class="py-8 text-center text-xs text-muted-2 border border-dashed border-surface-alt rounded-xl">
                    Belum ada catatan jurnal pada periode ini.
                </div>
            @else
                <div class="flex flex-col gap-2 max-h-96 overflow-y-auto pr-1">
                    @foreach ($riwayat as $a)
                        @php
                            $j = $a->jurnal;
                            $cfg = $statusMap[$a->status] ?? ['label' => ucfirst($a->status), 'tone' => 'bg-surface-alt text-ink'];
                        @endphp
                        <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl border border-surface-alt bg-card text-xs">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-ink">
                                        {{ $j?->tanggal ? $j->tanggal->translatedFormat('l, d M Y') : '—' }}
                                    </span>
                                    <span class="rounded bg-surface-alt px-1.5 py-0.5 text-[10px] font-semibold text-muted">
                                        JP {{ $j?->jam_ke_mulai }}–{{ $j?->jam_ke_selesai }}
                                    </span>
                                </div>
                                <p class="text-xs text-ink font-medium mt-0.5">
                                    {{ $j?->jadwal?->mapel?->nama ?? 'Mata Pelajaran' }}
                                    <span class="text-muted-2 font-normal">· {{ $j?->guru?->nama ?? 'Guru' }}</span>
                                </p>
                                @if ($a->catatan)
                                    <p class="text-[11px] text-muted-2 mt-1 italic bg-surface-alt/40 px-2 py-1 rounded">
                                        "{{ $a->catatan }}"
                                    </p>
                                @endif
                            </div>
                            <div class="shrink-0">
                                {{-- Warna badge diambil dari $cfg (satu sumber
                                     statusMap) -- dulu match() dobel di sini,
                                     kalau ada status baru gampang kelewat. --}}
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $cfg['tone'] }}">
                                    {{ $cfg['label'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
