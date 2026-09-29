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
    // Buat nunjukin di tiap checkbox hari: "hari ini kepake kategori lain
    // yang mana" (kalau ada) -- biar admin sadar centang hari ini bakal
    // MINDAHIN hari itu dari kategori lain, bukan cuma nambah.
    $kategoriPerHari = \Illuminate\Support\Facades\DB::table('jam_pelajaran_hari')->pluck('kategori', 'hari');
    $adaJadwalSebelumnya = \Illuminate\Support\Facades\DB::table('jam_pelajaran_snapshots')->where('kategori', $set)->exists();

    // Kategori vs "dipakai buat hari apa" itu 2 konsep beda yang gampang
    // ketuker (kategori yang lagi DIBUKA/diedit belum tentu kategori yang
    // BENERAN aktif dipakai hari sekolah) -- badge kecil di tab ini nunjukin
    // langsung dari daftar tab-nya, tanpa perlu buka satu-satu.
    $hariPerKategori = \Illuminate\Support\Facades\DB::table('jam_pelajaran_hari')
        ->whereIn('kategori', $kategoriList)
        ->get()
        ->groupBy('kategori')
        ->map(fn ($baris) => collect($hariLabel)->only($baris->pluck('hari'))->values()->implode(', '));

    // Reopen modal abis gagal validasi -> tebak mode yang bener dari field
    // mana yang error (save() pakai "mulai.0" array, generate() pakai "mulai"
    // tunggal) -- biar admin nggak balik ke mode yang beda dari yang tadi
    // disubmit, errornya jadi kelihatan lagi di tempat yang tepat.
    $modeAwal = is_array(old('mulai')) ? 'manual' : ($errors->getBag('jp')->has('mulai.0') ? 'manual' : 'otomatis');

    // Opsi "Jeda setelah JP..." pas render server (baris re-tampil abis gagal
    // validasi) -- JS (syncOpsiJeda) yang ngurus nyesuain ulang pas Jumlah JP
    // diketik ganti-ganti di browser, ini cuma buat render awal.
    $jumlahJpAwal = max(1, (int) old('jumlah_jp', 20));
    $opsiSetelahJp = collect(range(1, $jumlahJpAwal - 1))->map(fn ($n) => ['id' => $n, 'nama' => "JP {$n}"]);

    $jumlahJpManualAwal = max(1, $rows->count() ?: 10);
    $opsiSetelahJpManual = collect(range(1, max(1, $jumlahJpManualAwal - 1)))->map(fn ($n) => ['id' => $n, 'nama' => "JP {$n}"]);

    $jedaOtomatisAwal = (! is_array(old('mulai')) && old('jeda')) ? old('jeda') : [];
    $jedaManualAwal = [];
    if (is_array(old('mulai')) && old('jeda')) {
        $jedaManualAwal = old('jeda');
    } else {
        foreach ($rows as $jp) {
            if ($jp->jeda_sebelum_menit && $jp->jam_ke > 1) {
                $jedaManualAwal[] = [
                    'setelah' => $jp->jam_ke - 1,
                    'durasi' => $jp->jeda_sebelum_menit,
                    'label' => $jp->jeda_label ?? '',
                ];
            }
        }
    }
@endphp

<x-layouts.admin title="Jam Pelajaran" heading="Jam Pelajaran" subtitle="Rentang waktu tiap jam pelajaran">
    <x-admin.page title="Jam Pelajaran" subtitle="Rentang waktu tiap jam pelajaran">
        <x-slot:action>
            <x-ui.button type="button" variant="secondary" icon="add" data-jp-trigger data-jp-baru="1"
                data-modal-open="modal-jp" data-modal-title="Tambah Kategori Baru" class="w-full sm:w-auto">
                Kategori Baru
            </x-ui.button>
            <x-ui.button type="button" icon="edit" data-jp-trigger data-jp-nama="{{ $labelSet }}"
                data-modal-open="modal-jp" data-modal-title="Jam Pelajaran — {{ $labelSet }}" class="w-full sm:w-auto">
                Edit {{ $labelSet }}
            </x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($kategoriList as $key)
            <a href="{{ route('master.jam-pelajaran.index', ['set' => $key]) }}"
               @class(['flex flex-1 flex-col items-center rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $set === $key, 'text-muted-2 hover:text-ink' => $set !== $key])>
                {{ $labelBawaan[$key] ?? \Illuminate\Support\Str::headline($key) }}
                {{-- Badge "dipakai buat hari apa" -- supaya kelihatan langsung dari
                     tab-nya, kategori mana yang BENERAN aktif, tanpa harus buka
                     satu-satu & baca bagian di bawah. --}}
                @if ($hariPerKategori->get($key))
                    <span @class(['block text-[10px] font-normal', 'text-card/80' => $set === $key, 'text-muted-2' => $set !== $key])>
                        {{ $hariPerKategori->get($key) }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Satu kartu buat SEMUA "urusan operasional" kategori yang lagi dibuka
         (dipakai hari apa, majukan, reset) -- dulu 2 kartu terpisah kesannya
         beda topik padahal sama-sama "hal yang bisa dilakukan ke kategori
         ini", bukan "isi jam pelajarannya sendiri" (itu di modal terpisah). --}}
    <section class="mb-4 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)] sm:p-5">
        <h2 class="text-base font-bold text-ink">Pengaturan {{ $labelSet }}</h2>

        <div class="mt-3">
            <p class="text-sm font-semibold text-ink">Dipakai untuk hari</p>
            <p class="mt-0.5 text-xs text-muted">Pilih hari sekolah yang menggunakan kategori {{ $labelSet }} ini -- dapat dicentang secara bebas (misalnya hanya hari Rabu, atau seluruh hari). Setiap hari hanya dapat menggunakan 1 kategori; apabila hari yang dicentang sedang digunakan oleh kategori lain, hari tersebut akan otomatis berpindah ke kategori ini.</p>

            {{-- Dulu bar 2 pilihan (Senin-Kamis / Jumat) yang auto-submit --
                 ternyata di sekolah ini hari nggak selalu ngelompok rapi kayak
                 gitu (ada kategori yang dipakai 1 hari doang, ada yang semua
                 hari). Sekarang checkbox per hari asli, dari tabel
                 jam_pelajaran_hari yang emang udah per-hari, bukan dipaksa
                 jadi 2 kelompok. Butuh tombol Simpan (nggak auto-submit lagi)
                 karena bisa centang lebih dari 1 -- ini malah HILANGIN JS
                 (dulu ada listener auto-submit, sekarang form biasa). --}}
            <form method="POST" action="{{ route('master.jam-pelajaran.kategori-hari') }}" class="mt-2">
                @csrf
                <input type="hidden" name="set" value="{{ $set }}">
                <input type="hidden" name="kategori" value="{{ $set }}">

                <div class="flex flex-wrap gap-1.5">
                    @foreach ($hariLabel as $key => $label)
                        @php
                            $kategoriLain = $kategoriPerHari->get($key);
                            $dipakaiKategoriLain = $kategoriLain && $kategoriLain !== $set;
                        @endphp
                        <label class="grow basis-24 cursor-pointer select-none">
                            <input type="checkbox" name="hari[]" value="{{ $key }}"
                                @checked(in_array($key, $hariTerpakai, true))
                                class="peer sr-only">
                            {{-- Warna teks anak (nama hari + subtext) SENGAJA nggak
                                 dikasih kelas warna sendiri -- biar ikut warisan
                                 warna dari span luar ini pas peer-checked (utility
                                 peer-checked cuma nembak elemen yg LANGSUNG jadi
                                 sibling dari checkbox, bukan cucu/nested). --}}
                            <span class="flex w-full flex-col items-center justify-center gap-0.5 rounded-lg border border-surface-alt bg-card px-2 py-2.5 text-center font-semibold text-muted-2 transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-navy/40 peer-checked:border-navy peer-checked:bg-navy peer-checked:text-card">
                                <span class="text-[13px]">{{ $label }}</span>
                                @if ($dipakaiKategoriLain)
                                    <span class="text-[10px] font-normal opacity-70">pakai {{ $labelBawaan[$kategoriLain] ?? \Illuminate\Support\Str::headline($kategoriLain) }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                <x-ui.button type="submit" variant="secondary" icon="save" class="mt-2 !h-10">Simpan Hari</x-ui.button>
            </form>
        </div>

        <div class="mt-5 grid gap-3 border-t border-surface-alt pt-4 sm:grid-cols-2">
            <div>
                <p class="text-sm font-semibold text-ink">Majukan per JP</p>
                <p class="mt-0.5 text-xs text-muted">Nomor, jam, dan jadwal kelas ikut maju mengisi slot kosong (misalnya JP kegiatan ditiadakan). Waktu istirahat tetap berada pada jam aslinya.</p>
                {{-- data-confirm di tombol, bukan di <form> -- kalau di
                     form, ngetik di kotak "Jumlah JP" ikut kepicu konfirmasi
                     (klik masuk ke input aja udah kehitung "klik di dalam
                     form"). --}}
                <form method="POST" action="{{ route('master.jam-pelajaran.maju') }}" class="mt-2 flex flex-wrap items-end gap-2">
                    @csrf
                    <input type="hidden" name="kategori" value="{{ $set }}">
                    <x-ui.input label="Jumlah JP" name="jumlah_jp" type="number" min="1" max="5" value="1" required class="!h-10 w-24" />
                    <x-ui.button type="submit" variant="secondary" icon="schedule" class="!h-10" data-confirm="Majukan seluruh jadwal kategori {{ $labelSet }}?">Majukan</x-ui.button>
                </form>
            </div>
            <div>
                <p class="text-sm font-semibold text-ink">Reset ke sebelumnya</p>
                <p class="mt-0.5 text-xs text-muted">Pulihkan salinan jadwal sebelum perubahan terakhir untuk kategori ini.</p>
                <form method="POST" action="{{ route('master.jam-pelajaran.reset') }}" class="mt-2">
                    @csrf
                    <input type="hidden" name="kategori" value="{{ $set }}">
                    <x-ui.button type="submit" variant="secondary" icon="restart_alt" class="!h-10" :disabled="! $adaJadwalSebelumnya" data-confirm="Pulihkan {{ $labelSet }} ke salinan jadwal sebelumnya? Perubahan saat ini akan diganti.">Reset Jadwal</x-ui.button>
                    @unless ($adaJadwalSebelumnya)
                        <span class="ml-2 text-xs text-muted">Belum ada salinan.</span>
                    @endunless
                </form>
            </div>
        </div>
    </section>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada konfigurasi jam untuk kategori ini" />
    @else
        <x-admin.table :head="['Jam ke-', 'Mulai', 'Selesai', 'Keterangan']">
            @foreach ($rows as $jp)
                {{-- Jeda/istirahat ditampilin sebagai barisnya sendiri (bukan cuma
                     teks nempel di JP berikutnya) -- lebih jelas kebaca, dari kolom
                     jeda_sebelum_menit/jeda_label yang terstruktur (bukan tebakan
                     dari teks lagi). --}}
                @if ($jp->jeda_sebelum_menit)
                    <tr class="bg-surface-alt/50">
                        <td colspan="4" class="px-4 py-2 text-xs font-semibold text-muted-2">
                            <x-icon name="free_breakfast" :size="14" class="-mt-0.5 inline align-middle" />
                            {{ $jp->jeda_label ?: 'Jeda' }} ({{ $jp->jeda_sebelum_menit }} menit)
                        </td>
                    </tr>
                @endif
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
        <form method="POST" action="{{ route('master.jam-pelajaran.destroy-kategori', $set) }}" class="mt-3">
            @csrf @method('DELETE')
            <x-ui.button type="submit" variant="danger" icon="delete" data-confirm="Hapus kategori &quot;{{ $labelSet }}&quot; beserta semua jamnya?">Hapus Kategori Ini</x-ui.button>
        </form>
    @endif

    {{-- SATU modal buat isi jam pelajaran -- dulu 3 modal kepisah (Edit,
         Kategori Baru, Buat Otomatis) yang sebagian besar isinya sama, cuma
         beda dikit. Sekarang 1 modal, dipilih Otomatis/Manual lewat bar,
         dipakai bareng buat kategori baru maupun yang udah ada (lihat
         data-jp-trigger di tombol atas). --}}
    <x-admin.modal id="modal-jp" size="lg" title="Jam Pelajaran" errorBag="jp">
        {{-- Beberapa error validasi (mis. "jeda harus ditempatkan di antara
             jam pelajaran") nggak nempel ke satu field tertentu, jadi nggak
             ada @error() field yang bisa nampilinnya -- ringkasan generik
             ini jaring-jaring terakhir biar TETAP kelihatan, bukan cuma
             modal kebuka lagi tanpa keterangan apa-apa. --}}
        @if ($errors->getBag('jp')->any())
            <x-alert type="error" class="mb-4">{{ $errors->getBag('jp')->first() }}</x-alert>
        @endif

        <x-ui.input id="jp-kategori-nama" name="kategori" label="Nama Kategori" placeholder="Contoh: Senin–Kamis, Ramadhan" required errorBag="jp" class="mb-4" />

        <x-ui.choice
            label="Cara mengisi"
            name="jp_mode"
            :options="['otomatis' => 'Buat Otomatis', 'manual' => 'Atur Manual']"
            :value="$modeAwal"
            class="mb-4"
            data-jp-mode
        />

        {{-- Panel OTOMATIS -- isi berurutan dari 1 jam mulai + durasi tiap JP,
             sisipkan jeda kapan aja perlu. Cocok buat mulai dari nol / ganti
             semua sekaligus. --}}
        <form method="POST" action="{{ route('master.jam-pelajaran.generate') }}" id="form-jp-otomatis" data-panel-otomatis
              class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="baru" value="0" data-kategori-baru>
            <input type="hidden" name="kategori_lama" data-kategori-lama value="{{ $set }}">
            <input type="hidden" name="kategori" data-kategori-target value="{{ $set }}">
            <x-alert type="info">
                Isi JP berurutan dengan durasi standar 40 menit. Tambahkan jeda istirahat atau MBG di antara JP; waktu JP berikutnya otomatis bergeser setelah jeda.
            </x-alert>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <x-ui.input label="JP pertama mulai" name="mulai" type="time" :value="old('mulai', $rows->first()?->mulai?->format('H:i') ?? '07:00')" required errorBag="jp" />
                <x-ui.input label="Durasi tiap JP (menit)" name="durasi_jp" type="number" min="20" max="120" :value="old('durasi_jp', 40)" required errorBag="jp" />
                <x-ui.input label="Jumlah JP" name="jumlah_jp" type="number" min="1" max="20" :value="old('jumlah_jp', $rows->count() ?: 10)" required data-jp-jumlah errorBag="jp" />
            </div>

            <label class="flex flex-col gap-1 text-xs font-semibold text-ink">
                <span>Catatan JP (opsional)</span>
                <input type="text" name="keterangan_global" maxlength="100" value="{{ old('keterangan_global') }}" placeholder="Catatan untuk semua JP, misal: Jam reguler" class="h-9 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
            </label>

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
                    @foreach ($jedaOtomatisAwal as $jedaIndex => $jedaLama)
                        <div data-jeda-row class="grid grid-cols-1 items-end gap-2 rounded-lg border border-surface-alt/80 p-2.5 sm:grid-cols-[1.3fr_0.9fr_1.3fr_auto]">
                            <x-ui.cari-pilihan
                                label="Jeda setelah"
                                name="jeda[{{ $jedaIndex }}][setelah]"
                                :options="$opsiSetelahJp"
                                :value="$jedaLama['setelah'] ?? null"
                                placeholder="Ketik atau pilih JP..."
                                required
                                compact
                                errorBag="jp"
                                data-jeda-setelah
                            />
                            <label class="flex flex-col gap-1 text-xs font-semibold text-ink">
                                <span class="whitespace-nowrap">Durasi (menit) <span class="text-alpha" aria-hidden="true">*</span></span>
                                <input type="number" name="jeda[{{ $jedaIndex }}][durasi]" min="1" max="180" value="{{ $jedaLama['durasi'] ?? 15 }}" required class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
                            </label>
                            <label class="flex flex-col gap-1 text-xs font-semibold text-ink">
                                <span class="whitespace-nowrap">Nama jeda (opsional)</span>
                                <input type="text" name="jeda[{{ $jedaIndex }}][label]" maxlength="40" value="{{ $jedaLama['label'] ?? '' }}" placeholder="Istirahat / MBG" class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
                            </label>
                            <button type="button" data-jeda-remove class="flex h-10 items-center justify-center rounded-lg bg-alpha-soft px-3 text-xs font-bold text-alpha hover:bg-[#fecdd3]">Hapus</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" icon="auto_awesome" class="flex-1" data-buat-jadwal>Buat Jadwal</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>

        {{-- Panel MANUAL -- edit baris jam pelajaran satu-satu secara leluasa,
             ditambah pengaturan jeda istirahat / MBG terpusat di bawahnya. --}}
        <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" id="form-jp-manual" data-panel-manual hidden class="flex flex-col gap-3">
            @csrf
            <input type="hidden" name="baru" value="0" data-kategori-baru>
            <input type="hidden" name="kategori_lama" data-kategori-lama value="{{ $set }}">
            <input type="hidden" name="kategori" data-kategori-target value="{{ $set }}">

            <div class="hidden gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2 sm:flex">
                <span class="w-8 shrink-0">JP</span><span class="w-28 shrink-0">Mulai <span class="text-alpha">*</span></span><span class="w-28 shrink-0">Selesai <span class="text-alpha">*</span></span><span class="flex-1">Keterangan</span><span class="w-8 shrink-0"></span>
            </div>

            <div data-jp-rows class="max-h-[215px] overflow-y-auto pr-1">
                @forelse ($rows as $jp)
                    @include('admin.jam-pelajaran._baris', ['jp' => $jp, 'nomor' => $loop->iteration])
                @empty
                    @include('admin.jam-pelajaran._baris')
                @endforelse
            </div>

            <button type="button" data-jp-add class="flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-sm font-bold text-izin hover:bg-[#bae6fd]">
                <x-icon name="add" :size="18" /> Tambah Baris
            </button>

            <div class="mt-1">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-ink">Jeda istirahat atau MBG</h4>
                        <p class="mt-1 text-xs text-muted">Opsional. Tentukan jeda setelah JP tertentu, lalu isi durasinya.</p>
                    </div>
                    <button type="button" data-jeda-add class="inline-flex h-9 shrink-0 items-center gap-1 rounded-lg bg-izin-soft px-3 text-sm font-bold text-izin hover:bg-[#bae6fd]">
                        <x-icon name="add" :size="16" /> Tambah Jeda
                    </button>
                </div>
                <div data-jeda-rows class="mt-3 flex flex-col gap-2">
                    @foreach ($jedaManualAwal as $jedaIndex => $jedaLama)
                        <div data-jeda-row class="grid grid-cols-1 items-end gap-2 rounded-lg border border-surface-alt/80 p-2.5 sm:grid-cols-[1.3fr_0.9fr_1.3fr_auto]">
                            <x-ui.cari-pilihan
                                label="Jeda setelah"
                                name="jeda[{{ $jedaIndex }}][setelah]"
                                :options="$opsiSetelahJpManual"
                                :value="$jedaLama['setelah'] ?? null"
                                placeholder="Ketik atau pilih JP..."
                                required
                                compact
                                errorBag="jp"
                                data-jeda-setelah
                            />
                            <label class="flex flex-col gap-1 text-xs font-semibold text-ink">
                                <span class="whitespace-nowrap">Durasi (menit) <span class="text-alpha" aria-hidden="true">*</span></span>
                                <input type="number" name="jeda[{{ $jedaIndex }}][durasi]" min="1" max="180" value="{{ $jedaLama['durasi'] ?? 15 }}" required class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
                            </label>
                            <label class="flex flex-col gap-1 text-xs font-semibold text-ink">
                                <span class="whitespace-nowrap">Nama jeda (opsional)</span>
                                <input type="text" name="jeda[{{ $jedaIndex }}][label]" maxlength="40" value="{{ $jedaLama['label'] ?? '' }}" placeholder="Istirahat / MBG" class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
                            </label>
                            <button type="button" data-jeda-remove class="flex h-10 items-center justify-center rounded-lg bg-alpha-soft px-3 text-xs font-bold text-alpha hover:bg-[#fecdd3]">Hapus</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan Perubahan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            (function () {
                const modal = document.getElementById('modal-jp');
                if (!modal) return;

                const namaInput = document.getElementById('jp-kategori-nama');
                const panelOtomatis = modal.querySelector('[data-panel-otomatis]');
                const panelManual = modal.querySelector('[data-panel-manual]');
                const btnBuatJadwal = panelOtomatis.querySelector('[data-buat-jadwal]');
                const jpRows = modal.querySelector('[data-jp-rows]');
                const jumlahInput = panelOtomatis.querySelector('input[name="jumlah_jp"]');

                // Bar Otomatis/Manual -- tampilin 1 panel, DISABLE semua field di
                // panel yang lagi disembunyiin (pola yang sama dipakai di banyak
                // form lain di app) biar cuma 1 form yang beneran kesubmit.
                function syncMode() {
                    const manual = modal.querySelector('input[name="jp_mode"]:checked')?.value === 'manual';
                    panelOtomatis.hidden = manual;
                    panelManual.hidden = !manual;
                    panelOtomatis.querySelectorAll('input, select, textarea').forEach((el) => { el.disabled = manual; });
                    panelManual.querySelectorAll('input, select, textarea').forEach((el) => { el.disabled = !manual; });
                }
                modal.querySelectorAll('input[name="jp_mode"]').forEach((el) => el.addEventListener('change', syncMode));
                syncMode();

                // Setup pengelolaan Jeda untuk Otomatis dan Manual
                function setupJedaSection(panel, prefix, getOptions) {
                    const jedaRows = panel.querySelector('[data-jeda-rows]');
                    const jedaAdd = panel.querySelector('[data-jeda-add]');
                    if (!jedaRows || !jedaAdd) return { sync: () => {}, clear: () => {}, setHtml: () => {} };

                    let nextIndex = jedaRows.querySelectorAll('[data-jeda-row]').length;

                    function sync() {
                        const opsi = getOptions();
                        jedaRows.querySelectorAll('[data-jeda-setelah]').forEach((wrap) => {
                            wrap.dataset.list = JSON.stringify(opsi);
                            const hidden = wrap.querySelector('[data-cari-pilihan-value]');
                            if (hidden?.value && !opsi.some((o) => String(o.id) === String(hidden.value))) {
                                hidden.value = '';
                                const input = wrap.querySelector('[data-cari-pilihan-input]');
                                const tombolClear = wrap.querySelector('[data-cari-pilihan-clear]');
                                if (input) input.value = '';
                                if (tombolClear) tombolClear.hidden = true;
                            }
                        });
                    }

                    jedaAdd.addEventListener('click', () => {
                        if (jedaRows.querySelectorAll('[data-jeda-row]').length >= 10) return;

                        const row = document.createElement('div');
                        row.dataset.jedaRow = '';
                        row.className = 'grid grid-cols-1 items-end gap-2 rounded-lg border border-surface-alt/80 p-2.5 sm:grid-cols-[1.3fr_0.9fr_1.3fr_auto]';
                        const opsiJson = JSON.stringify(getOptions()).replace(/'/g, '&#39;');
                        row.innerHTML = `
                            <div class="flex flex-col gap-1 min-w-[7rem]" data-cari-pilihan data-list='${opsiJson}' data-jeda-setelah>
                                <label class="text-xs font-semibold text-ink whitespace-nowrap" for="${prefix}-setelah-${nextIndex}">Jeda setelah <span class="text-alpha" aria-hidden="true">*</span></label>
                                <div class="relative">
                                    <div class="flex h-10 items-center gap-2 rounded-lg border border-surface-alt bg-card px-3 transition-colors focus-within:border-navy">
                                        <span class="material-symbols-rounded select-none leading-none shrink-0 text-muted-2" style="font-size: 16px; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 16;" aria-hidden="true">search</span>
                                        <input type="text" id="${prefix}-setelah-${nextIndex}" autocomplete="off" placeholder="Ketik atau pilih JP..." data-cari-pilihan-input class="w-full border-none bg-transparent text-sm font-medium text-ink outline-none placeholder:text-muted">
                                        <button type="button" data-cari-pilihan-clear hidden class="flex shrink-0 items-center text-muted-2 hover:text-alpha" aria-label="Ganti pilihan">
                                            <span class="material-symbols-rounded select-none leading-none" style="font-size: 16px; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 16;" aria-hidden="true">close</span>
                                        </button>
                                    </div>
                                    <input type="hidden" name="jeda[${nextIndex}][setelah]" value="" data-cari-pilihan-value>
                                    <div data-cari-pilihan-hasil hidden class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-surface-alt bg-card py-1 shadow-lg">
                                        <div data-cari-pilihan-daftar></div>
                                    </div>
                                </div>
                            </div>
                            <label class="flex flex-col gap-1 text-xs font-semibold text-ink">
                                <span class="whitespace-nowrap">Durasi (menit) <span class="text-alpha" aria-hidden="true">*</span></span>
                                <input type="number" name="jeda[${nextIndex}][durasi]" min="1" max="180" value="15" required class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
                            </label>
                            <label class="flex flex-col gap-1 text-xs font-semibold text-ink">
                                <span class="whitespace-nowrap">Nama jeda (opsional)</span>
                                <input type="text" name="jeda[${nextIndex}][label]" maxlength="40" placeholder="Istirahat / MBG" class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
                            </label>
                            <button type="button" data-jeda-remove class="flex h-10 items-center justify-center rounded-lg bg-alpha-soft px-3 text-xs font-bold text-alpha hover:bg-[#fecdd3]">Hapus</button>
                        `;
                        jedaRows.append(row);
                        window.initCariPilihan?.();
                        nextIndex++;
                    });

                    jedaRows.addEventListener('click', (event) => {
                        if (event.target.closest('[data-jeda-remove]')) {
                            event.target.closest('[data-jeda-row]')?.remove();
                        }
                    });

                    return {
                        sync,
                        clear: () => {
                            jedaRows.innerHTML = '';
                            nextIndex = 0;
                        },
                        setHtml: (html) => {
                            jedaRows.innerHTML = html;
                            nextIndex = jedaRows.querySelectorAll('[data-jeda-row]').length;
                            window.initCariPilihan?.();
                            sync();
                        },
                    };
                }

                const handlerOtomatis = setupJedaSection(panelOtomatis, 'jeda-otomatis', () => {
                    const n = Math.max(1, Math.min(19, Number(jumlahInput?.value) || 19));
                    return Array.from({ length: Math.max(0, n - 1) }, (_, i) => ({ id: i + 1, nama: `JP ${i + 1}` }));
                });
                jumlahInput?.addEventListener('input', handlerOtomatis.sync);

                const initialManualJedaHtml = panelManual.querySelector('[data-jeda-rows]')?.innerHTML || '';
                const handlerManual = setupJedaSection(panelManual, 'jeda-manual', () => {
                    const n = Math.max(1, Math.min(19, jpRows.querySelectorAll('[data-jp-row]').length));
                    return Array.from({ length: Math.max(0, n - 1) }, (_, i) => ({ id: i + 1, nama: `JP ${i + 1}` }));
                });

                const existingSlugs = @json($semuaKategori->map(fn ($k) => \Illuminate\Support\Str::slug($k, '_'))->values());

                // "Nama Kategori" sekarang bisa diedit baik saat kategori baru maupun edit.
                // Nilai akan disinkronkan ke field tersembunyi [data-kategori-target].
                let modeBaru = false;
                namaInput?.addEventListener('input', () => {
                    const val = (namaInput.value || '').trim();
                    modal.querySelectorAll('[data-kategori-target]').forEach((el) => { el.value = val; });
                });

                // Tombol "Kategori Baru" / "Edit" -- atur isi awal modal SEBELUM
                // initModals() bawaan nampilin dialognya.
                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-jp-trigger]');
                    if (!btn) return;
                    const baru = btn.dataset.jpBaru === '1';
                    modeBaru = baru;
                    namaInput.readOnly = false;

                    setTimeout(() => {
                        if (baru) {
                            namaInput.value = '';
                            modal.querySelectorAll('[data-kategori-target]').forEach((el) => { el.value = ''; });
                            modal.querySelectorAll('[data-kategori-lama]').forEach((el) => { el.value = ''; });
                            modal.querySelectorAll('[data-kategori-baru]').forEach((el) => { el.value = '1'; });
                            btnBuatJadwal?.removeAttribute('data-confirm');
                            handlerOtomatis.clear();
                            handlerManual.clear();
                            panelOtomatis.querySelector('input[name="mulai"]').value = '07:00';
                            panelOtomatis.querySelector('input[name="durasi_jp"]').value = '40';
                            panelOtomatis.querySelector('input[name="jumlah_jp"]').value = '10';
                            handlerOtomatis.sync();
                            handlerManual.sync();
                        } else {
                            const namaKategori = btn.dataset.jpNama || '';
                            namaInput.value = namaKategori;
                            modal.querySelectorAll('[data-kategori-target]').forEach((el) => { el.value = namaKategori; });
                            modal.querySelectorAll('[data-kategori-lama]').forEach((el) => { el.value = @json($set); });
                            modal.querySelectorAll('[data-kategori-baru]').forEach((el) => { el.value = '0'; });
                            btnBuatJadwal?.setAttribute('data-confirm', `Ganti seluruh jadwal kategori ${namaKategori} dengan jadwal otomatis yang baru?`);
                            handlerManual.setHtml(initialManualJedaHtml);
                        }
                    }, 0);

                    // Kategori baru -> mulai dari Otomatis (paling cepat dari nol).
                    // Edit yang udah ada -> mulai dari Manual (data lama kelihatan
                    // apa adanya, nggak digenerate ulang tiba-tiba).
                    const modeInput = modal.querySelector(`input[name="jp_mode"][value="${baru ? 'otomatis' : 'manual'}"]`);
                    if (modeInput) modeInput.checked = true;
                    syncMode();
                });

                function validasiDanSubmit(e) {
                    const nama = (namaInput?.value || '').trim();
                    if (!nama) {
                        e.preventDefault();
                        e.stopPropagation();
                        alert('Nama kategori wajib diisi.');
                        namaInput?.focus();
                        return false;
                    }

                    const slug = nama.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
                    const currentSet = @json($set);

                    if (modeBaru) {
                        if (existingSlugs.includes(slug)) {
                            e.preventDefault();
                            e.stopPropagation();
                            alert(`Kategori "${nama}" sudah ada. Silakan gunakan nama lain atau edit kategori tersebut.`);
                            namaInput?.focus();
                            return false;
                        }
                    } else {
                        if (slug !== currentSet && existingSlugs.includes(slug)) {
                            e.preventDefault();
                            e.stopPropagation();
                            alert(`Kategori "${nama}" sudah ada. Silakan gunakan nama lain.`);
                            namaInput?.focus();
                            return false;
                        }
                    }

                    e.target.querySelectorAll('[data-kategori-target]').forEach((el) => { el.value = nama; });
                    e.target.querySelectorAll('[data-kategori-baru]').forEach((el) => { el.value = modeBaru ? '1' : '0'; });
                    if (!modeBaru) {
                        e.target.querySelectorAll('[data-kategori-lama]').forEach((el) => { el.value = currentSet; });
                    }
                }

                panelOtomatis?.addEventListener('submit', validasiDanSubmit);
                panelManual?.addEventListener('submit', validasiDanSubmit);

                // --- Panel Manual: tambah/hapus baris ---
                const jpRenumber = () => {
                    jpRows.querySelectorAll('[data-jp-no]').forEach((el, i) => (el.textContent = i + 1));
                    handlerManual.sync();
                };

                modal.querySelector('[data-jp-add]').addEventListener('click', () => {
                    const clone = jpRows.querySelector('[data-jp-row]')?.cloneNode(true);
                    if (!clone) return;
                    clone.querySelectorAll('input[type="text"], input[type="time"], input[type="number"]').forEach((i) => (i.value = ''));
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
            })();
        </script>
    @endpush
</x-layouts.admin>
