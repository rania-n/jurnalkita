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
    $modeAwal = $errors->has('mulai.0') ? 'manual' : 'otomatis';

    // Opsi "Jeda setelah JP..." pas render server (baris re-tampil abis gagal
    // validasi) -- JS (syncOpsiJeda) yang ngurus nyesuain ulang pas Jumlah JP
    // diketik ganti-ganti di browser, ini cuma buat render awal.
    $jumlahJpAwal = max(1, (int) old('jumlah_jp', 20));
    $opsiSetelahJp = collect(range(1, $jumlahJpAwal - 1))->map(fn ($n) => ['id' => $n, 'nama' => "JP {$n}"]);
@endphp

<x-layouts.admin title="Jam Pelajaran" heading="Jam Pelajaran">
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
            <p class="mt-0.5 text-xs text-muted">Pilih hari sekolah mana saja yang pakai kategori {{ $labelSet }} ini -- bisa dicentang bebas (mis. cuma Rabu, atau semua hari). Tiap hari cuma bisa pakai 1 kategori; kalau hari yang dicentang lagi dipakai kategori lain, otomatis pindah ke kategori ini.</p>

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
                <p class="mt-0.5 text-xs text-muted">Nomor, jam, dan jadwal kelas ikut maju ngisi slot kosong (mis. JP kegiatan ditiadakan). Istirahat tetap di jam aslinya.</p>
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

    {{-- Bag "default" -- validasi bar hari/Majukan/Reset (nggak pakai bag
         khusus kayak modal "jp"). Pesan error hasil aksi (mis. "kategori ini
         belum dipakai") sendiri udah tampil otomatis lewat flash session di
         layout admin, nggak perlu dicek lagi di sini. --}}
    @if ($errors->getBag('default')->any())
        <x-alert type="error" class="mb-4">{{ $errors->getBag('default')->first() }}</x-alert>
    @endif

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
        <x-ui.input id="jp-kategori-nama" label="Nama Kategori" placeholder="Contoh: Senin–Kamis, Ramadhan" required class="mb-4" />

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
            <input type="hidden" name="kategori" data-kategori-target value="{{ $set }}">
            <x-alert type="info">
                Isi JP berurutan dengan durasi standar 40 menit. Tambahkan jeda istirahat atau MBG di antara JP; waktu JP berikutnya otomatis bergeser setelah jeda.
            </x-alert>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <x-ui.input label="JP pertama mulai" name="mulai" type="time" :value="old('mulai', $rows->first()?->mulai?->format('H:i') ?? '07:00')" required />
                <x-ui.input label="Durasi tiap JP (menit)" name="durasi_jp" type="number" min="20" max="120" :value="old('durasi_jp', 40)" required />
                <x-ui.input label="Jumlah JP" name="jumlah_jp" type="number" min="1" max="20" :value="old('jumlah_jp', $rows->count() ?: 10)" required data-jp-jumlah />
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
                            {{-- Sama kayak dropdown "cari" yang udah dipakai di form lain
                                 (mis. pilih mapel/guru/kelas) -- bukan select browser bawaan,
                                 biar konsisten. data-jeda-setelah tetap dipasang di wrapper-nya
                                 buat dipegang JS pas Jumlah JP berubah (lihat script bawah). --}}
                            <x-ui.cari-pilihan
                                label="Jeda setelah"
                                name="jeda[{{ $jedaIndex }}][setelah]"
                                :options="$opsiSetelahJp"
                                :value="$jedaLama['setelah'] ?? null"
                                placeholder="Ketik atau pilih JP..."
                                required
                                data-jeda-setelah
                            />
                            <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Durasi (menit) <span class="text-alpha" aria-hidden="true">*</span>
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

        {{-- Panel MANUAL -- edit baris satu-satu, bar durasi cepat & toggle
             jeda per baris (lihat _baris.blade.php). Cocok buat nge-tweak
             dikit dari yang udah ada. --}}
        <form method="POST" action="{{ route('master.jam-pelajaran.save') }}" id="form-jp-manual" data-panel-manual hidden class="flex flex-col gap-3">
            @csrf
            <input type="hidden" name="kategori" data-kategori-target value="{{ $set }}">

            <div class="hidden gap-2 border-b border-surface-alt pb-2 text-xs font-bold uppercase tracking-wide text-muted-2 sm:flex">
                <span class="w-8 shrink-0">JP</span><span class="w-28 shrink-0">Mulai <span class="text-alpha">*</span></span><span class="w-28 shrink-0">Selesai <span class="text-alpha">*</span></span><span class="flex-1">Keterangan</span><span class="w-8 shrink-0"></span>
            </div>

            <div data-jp-rows class="max-h-[50vh] overflow-y-auto">
                @forelse ($rows as $jp)
                    @include('admin.jam-pelajaran._baris', ['jp' => $jp, 'nomor' => $loop->iteration])
                @empty
                    @include('admin.jam-pelajaran._baris')
                @endforelse
            </div>

            <button type="button" data-jp-add class="flex w-full items-center justify-center gap-2 rounded-lg border border-izin/40 bg-izin-soft py-2.5 text-sm font-bold text-izin hover:bg-[#bae6fd]">
                <x-icon name="add" :size="18" /> Tambah Baris
            </button>

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

                // "Nama Kategori" itu 1 input yang kelihatan doang (nggak punya
                // name="", nggak kesubmit langsung) -- nilainya disalin ke field
                // kategori tersembunyi di form yang lagi aktif, biar 2 form
                // (Otomatis/Manual) nggak perlu 2 kotak nama kategori sendiri-sendiri.
                // CUMA disalin pas mode "kategori baru" -- mode Edit nampilin
                // LABEL cantik ("Senin–Kamis"), bukan KEY aslinya ("senin_kamis"),
                // jadi field tersembunyi WAJIB tetap pegang key asli dari server
                // ({{ $set }}), kalau ikut disalin malah kesimpen sebagai kategori
                // BARU yang salah nama pas disubmit.
                let modeBaru = false;
                namaInput?.addEventListener('input', () => {
                    if (!modeBaru) return;
                    modal.querySelectorAll('[data-kategori-target]').forEach((el) => { el.value = namaInput.value; });
                });

                // Tombol "Kategori Baru" / "Edit" -- atur isi awal modal SEBELUM
                // initModals() bawaan nampilin dialognya (listener ini didaftarin
                // duluan di source, jadi jalan duluan juga pas event yang sama).
                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-jp-trigger]');
                    if (!btn) return;
                    const baru = btn.dataset.jpBaru === '1';
                    modeBaru = baru;
                    namaInput.value = baru ? '' : (btn.dataset.jpNama || '');
                    namaInput.readOnly = ! baru;
                    // Konfirmasi "ganti jadwal" cuma masuk akal kalau kategorinya
                    // SUDAH ADA isinya (Buat Otomatis bakal nimpa) -- kategori baru
                    // belum punya apa-apa buat ditimpa, jadi nggak perlu ditanya.
                    if (baru) {
                        panelOtomatis.removeAttribute('data-confirm');
                        modal.querySelectorAll('[data-kategori-target]').forEach((el) => { el.value = namaInput.value; });
                        jedaRows.innerHTML = '';
                        // setTimeout(0) -- initModals() (di app.js) juga dengerin klik yang
                        // SAMA buat nge-reset <form> pas modal dibuka (form.reset(), balikin
                        // field ke value hasil render server, mis. Jumlah JP=13 dari kategori
                        // yang lagi kebuka). Listener KITA didaftarin duluan jadi kepanggil
                        // duluan juga -- tapi itu artinya form.reset() dari initModals() jalan
                        // BELAKANGAN di event yang sama, nimpa lagi nilai yang barusan kita
                        // set (bug asli yang dilaporin: Jumlah JP nunjuk 13 padahal kategori
                        // baru). Ditunda ke tick berikutnya biar kita yang jalan PALING
                        // AKHIR, setelah reset-nya kelar.
                        setTimeout(() => {
                            panelOtomatis.querySelector('input[name="mulai"]').value = '07:00';
                            panelOtomatis.querySelector('input[name="durasi_jp"]').value = '40';
                            panelOtomatis.querySelector('input[name="jumlah_jp"]').value = '10';
                            syncOpsiJeda();
                        }, 0);
                    } else {
                        panelOtomatis.setAttribute('data-confirm', `Ganti seluruh jadwal kategori ${btn.dataset.jpNama || ''} dengan jadwal otomatis yang baru?`);
                    }
                    // Kategori baru -> mulai dari Otomatis (paling cepat dari nol).
                    // Edit yang udah ada -> mulai dari Manual (data lama kelihatan
                    // apa adanya, nggak digenerate ulang tiba-tiba).
                    const modeInput = modal.querySelector(`input[name="jp_mode"][value="${baru ? 'otomatis' : 'manual'}"]`);
                    if (modeInput) modeInput.checked = true;
                    syncMode();
                });

                // "Jumlah JP" berubah -> opsi "Jeda setelah JP..." di baris yang
                // UDAH ADA ikut disesuaikan, biar nggak bisa milih "setelah JP 15"
                // padahal Jumlah JP cuma 6 (dulu opsinya statis 1-19 terus, baru
                // ketauan salah pas submit ditolak server -- sekarang dicegah dari
                // awal langsung di pilihannya).
                // "input[name=jumlah_jp]" LANGSUNG (bukan cari elemen ber-atribut
                // data-jp-jumlah) -- x-ui.input SENGAJA nglempar semua data-* ke DIV
                // pembungkus, bukan ke <input>-nya (lihat components/ui/input.blade.php),
                // jadi selector data-jp-jumlah dulu nggak pernah kena inputnya sendiri
                // (.value selalu undefined -> opsi jeda kebaca default 19 terus, nggak
                // pernah ngikut angka yang beneran diketik -- ini akar bug yang dilaporin).
                const jumlahInput = panelOtomatis.querySelector('input[name="jumlah_jp"]');
                function opsiSetelahJp() {
                    const n = Math.max(1, Math.min(19, Number(jumlahInput?.value) || 19));
                    return Array.from({ length: n - 1 }, (_, i) => ({ id: i + 1, nama: `JP ${i + 1}` }));
                }
                // Field "Jeda setelah" pakai dropdown cari (x-ui.cari-pilihan) yang sama
                // kayak field lain di app ini (pilih mapel/guru/kelas), BUKAN <select>
                // bawaan browser -- data opsinya disimpan di atribut data-list (JSON),
                // bukan <option> HTML, jadi cara nyinkronnya beda dari sebelumnya.
                function syncOpsiJeda() {
                    const opsi = opsiSetelahJp();
                    modal.querySelectorAll('[data-jeda-setelah]').forEach((wrap) => {
                        wrap.dataset.list = JSON.stringify(opsi);
                        const hidden = wrap.querySelector('[data-cari-pilihan-value]');
                        if (hidden?.value && !opsi.some((o) => String(o.id) === hidden.value)) {
                            // Nilai lama udah di luar jangkauan (Jumlah JP diperkecil) --
                            // kosongin biar admin milih ulang, jangan diam-diam ngirim
                            // JP yang udah nggak ada.
                            hidden.value = '';
                            const input = wrap.querySelector('[data-cari-pilihan-input]');
                            const tombolClear = wrap.querySelector('[data-cari-pilihan-clear]');
                            if (input) input.value = '';
                            if (tombolClear) tombolClear.hidden = true;
                        }
                    });
                }
                jumlahInput?.addEventListener('input', syncOpsiJeda);

                const jedaRows = modal.querySelector('[data-jeda-rows]');
                const jedaAdd = modal.querySelector('[data-jeda-add]');
                let jedaIndex = jedaRows?.querySelectorAll('[data-jeda-row]').length || 0;
                jedaAdd?.addEventListener('click', () => {
                    if (!jedaRows || jedaRows.querySelectorAll('[data-jeda-row]').length >= 10) return;

                    const row = document.createElement('div');
                    row.dataset.jedaRow = '';
                    row.className = 'grid grid-cols-1 items-end gap-2 rounded-xl border border-surface-alt p-3 sm:grid-cols-[1.2fr_1fr_1.2fr_auto]';
                    // Markup di bawah SENGAJA niru persis output x-ui.cari-pilihan
                    // (lihat components/ui/cari-pilihan.blade.php) -- baris ini
                    // ditambah lewat JS jadi nggak bisa manggil komponen Blade,
                    // tapi harus tetap struktur yang sama biar initCariPilihan()
                    // (dipanggil ulang di bawah, sengaja diekspos ke window buat
                    // kasus ini doang -- lihat resources/js/app.js) bisa nge-wire.
                    const opsiJson = JSON.stringify(opsiSetelahJp()).replace(/'/g, '&#39;');
                    row.innerHTML = `
                        <div class="flex flex-col gap-1.5" data-cari-pilihan data-list='${opsiJson}' data-jeda-setelah>
                            <label class="text-sm font-semibold text-ink" for="jeda-setelah-${jedaIndex}">Jeda setelah <span class="text-alpha" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span></label>
                            <div class="relative">
                                <div class="flex h-[52px] items-center gap-2 rounded-xl border border-surface-alt bg-card px-4 transition-colors focus-within:border-navy">
                                    <span class="material-symbols-rounded select-none leading-none shrink-0 text-muted-2" style="font-size: 20px; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20;" aria-hidden="true">search</span>
                                    <input type="text" id="jeda-setelah-${jedaIndex}" autocomplete="off" placeholder="Ketik atau pilih JP..." data-cari-pilihan-input class="w-full border-none bg-transparent text-[15px] text-ink outline-none placeholder:text-placeholder">
                                    <button type="button" data-cari-pilihan-clear hidden class="flex shrink-0 items-center text-muted-2 hover:text-alpha" aria-label="Ganti pilihan">
                                        <span class="material-symbols-rounded select-none leading-none" style="font-size: 18px; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 18;" aria-hidden="true">close</span>
                                    </button>
                                </div>
                                <input type="hidden" name="jeda[${jedaIndex}][setelah]" value="" data-cari-pilihan-value>
                                <div data-cari-pilihan-hasil hidden class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-surface-alt bg-card py-1 shadow-lg">
                                    <div data-cari-pilihan-daftar></div>
                                </div>
                            </div>
                        </div>
                        <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Durasi (menit) <span class="text-alpha" aria-hidden="true">*</span>
                            <input type="number" name="jeda[${jedaIndex}][durasi]" min="1" max="180" value="15" required class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                        </label>
                        <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">Nama jeda (opsional)
                            <input type="text" name="jeda[${jedaIndex}][label]" maxlength="40" placeholder="Istirahat / MBG" class="h-[52px] w-full rounded-xl border border-surface-alt bg-card px-4 text-[15px] text-ink outline-none focus:border-navy">
                        </label>
                        <button type="button" data-jeda-remove class="flex h-10 items-center justify-center rounded-lg bg-alpha-soft px-3 text-sm font-bold text-alpha">Hapus</button>
                    `;
                    jedaRows.append(row);
                    window.initCariPilihan?.();
                    jedaIndex++;
                });

                jedaRows?.addEventListener('click', (event) => {
                    if (event.target.closest('[data-jeda-remove]')) event.target.closest('[data-jeda-row]')?.remove();
                });

                // --- Panel Manual: tambah/hapus baris, bar durasi cepat, toggle jeda ---
                const jpRows = modal.querySelector('[data-jp-rows]');
                const jpRenumber = () => jpRows.querySelectorAll('[data-jp-no]').forEach((el, i) => (el.textContent = i + 1));

                modal.querySelector('[data-jp-add]').addEventListener('click', () => {
                    const clone = jpRows.querySelector('[data-jp-row]')?.cloneNode(true);
                    if (!clone) return;
                    clone.querySelectorAll('input[type="text"], input[type="time"], input[type="number"]').forEach((i) => (i.value = ''));
                    // Checkbox jeda & kotak-kotaknya HARUS direset manual -- cloneNode
                    // ikut nyalin status "checked" & kotaknya kelihatan/nggak dari
                    // baris yang di-clone, bukan otomatis balik ke kosongan.
                    const jedaToggle = clone.querySelector('[data-jp-jeda-toggle]');
                    if (jedaToggle) jedaToggle.checked = false;
                    const jedaFields = clone.querySelector('[data-jp-jeda-fields]');
                    if (jedaFields) jedaFields.hidden = true;
                    jpRows.appendChild(clone);
                    jpRenumber();
                });

                jpRows.addEventListener('click', (e) => {
                    // Bar durasi cepat -- isi "Selesai" = "Mulai" + durasi yang diklik,
                    // biar nggak itung manual & durasi antar baris konsisten.
                    const tombolDurasi = e.target.closest('[data-jp-durasi]');
                    if (tombolDurasi) {
                        const baris = tombolDurasi.closest('[data-jp-row]');
                        const mulai = baris.querySelector('[data-jp-mulai]');
                        const selesai = baris.querySelector('[data-jp-selesai]');
                        if (mulai?.value) {
                            const [jam, menit] = mulai.value.split(':').map(Number);
                            const totalMenit = jam * 60 + menit + Number(tombolDurasi.dataset.jpDurasi);
                            const jamSelesai = Math.floor(totalMenit / 60) % 24;
                            const menitSelesai = totalMenit % 60;
                            selesai.value = `${String(jamSelesai).padStart(2, '0')}:${String(menitSelesai).padStart(2, '0')}`;
                        }
                        return;
                    }

                    if (!e.target.closest('[data-jp-remove]')) return;
                    if (jpRows.querySelectorAll('[data-jp-row]').length <= 1) return;
                    if (!confirm('Hapus baris jam pelajaran ini?')) return;
                    e.target.closest('[data-jp-row]').remove();
                    jpRenumber();
                });

                // Checkbox "Ada jeda sebelum baris ini" -- munculin/sembunyiin kotak
                // menit+label. Field-nya SENGAJA nggak di-disable pas disembunyiin
                // (beda dari pola disable-blok biasa) -- lihat catatan di _baris.blade.php.
                jpRows.addEventListener('change', (e) => {
                    if (!e.target.matches('[data-jp-jeda-toggle]')) return;
                    const fields = e.target.closest('[data-jp-row]').querySelector('[data-jp-jeda-fields]');
                    fields.hidden = !e.target.checked;
                    if (!e.target.checked) fields.querySelectorAll('input').forEach((i) => (i.value = ''));
                });
            })();
        </script>
    @endpush
</x-layouts.admin>
