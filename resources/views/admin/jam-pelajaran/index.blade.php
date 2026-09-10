@php
    $set = request('set', 'senin_kamis');
    $sets = ['senin_kamis' => 'Senin–Kamis', 'jumat' => 'Jumat', 'khusus' => 'Khusus'];
    $rows = \App\Models\JamPelajaran::where('kategori', $set)->orderBy('jam_ke')->get();
@endphp

<x-layouts.admin title="Jam Pelajaran" heading="Jam Pelajaran">
    <x-admin.page title="Jam Pelajaran" subtitle="Rentang waktu tiap jam pelajaran">
        <x-slot:action>
            <x-ui.button type="button" icon="edit" data-modal-open="modal-jp" data-modal-title="Edit Jam Pelajaran — {{ $sets[$set] ?? $set }}">Edit</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($sets as $key => $label)
            <a href="{{ route('master.jam-pelajaran.index', ['set' => $key]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold transition-colors', 'bg-navy text-card' => $set === $key, 'text-muted-2 hover:text-ink' => $set !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada konfigurasi jam untuk kategori ini" />
    @else
        <x-admin.table :head="['Jam ke-', 'Mulai', 'Selesai', 'Keterangan']">
            @foreach ($rows as $jp)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $jp->jam_ke }}</td>
                    <td class="px-4 py-3 text-muted">{{ $jp->mulai?->format('H:i') }}</td>
                    <td class="px-4 py-3 text-muted">{{ $jp->selesai?->format('H:i') }}</td>
                    <td class="px-4 py-3 text-muted">{{ $jp->keterangan ?: '—' }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-jp" size="lg" title="Edit Jam Pelajaran">
        <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" id="form-jp">
            @csrf
            <input type="hidden" name="kategori" value="{{ $set }}">

            <div class="grid grid-cols-[2.5rem_1fr_1fr_2.25rem] gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2">
                <span>JP</span><span>Mulai</span><span>Selesai</span><span></span>
            </div>

            <div data-jp-rows class="max-h-[45vh] overflow-y-auto">
                @forelse ($rows as $jp)
                    <div class="grid grid-cols-[2.5rem_1fr_1fr_2.25rem] items-center gap-2 py-1.5" data-jp-row>
                        <span class="text-sm font-bold text-ink" data-jp-no>{{ $loop->iteration }}</span>
                        <input type="time" name="mulai[]" value="{{ $jp->mulai?->format('H:i') }}" required class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                        <input type="time" name="selesai[]" value="{{ $jp->selesai?->format('H:i') }}" required class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                        <button type="button" data-jp-remove class="flex h-8 w-8 items-center justify-center rounded-lg bg-alpha-soft text-alpha hover:bg-[#fecdd3]" aria-label="Hapus baris">
                            <x-icon name="delete" :size="16" />
                        </button>
                    </div>
                @empty
                    <div class="grid grid-cols-[2.5rem_1fr_1fr_2.25rem] items-center gap-2 py-1.5" data-jp-row>
                        <span class="text-sm font-bold text-ink" data-jp-no>1</span>
                        <input type="time" name="mulai[]" value="07:00" required class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                        <input type="time" name="selesai[]" value="07:45" required class="rounded-lg border border-surface-alt bg-card px-2 py-2 text-sm outline-none focus:border-navy">
                        <button type="button" data-jp-remove class="flex h-8 w-8 items-center justify-center rounded-lg bg-alpha-soft text-alpha" aria-label="Hapus baris">
                            <x-icon name="delete" :size="16" />
                        </button>
                    </div>
                @endforelse
            </div>

            <button type="button" data-jp-add class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-sm font-bold text-izin hover:bg-[#bae6fd]">
                <x-icon name="add" :size="18" /> Tambah Baris
            </button>

            <div class="mt-4 flex gap-2">
                <x-ui.button type="submit" icon="save">Simpan Perubahan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            const jpForm = document.getElementById('form-jp');
            const jpRows = jpForm.querySelector('[data-jp-rows]');
            const jpRenumber = () => jpRows.querySelectorAll('[data-jp-no]').forEach((el, i) => (el.textContent = i + 1));

            jpForm.querySelector('[data-jp-add]').addEventListener('click', () => {
                const clone = jpRows.querySelector('[data-jp-row]')?.cloneNode(true);
                if (!clone) return;
                clone.querySelectorAll('input').forEach((i) => (i.value = ''));
                jpRows.appendChild(clone);
                jpRenumber();
            });

            jpRows.addEventListener('click', (e) => {
                if (!e.target.closest('[data-jp-remove]')) return;
                if (jpRows.querySelectorAll('[data-jp-row]').length <= 1) return;
                if (!confirm('Hapus baris jam pelajaran ini?')) return;
                e.target.closest('[data-jp-row]').remove();
                jpRenumber();
            });
        </script>
    @endpush
</x-layouts.admin>
