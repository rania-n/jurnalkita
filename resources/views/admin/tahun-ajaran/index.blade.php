@php
    $aktif = \App\Models\TahunAjaran::aktif();

    $kelasAktif = \App\Models\Kelas::aktif()
        ->withCount(['siswas' => fn ($q) => $q->where('status', 'aktif')])
        ->get();

    $perTingkat = $kelasAktif->groupBy('tingkat')->map(fn ($grup) => [
        'kelas' => $grup->count(),
        'siswa' => $grup->sum('siswas_count'),
    ]);

    $siswaLulusTotal = \App\Models\Siswa::where('status', 'lulus')->count();

    // Saran nama tahun ajaran baru: "2026/2027" -> "2027/2028".
    $saranNama = '';
    if ($aktif && preg_match('/^(\d{4})\/(\d{4})$/', $aktif->nama, $m)) {
        $saranNama = (((int) $m[1]) + 1).'/'.(((int) $m[2]) + 1);
    }

    $riwayat = \App\Models\TahunAjaran::withCount('kelas')->orderByDesc('id')->get();
@endphp

<x-layouts.admin title="Tahun Ajaran" heading="Tahun Ajaran">
    <x-admin.page title="Tahun Ajaran & Kenaikan Kelas" subtitle="Tahun ajaran aktif: {{ $aktif?->nama ?? '— belum diset —' }}" />

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach (['X', 'XI', 'XII'] as $tingkat)
            <div class="rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <p class="text-xs font-semibold tracking-wide text-muted uppercase">Tingkat {{ $tingkat }}</p>
                <p class="mt-1 text-2xl font-bold text-ink">{{ $perTingkat[$tingkat]['kelas'] ?? 0 }} kelas</p>
                <p class="text-sm text-muted">{{ $perTingkat[$tingkat]['siswa'] ?? 0 }} siswa aktif</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl bg-card p-5 shadow-[var(--shadow-soft)]">
            <h3 class="text-base font-bold text-ink">Naikkan Kelas</h3>
            <p class="mt-1 text-sm text-muted">
                Kelas <strong>X</strong> naik jadi <strong>XI</strong>, <strong>XI</strong> naik jadi <strong>XII</strong>
                (kelas baru otomatis dibuat, siswa aktif dipindah). Siswa <strong>XII</strong> ditandai
                <strong>lulus</strong>. Kelas &amp; siswa tahun lama tidak dihapus — tetap jadi arsip, tetap
                bisa dilihat, riwayat jurnal/absensinya tidak berubah.
            </p>

            <form method="POST" action="{{ route('master.tahun-ajaran.naik-kelas') }}" class="mt-4 flex flex-col gap-3"
                  data-confirm="Yakin naikkan kelas ke tahun ajaran baru? Proses ini TIDAK BISA dibatalkan.">
                @csrf
                <x-ui.input label="Nama Tahun Ajaran Baru" name="nama" :value="old('nama', $saranNama)" placeholder="mis. 2027/2028" />
                <x-ui.button type="submit" icon="event_repeat">Naikkan Kelas &amp; Mulai Tahun Ajaran Baru</x-ui.button>
            </form>
        </div>

        <div>
            <h3 class="mb-3 text-base font-bold text-ink">Riwayat Tahun Ajaran</h3>
            @if ($riwayat->isEmpty())
                <x-ui.empty title="Belum ada tahun ajaran" />
            @else
                <x-admin.table :head="['Tahun Ajaran', 'Status', 'Jml Kelas']">
                    @foreach ($riwayat as $ta)
                        <tr class="hover:bg-surface/60">
                            <td class="px-4 py-3 font-semibold text-ink">{{ $ta->nama }}</td>
                            <td class="px-4 py-3">
                                @if ($ta->aktif)
                                    <x-ui.status-badge status="hadir">Aktif</x-ui.status-badge>
                                @else
                                    <span class="text-muted">Arsip</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted">{{ $ta->kelas_count }}</td>
                        </tr>
                    @endforeach
                </x-admin.table>
            @endif
            @if ($siswaLulusTotal > 0)
                <p class="mt-3 text-sm text-muted">Total siswa lulus (semua angkatan): {{ $siswaLulusTotal }}.</p>
            @endif
        </div>
    </div>
</x-layouts.admin>
