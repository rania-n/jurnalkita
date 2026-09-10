@php
    $set = request('set', 'senin_kamis');
    $sets = ['senin_kamis' => 'Senin–Kamis', 'jumat' => 'Jumat', 'khusus' => 'Khusus'];
    $rows = \App\Models\JamPelajaran::where('kategori', $set)->orderBy('jam_ke')->get();
@endphp

<x-layouts.admin title="Edit Jam Pelajaran" heading="Edit Jam Pelajaran">
    <x-admin.page title="Edit Jam Pelajaran — {{ $sets[$set] ?? $set }}" :back="route('master.jam-pelajaran.index', ['set' => $set])" />

    <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" class="max-w-2xl" id="form-jp">
        @csrf
        <input type="hidden" name="kategori" value="{{ $set }}">

        <div class="rounded-xl border border-surface-alt bg-card p-5">
            <div class="grid grid-cols-[3rem_1fr_1fr_2.5rem] gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2">
                <span>JP</span><span>Mulai</span><span>Selesai</span><span></span>
            </div>

            <div data-jp-rows>
                @foreach ($rows as $jp)
                    <div class="grid grid-cols-[3rem_1fr_1fr_2.5rem] items-center gap-2 py-2" data-jp-row>
                        <span class="text-sm font-bold text-ink" data-jp-no>{{ $loop->iteration }}</span>
                        <input type="time" name="mulai[]" value="{{ $jp->mulai?->format('H:i') }}" required class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                        <input type="time" name="selesai[]" value="{{ $jp->selesai?->format('H:i') }}" required class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                        <button type="button" data-jp-remove class="flex h-8 w-8 items-center justify-center rounded-lg bg-alpha-soft text-alpha hover:bg-[#fecdd3]" aria-label="Hapus baris">
                            <x-icon name="delete" :size="16" />
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" data-jp-add class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-sm font-bold text-izin hover:bg-[#bae6fd]">
                <x-icon name="add" :size="18" /> Tambah Baris
            </button>
        </div>

        <div class="mt-4">
            <x-ui.button type="submit" icon="save">Simpan Perubahan</x-ui.button>
        </div>
    </form>

    @push('scripts')
        <script>
            const form = document.getElementById('form-jp');
            const rows = form.querySelector('[data-jp-rows]');
            const renumber = () => rows.querySelectorAll('[data-jp-no]').forEach((el, i) => (el.textContent = i + 1));

            form.querySelector('[data-jp-add]').addEventListener('click', () => {
                const first = rows.querySelector('[data-jp-row]');
                const clone = first
                    ? first.cloneNode(true)
                    : null;
                if (!clone) return;
                clone.querySelectorAll('input').forEach((i) => (i.value = ''));
                rows.appendChild(clone);
                renumber();
            });

            rows.addEventListener('click', (e) => {
                if (!e.target.closest('[data-jp-remove]')) return;
                if (rows.querySelectorAll('[data-jp-row]').length <= 1) return;
                if (!confirm('Hapus baris jam pelajaran ini?')) return;
                e.target.closest('[data-jp-row]').remove();
                renumber();
            });
        </script>
    @endpush
</x-layouts.admin>
