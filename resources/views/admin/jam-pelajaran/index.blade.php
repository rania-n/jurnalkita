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
    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
    $hariTerpakai = \Illuminate\Support\Facades\DB::table('jam_pelajaran_hari')->where('kategori', $set)->pluck('hari')->all();
    $labelHariTerpakai = collect($hariLabel)->only($hariTerpakai)->values()->implode(', ');
    $adaJadwalSebelumnya = \Illuminate\Support\Facades\DB::table('jam_pelajaran_snapshots')->where('kategori', $set)->exists();
@endphp

<x-layouts.admin title="Jam Pelajaran" heading="Jam Pelajaran">
    <x-admin.page title="Jam Pelajaran" subtitle="Rentang waktu tiap jam pelajaran">
        <x-slot:action>
            <x-ui.button type="button" variant="secondary" icon="add" data-modal-open="modal-jp-baru" data-modal-title="Tambah Kategori Baru" class="w-full sm:w-auto">Kategori Baru</x-ui.button>
            <x-ui.button type="button" variant="secondary" icon="auto_awesome" data-modal-open="modal-jp-generate" data-modal-title="Buat Jadwal Otomatis" class="w-full sm:w-auto">Buat Otomatis</x-ui.button>
            <x-ui.button type="button" icon="edit" data-modal-open="modal-jp" data-modal-title="Edit Jam Pelajaran — {{ $labelSet }}" class="w-full sm:w-auto">Edit</x-ui.button>
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

    <section class="mb-4 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)] sm:p-5">
        <h2 class="text-base font-bold text-ink">Gunakan Kategori Ini</h2>
        <p class="mt-1 text-sm text-muted">
            Default otomatis: Senin–Kamis memakai kategori Senin–Kamis dan Jumat memakai kategori Jumat.
            @if ($labelHariTerpakai !== '') Kategori {{ $labelSet }} sekarang dipakai untuk {{ $labelHariTerpakai }}. @else Kategori {{ $labelSet }} belum dipakai di hari sekolah. @endif
        </p>
        <form method="POST" action="{{ route('master.jam-pelajaran.kategori-hari') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <input type="hidden" name="set" value="{{ $set }}">
            <input type="hidden" name="kategori" value="{{ $set }}">
            <x-ui.select name="kelompok_hari" label="Gunakan untuk" required class="sm:max-w-xs">
                <option value="senin_kamis" @selected(old('kelompok_hari', 'senin_kamis') === 'senin_kamis')>Senin–Kamis</option>
                <option value="jumat" @selected(old('kelompok_hari') === 'jumat')>Jumat</option>
            </x-ui.select>
            <x-ui.button type="submit" icon="save" class="w-full sm:w-auto">Gunakan JP Ini</x-ui.button>
        </form>
    </section>

    <section class="mb-4 grid gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)] sm:grid-cols-2 sm:p-5">
        <div>
            <h2 class="text-base font-bold text-ink">Majukan {{ $labelSet }} per JP</h2>
            <p class="mt-1 text-sm text-muted">Nomor JP dan jadwal kelas ikut bergeser. Contoh: JP2 menjadi JP1, dan slot nomor terakhir tidak dipakai. Reset bisa mengembalikan keduanya.</p>
            <form method="POST" action="{{ route('master.jam-pelajaran.maju') }}" class="mt-3 flex flex-wrap items-end gap-2" data-confirm="Majukan seluruh jadwal kategori {{ $labelSet }}?">
                @csrf
                <input type="hidden" name="kategori" value="{{ $set }}">
                <x-ui.input label="Jumlah JP" name="jumlah_jp" type="number" min="1" max="5" value="1" required />
                <x-ui.button type="submit" icon="schedule">Majukan JP</x-ui.button>
            </form>
        </div>
        <div class="rounded-xl border border-surface-alt p-3">
            <h2 class="text-base font-bold text-ink">Reset ke jadwal sebelumnya</h2>
            <p class="mt-1 text-sm text-muted">Memulihkan salinan jadwal sebelum perubahan terakhir untuk kategori ini.</p>
            <form method="POST" action="{{ route('master.jam-pelajaran.reset') }}" class="mt-3" data-confirm="Pulihkan {{ $labelSet }} ke salinan jadwal sebelumnya? Perubahan saat ini akan diganti.">
                @csrf
                <input type="hidden" name="kategori" value="{{ $set }}">
                <x-ui.button type="submit" variant="secondary" icon="restart_alt" :disabled="! $adaJadwalSebelumnya">Reset Jadwal</x-ui.button>
                @unless ($adaJadwalSebelumnya)
                    <p class="mt-2 text-xs text-muted">Belum ada salinan sebelumnya.</p>
                @endunless
            </form>
        </div>
    </section>

    @if ($errors->any())
        <x-alert type="error" class="mb-4">{{ $errors->first() }}</x-alert>
    @endif

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

    <x-admin.modal id="modal-jp-generate" size="lg" title="Buat Jadwal Otomatis">
        <form method="POST" action="{{ route('master.jam-pelajaran.generate') }}" class="flex flex-col gap-4" data-confirm="Ganti seluruh jadwal {{ $labelSet }} dengan jadwal otomatis yang baru?">
            @csrf
            <input type="hidden" name="kategori" value="{{ $set }}">
            <x-alert type="info">
                Isi JP berurutan dengan durasi standar 40 menit. Tambahkan jeda istirahat atau MBG di antara JP; waktu JP berikutnya otomatis bergeser setelah jeda.
            </x-alert>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <x-ui.input label="JP pertama mulai" name="mulai" type="time" :value="old('mulai', $rows->first()?->mulai?->format('H:i') ?? '07:00')" required />
                <x-ui.input label="Durasi tiap JP (menit)" name="durasi_jp" type="number" min="20" max="120" :value="old('durasi_jp', 40)" required />
                <x-ui.input label="Jumlah JP" name="jumlah_jp" type="number" min="1" max="20" :value="old('jumlah_jp', $rows->count() ?: 10)" required />
            </div>

            <div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-ink">Jeda istirahat atau MBG</h4>
                        <p class="mt-1 text-xs text-muted">Opsional. Tentukan jeda setelah JP tertentu, lalu isi durasinya.</p>
                    </div>
                    <button type="button" data-jeda-add class="inline-flex h-9 shrink-0 items-center gap-1 rounded-lg bg-izin-soft px-3 text-sm font-bold text-izin">
                        <x-icon name="add" :size="16" /> Tambah Jeda
                    </button>
                </div>
                <div data-jeda-rows class="mt-3 flex flex-col gap-2">
                    @foreach (old('jeda', []) as $jedaIndex => $jedaLama)
                        <div data-jeda-row class="grid grid-cols-1 items-end gap-2 rounded-xl border border-surface-alt p-3 sm:grid-cols-[1.2fr_1fr_1.2fr_auto]">
                            <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Jeda setelah
                                <select name="jeda[{{ $jedaIndex }}][setelah]" required class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                                    <option value="">Pilih JP</option>
                                    @for ($nomorJp = 1; $nomorJp < (int) old('jumlah_jp', 20); $nomorJp++)
                                        <option value="{{ $nomorJp }}" @selected((int) ($jedaLama['setelah'] ?? 0) === $nomorJp)>Setelah JP {{ $nomorJp }}</option>
                                    @endfor
                                </select>
                            </label>
                            <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Durasi (menit)
                                <input type="number" name="jeda[{{ $jedaIndex }}][durasi]" min="1" max="180" value="{{ $jedaLama['durasi'] ?? 15 }}" required class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                            </label>
                            <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Nama jeda (opsional)
                                <input type="text" name="jeda[{{ $jedaIndex }}][label]" maxlength="40" value="{{ $jedaLama['label'] ?? '' }}" placeholder="Istirahat / MBG" class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                            </label>
                            <button type="button" data-jeda-remove class="flex h-10 items-center justify-center rounded-lg bg-alpha-soft px-3 text-sm font-bold text-alpha">Hapus</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" icon="auto_awesome" class="flex-1">Buat Jadwal</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    {{-- Edit kategori yang lagi dipilih --}}
    <x-admin.modal id="modal-jp" size="lg" title="Edit Jam Pelajaran" errorBag="jpEdit">
        <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" id="form-jp">
            @csrf
            <input type="hidden" name="kategori" value="{{ $set }}">

            <div class="hidden gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2 sm:flex">
                <span class="w-8 shrink-0">JP</span><span class="w-28 shrink-0">Mulai <span class="text-alpha">*</span></span><span class="w-28 shrink-0">Selesai <span class="text-alpha">*</span></span><span class="flex-1">Keterangan</span><span class="w-8 shrink-0"></span>
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
    <x-admin.modal id="modal-jp-baru" size="lg" title="Tambah Kategori Baru" errorBag="jpBaru">
        <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" id="form-jp-baru">
            @csrf
            <x-ui.input label="Nama Kategori" name="kategori_baru" placeholder="Contoh: Ramadhan, Ujian" class="mb-3" errorBag="jpBaru" required />

            <div class="hidden gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2 sm:flex">
                <span class="w-8 shrink-0">JP</span><span class="w-28 shrink-0">Mulai <span class="text-alpha">*</span></span><span class="w-28 shrink-0">Selesai <span class="text-alpha">*</span></span><span class="flex-1">Keterangan</span><span class="w-8 shrink-0"></span>
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

            const jedaRows = document.querySelector('[data-jeda-rows]');
            const jedaAdd = document.querySelector('[data-jeda-add]');
            let jedaIndex = jedaRows?.querySelectorAll('[data-jeda-row]').length || 0;
            jedaAdd?.addEventListener('click', () => {
                if (!jedaRows || jedaRows.querySelectorAll('[data-jeda-row]').length >= 10) return;

                const row = document.createElement('div');
                row.dataset.jedaRow = '';
                row.className = 'grid grid-cols-1 items-end gap-2 rounded-xl border border-surface-alt p-3 sm:grid-cols-[1.2fr_1fr_1.2fr_auto]';

                const opsiSetelah = Array.from({ length: 19 }, (_, i) => `<option value="${i + 1}">Setelah JP ${i + 1}</option>`).join('');
                row.innerHTML = `
                    <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Jeda setelah
                        <select name="jeda[${jedaIndex}][setelah]" required class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                            <option value="">Pilih JP</option>${opsiSetelah}
                        </select>
                    </label>
                    <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Durasi (menit)
                        <input type="number" name="jeda[${jedaIndex}][durasi]" min="1" max="180" value="15" required class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                    </label>
                    <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Nama jeda (opsional)
                        <input type="text" name="jeda[${jedaIndex}][label]" maxlength="40" placeholder="Istirahat / MBG" class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                    </label>
                    <button type="button" data-jeda-remove class="flex h-10 items-center justify-center rounded-lg bg-alpha-soft px-3 text-sm font-bold text-alpha">Hapus</button>
                `;
                jedaRows.append(row);
                jedaIndex++;
            });

            jedaRows?.addEventListener('click', (event) => {
                if (event.target.closest('[data-jeda-remove]')) event.target.closest('[data-jeda-row]')?.remove();
            });
        </script>
    @endpush
</x-layouts.admin>
