@php
    $set = request('set', 'senin_kamis');
    $sets = ['senin_kamis' => 'Senin–Kamis', 'jumat' => 'Jumat', 'khusus' => 'Khusus'];
    $rows = \App\Models\JamPelajaran::where('kategori', $set)->orderBy('jam_ke')->get();
@endphp

<x-layouts.admin title="Edit Jam Pelajaran" heading="Edit Jam Pelajaran">
    <x-admin.page title="Edit Jam Pelajaran — {{ $sets[$set] ?? $set }}" :back="route('master.jam-pelajaran.index', ['set' => $set])" />

    <form method="POST" action="{{ route('master.store', 'jam-pelajaran') }}" class="max-w-2xl">
        @csrf
        <div class="rounded-xl border border-surface-alt bg-card p-5">
            <div class="grid grid-cols-[3rem_1fr_1fr_2.5rem] gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2">
                <span>JP</span><span>Mulai</span><span>Selesai</span><span></span>
            </div>
            @forelse ($rows as $i => $jp)
                <div class="grid grid-cols-[3rem_1fr_1fr_2.5rem] items-center gap-2 py-2">
                    <span class="text-sm font-bold text-ink">{{ $jp->jam_ke }}</span>
                    <input type="time" name="mulai[{{ $i }}]" value="{{ $jp->mulai?->format('H:i') }}" class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                    <input type="time" name="selesai[{{ $i }}]" value="{{ $jp->selesai?->format('H:i') }}" class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-alpha-soft text-alpha" data-confirm="Hapus baris ini?" aria-label="Hapus">
                        <x-icon name="delete" :size="16" />
                    </button>
                </div>
            @empty
                <p class="py-4 text-sm text-muted">Belum ada baris. Tambahkan di bawah.</p>
            @endforelse

            <button type="button" class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-sm font-bold text-izin">
                <x-icon name="add" :size="18" /> Tambah Baris
            </button>
        </div>

        <div class="mt-4">
            <x-ui.button type="submit" icon="save">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
