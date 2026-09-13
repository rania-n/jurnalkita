@php
    // Label rapi buat kategori bawaan; kategori custom (buatan admin) tampil apa adanya
    // (huruf besar tiap kata) — lihat App\Http\Controllers\Admin\JamPelajaranController.
    $labelBawaan = ['senin_kamis' => 'Senin–Kamis', 'jumat' => 'Jumat', 'khusus' => 'Khusus'];
    $urutanBawaan = array_keys($labelBawaan);

    $semuaKategori = \App\Models\JamPelajaran::select('kategori')->distinct()->pluck('kategori');
    // Kategori bawaan duluan (urutan tetap), baru custom (alfabetis) -- konsisten tiap dibuka.
    $kategoriList = $semuaKategori
        ->sortBy(fn ($k) => [array_search($k, $urutanBawaan) === false ? 1 : 0, array_search($k, $urutanBawaan) ?: $k])
        ->values();

    $set = request('set', $kategoriList->first() ?? 'senin_kamis');
    $labelSet = $labelBawaan[$set] ?? \Illuminate\Support\Str::headline($set);
    $rows = \App\Models\JamPelajaran::where('kategori', $set)->orderBy('jam_ke')->get();
    $bisaHapusKategori = ! in_array($set, $urutanBawaan, true) && $rows->isNotEmpty();
@endphp

<x-layouts.admin title="Jam Pelajaran" heading="Jam Pelajaran">
    <x-admin.page title="Jam Pelajaran" subtitle="Rentang waktu tiap jam pelajaran">
        <x-slot:action>
            <x-ui.button type="button" variant="secondary" icon="add" data-modal-open="modal-jp-baru" data-modal-title="Tambah Kategori Baru">Kategori Baru</x-ui.button>
            <x-ui.button type="button" icon="edit" data-modal-open="modal-jp" data-modal-title="Edit Jam Pelajaran — {{ $labelSet }}">Edit</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($kategoriList as $key)
            <a href="{{ route('master.jam-pelajaran.index', ['set' => $key]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $set === $key, 'text-muted-2 hover:text-ink' => $set !== $key])>
                {{ $labelBawaan[$key] ?? \Illuminate\Support\Str::headline($key) }}
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

    @if ($bisaHapusKategori)
        <form method="POST" action="{{ route('master.jam-pelajaran.destroy-kategori', $set) }}" class="mt-3"
              data-confirm="Hapus kategori &quot;{{ $labelSet }}&quot; beserta semua jamnya?">
            @csrf @method('DELETE')
            <x-ui.button type="submit" variant="danger" icon="delete">Hapus Kategori Ini</x-ui.button>
        </form>
    @endif

    {{-- Edit kategori yang lagi dipilih --}}
    <x-admin.modal id="modal-jp" size="lg" title="Edit Jam Pelajaran">
        <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" id="form-jp">
            @csrf
            <input type="hidden" name="kategori" value="{{ $set }}">

            <div class="hidden gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2 sm:flex">
                <span class="w-8 shrink-0">JP</span><span class="w-28 shrink-0">Mulai</span><span class="w-28 shrink-0">Selesai</span><span class="flex-1">Keterangan</span><span class="w-8 shrink-0"></span>
            </div>

            <div data-jp-rows class="max-h-[45vh] overflow-y-auto">
                @forelse ($rows as $jp)
                    <div class="flex flex-wrap items-center gap-2 border-b border-surface-alt/60 py-2 last:border-0" data-jp-row>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-sm font-bold text-ink" data-jp-no>{{ $loop->iteration }}</span>
                        <input type="time" name="mulai[]" value="{{ $jp->mulai?->format('H:i') }}" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                        <input type="time" name="selesai[]" value="{{ $jp->selesai?->format('H:i') }}" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                        <input type="text" name="keterangan[]" value="{{ $jp->keterangan }}" placeholder="Opsional" class="h-9 min-w-[8rem] flex-1 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                        <button type="button" data-jp-remove class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-alpha-soft text-alpha hover:bg-[#fecdd3]" aria-label="Hapus baris">
                            <x-icon name="delete" :size="16" />
                        </button>
                    </div>
                @empty
                    <div class="flex flex-wrap items-center gap-2 border-b border-surface-alt/60 py-2 last:border-0" data-jp-row>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-sm font-bold text-ink" data-jp-no>1</span>
                        <input type="time" name="mulai[]" value="07:00" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                        <input type="time" name="selesai[]" value="07:45" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                        <input type="text" name="keterangan[]" placeholder="Opsional" class="h-9 min-w-[8rem] flex-1 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                        <button type="button" data-jp-remove class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-alpha-soft text-alpha" aria-label="Hapus baris">
                            <x-icon name="delete" :size="16" />
                        </button>
                    </div>
                @endforelse
            </div>

            <button type="button" data-jp-add class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-sm font-bold text-izin hover:bg-[#bae6fd]">
                <x-icon name="add" :size="18" /> Tambah Baris
            </button>

            <div class="mt-4 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan Perubahan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    {{-- Kategori baru — sama persis, cuma nama kategorinya diketik manual --}}
    <x-admin.modal id="modal-jp-baru" size="lg" title="Tambah Kategori Baru">
        <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" id="form-jp-baru">
            @csrf
            <x-ui.input label="Nama Kategori" name="kategori_baru" placeholder="Contoh: Ramadhan, Ujian" class="mb-3" />

            <div class="hidden gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2 sm:flex">
                <span class="w-8 shrink-0">JP</span><span class="w-28 shrink-0">Mulai</span><span class="w-28 shrink-0">Selesai</span><span class="flex-1">Keterangan</span><span class="w-8 shrink-0"></span>
            </div>

            <div data-jp-rows class="max-h-[45vh] overflow-y-auto">
                <div class="flex flex-wrap items-center gap-2 border-b border-surface-alt/60 py-2 last:border-0" data-jp-row>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-sm font-bold text-ink" data-jp-no>1</span>
                    <input type="time" name="mulai[]" value="07:00" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                    <input type="time" name="selesai[]" value="07:45" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                    <input type="text" name="keterangan[]" placeholder="Opsional" class="h-9 min-w-[8rem] flex-1 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
                    <button type="button" data-jp-remove class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-alpha-soft text-alpha" aria-label="Hapus baris">
                        <x-icon name="delete" :size="16" />
                    </button>
                </div>
            </div>

            <button type="button" data-jp-add class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-sm font-bold text-izin hover:bg-[#bae6fd]">
                <x-icon name="add" :size="18" /> Tambah Baris
            </button>

            <div class="mt-4 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan Kategori</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            // Dipakai bareng buat kedua form (edit kategori & kategori baru) -- data-jp-*
            // scoped ke masing-masing form, jadi aman dipasang di keduanya.
            document.querySelectorAll('#form-jp, #form-jp-baru').forEach((jpForm) => {
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
            });
        </script>
    @endpush
</x-layouts.admin>
