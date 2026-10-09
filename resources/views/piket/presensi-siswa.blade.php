<x-layouts.app title="Presensi Siswa" width="wide">
    <x-page-header
        title="Presensi Siswa"
        subtitle="Catat siswa sakit atau izin dari surat yang diterima piket. Status ini disamakan ke semua jurnal kelas pada tanggal tersebut."
        size="sm"
    />

    <form method="GET" action="{{ route('piket.presensi-siswa.index') }}" class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-surface-alt bg-card p-4 sm:grid-cols-2">
        <x-ui.cari-pilihan name="kelas_id" label="Kelas" :options="$kelasList" all="Pilih kelas" />
        <x-admin.f-date name="tanggal" label="Tanggal presensi" :value="$tanggal->toDateString()" :max="today()->toDateString()" onchange="this.form.submit()" />
    </form>

    <x-ui.auto-refresh :url="route('piket.presensi-siswa.versi')" />

    @if ($kelas)
        <form method="POST" action="{{ route('piket.presensi-siswa.store') }}" enctype="multipart/form-data"
              class="mb-6 flex flex-col gap-4 rounded-2xl border border-surface-alt bg-card p-4 sm:p-5"
              id="form-presensi-piket">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">

            @if ($siswas->isEmpty())
                <x-ui.empty icon="school" title="Belum ada siswa aktif di kelas ini" />
            @else
                @if ($presensiTerpilih)
                    <x-alert type="info">Presensi {{ $presensiTerpilih->siswa->nama }} untuk tanggal ini sudah ada. Simpan lagi untuk memperbaruinya.</x-alert>
                @endif

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-ui.cari-checkbox
                        name="siswa_ids"
                        label="Siswa"
                        :options="$siswas"
                        :value="$presensiTerpilih ? [$presensiTerpilih->siswa_id] : []"
                        hint="Pilih satu atau lebih siswa"
                        required
                    />

                    <x-ui.choice
                        label="Status Kehadiran"
                        name="status"
                        :options="['sakit' => 'Sakit', 'izin' => 'Izin', 'izin_terlambat' => 'Terlambat', 'dispensasi' => 'Dispen']"
                        :tones="['sakit' => 'sakit', 'izin' => 'izin', 'izin_terlambat' => 'alpha', 'dispensasi' => 'dispen']"
                        :value="old('status', $presensiTerpilih?->status)"
                        required
                    />

                    {{-- Terlambat: pilih mulai JP ke berapa siswa masuk --}}
                    <div id="blok-jam-masuk" class="sm:col-span-2" hidden>
                        <x-ui.select label="Mulai masuk kelas di JP ke-" name="jam_masuk" id="jam_masuk">
                            <option value="">Pilih JP</option>
                            @for ($i = 1; $i <= 13; $i++)
                                <option value="{{ $i }}" @selected(old('jam_masuk', $presensiTerpilih?->jam_masuk) == $i)>
                                    JP {{ $i }}
                                </option>
                            @endfor
                        </x-ui.select>
                        <p class="mt-1 text-xs text-muted-2">JP sebelum ini akan dicatat terlambat, JP mulai ini ke atas akan dihitung hadir.</p>
                    </div>

                    {{-- Dispensasi parsial: pilih rentang JP dispen --}}
                    <div id="blok-dispen-jp" class="grid grid-cols-1 gap-3 sm:col-span-2 sm:grid-cols-2" hidden>
                        <x-ui.select label="Dispen dari JP ke- (mulai)" name="jam_ke_mulai" id="jam_ke_mulai">
                            <option value="">Sehari penuh</option>
                            @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_mulai', $presensiTerpilih?->jam_ke_mulai) == $i)>JP {{ $i }}</option>@endfor
                        </x-ui.select>
                        <x-ui.select label="Sampai JP ke- (selesai)" name="jam_ke_selesai" id="jam_ke_selesai">
                            <option value="">Sampai selesai hari itu</option>
                            @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_selesai', $presensiTerpilih?->jam_ke_selesai) == $i)>JP {{ $i }}</option>@endfor
                        </x-ui.select>
                        <p class="-mt-1 text-xs text-muted-2 sm:col-span-2">Kosongkan jika izin berlaku sehari penuh.</p>
                    </div>

                    <div id="blok-dispen-jenis" class="sm:col-span-2" hidden>
                        <x-ui.choice
                            label="Jenis Izin (Khusus Dispen)"
                            name="jenis"
                            :options="['izin_keluar' => 'Izin Keluar', 'lomba' => 'Lomba / Dinas']"
                            :tones="['izin_keluar' => 'izin', 'lomba' => 'hadir']"
                            :value="old('jenis', 'izin_keluar')"
                            size="sm"
                        />
                    </div>

                    {{-- Tanggal selesai: hanya untuk Sakit (surat dokter bisa multi-hari) --}}
                    <div id="blok-tanggal-selesai" class="sm:col-span-2" hidden>
                        <x-admin.f-date
                            name="tanggal_selesai"
                            label="Berlaku sampai tanggal (surat dokter)"
                            :value="old('tanggal_selesai', $presensiTerpilih?->tanggal_selesai?->toDateString() ?? $tanggal->toDateString())"
                            :min="$tanggal->toDateString()"
                        />
                        <p class="mt-1 text-xs text-muted-2">Surat dokter bisa berlaku beberapa hari. Izin biasa hanya 1 hari (kosongkan ini).</p>
                    </div>

                    <x-ui.textarea label="Catatan" name="catatan" :rows="2" class="sm:col-span-2" placeholder="Contoh: mewakili lomba / izin keluarga / demam">{{ old('catatan', $presensiTerpilih?->catatan) }}</x-ui.textarea>

                    <div class="flex flex-col gap-1.5 sm:col-span-2" data-kamera-wrap>
                        <x-ui.label for="surat" id="label-surat-ui">Bukti Terlambat</x-ui.label>
                        <input id="surat" name="surat" type="file" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" data-kamera-input>
                        
                        <div class="flex min-h-40 w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-[#B8C4D9] bg-card px-5 py-6 text-center">
                            
                            <img data-kamera-img hidden alt="Pratinjau Bukti" class="max-h-56 w-full rounded-lg object-cover">
                            
                            <div id="file-info-preview" hidden class="flex flex-col items-center gap-2">
                                <x-icon name="insert_drive_file" :size="32" class="text-navy" />
                                <span class="text-sm font-semibold text-ink" id="file-name-preview"></span>
                            </div>

                            <div data-kamera-placeholder class="flex flex-col items-center gap-1.5">
                                <x-icon name="add_photo_alternate" :size="28" class="text-navy" />
                                <span class="text-xs font-semibold text-navy">Pilih File atau Buka Kamera</span>
                                <span class="text-[11px] text-muted-2">JPG, PNG, atau PDF maksimal 4 MB.</span>
                                
                                <div class="mt-2 flex gap-2">
                                    <button type="button" onclick="document.getElementById('surat').click()" class="press inline-flex items-center gap-1.5 rounded-lg bg-surface-alt px-3.5 py-2 text-xs font-bold text-ink">
                                        <x-icon name="folder_open" :size="15" /> Pilih File
                                    </button>
                                    <button type="button" data-modal-open="modal-kamera-surat" data-kamera-buka class="press inline-flex items-center gap-1.5 rounded-lg bg-navy px-3.5 py-2 text-xs font-bold text-card">
                                        <x-icon name="photo_camera" :size="15" /> Kamera
                                    </button>
                                </div>
                            </div>
                            
                            <div data-kamera-ulang hidden class="mt-2 flex gap-2">
                                <button type="button" onclick="document.getElementById('surat').click()" class="press inline-flex items-center gap-1.5 rounded-lg bg-surface-alt px-3.5 py-2 text-xs font-bold text-ink">
                                    <x-icon name="folder_open" :size="15" /> Ganti File
                                </button>
                                <button type="button" data-modal-open="modal-kamera-surat" data-kamera-buka class="press inline-flex items-center gap-1.5 rounded-lg bg-surface-alt px-3.5 py-2 text-xs font-bold text-ink">
                                    <x-icon name="refresh" :size="15" /> Buka Kamera
                                </button>
                            </div>
                        </div>

                        @error('surat')<p class="text-xs font-medium text-alpha">{{ $message }}</p>@enderror

                        @if ($presensiTerpilih?->surat_path)
                            <a class="text-sm font-semibold text-navy underline mt-1" href="{{ Storage::url($presensiTerpilih->surat_path) }}" target="_blank" rel="noopener">Lihat surat yang tersimpan</a>
                        @endif
                        
                        <x-ui.modal id="modal-kamera-surat" title="Ambil Foto Bukti">
                            <div class="flex flex-col gap-3">
                                <div data-kamera-box class="relative flex max-h-[50vh] items-center justify-center overflow-hidden rounded-xl bg-ink transition-[max-height]">
                                    <video data-kamera-video hidden autoplay playsinline muted class="max-h-[50vh] w-full object-contain"></video>
                                    <canvas data-kamera-canvas hidden></canvas>
                                    <p data-kamera-error hidden class="flex aspect-[4/3] w-full flex-col items-center justify-center gap-2 px-6 text-center text-sm font-semibold text-card">
                                        Nggak bisa buka kamera. Pastikan izin kamera diaktifkan buat browser ini, lalu coba lagi.
                                    </p>
                                    <button type="button" data-kamera-ganti hidden class="press absolute right-2.5 top-2.5 flex h-9 w-9 items-center justify-center rounded-full bg-ink/60 text-card">
                                        <x-icon name="cameraswitch" :size="18" />
                                    </button>
                                    <button type="button" data-kamera-perbesar hidden class="press absolute right-2.5 bottom-2.5 flex h-9 w-9 items-center justify-center rounded-full bg-ink/60 text-card">
                                        <x-icon name="fullscreen" :size="18" />
                                    </button>
                                </div>
                                <button type="button" data-kamera-jepret class="press flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-navy text-sm font-bold text-card">
                                    <x-icon name="photo_camera" :size="18" /> Jepret
                                </button>
                            </div>
                        </x-ui.modal>
                    </div>
                </div>

                <x-ui.button type="submit" variant="primary" icon="save" class="w-full sm:w-auto">
                    {{ $presensiTerpilih ? 'Perbarui Presensi' : 'Simpan Presensi' }}
                </x-ui.button>
            @endif
        </form>

        <script>
            (function () {
                const radios = document.querySelectorAll('#form-presensi-piket input[name="status"]');
                const blokJamMasuk = document.getElementById('blok-jam-masuk');
                const blokDispenJp = document.getElementById('blok-dispen-jp');
                const blokDispenJenis = document.getElementById('blok-dispen-jenis');
                const blokTanggalSelesai = document.getElementById('blok-tanggal-selesai');
                const inputJamMasuk = document.getElementById('jam_masuk');
                const labelSurat = document.getElementById('label-surat-ui');
                const inputSurat = document.getElementById('surat');
                const imgPreview = document.querySelector('[data-kamera-img]');
                const placeholder = document.querySelector('[data-kamera-placeholder]');
                const divUlang = document.querySelector('[data-kamera-ulang]');
                const fileInfoPreview = document.getElementById('file-info-preview');
                const fileNamePreview = document.getElementById('file-name-preview');

                if (inputSurat) {
                    inputSurat.addEventListener('change', () => {
                        if (inputSurat.files && inputSurat.files[0]) {
                            const file = inputSurat.files[0];
                            placeholder.hidden = true;
                            if (divUlang) divUlang.hidden = false;
                            
                            if (file.type.startsWith('image/')) {
                                imgPreview.src = URL.createObjectURL(file);
                                imgPreview.hidden = false;
                                fileInfoPreview.hidden = true;
                            } else {
                                imgPreview.hidden = true;
                                fileNamePreview.textContent = file.name;
                                fileInfoPreview.hidden = false;
                            }
                        } else {
                            placeholder.hidden = false;
                            if (divUlang) divUlang.hidden = true;
                            imgPreview.hidden = true;
                            fileInfoPreview.hidden = true;
                        }
                    });
                }

                function sync() {
                    const val = document.querySelector('#form-presensi-piket input[name="status"]:checked')?.value;
                    const terlambat = val === 'izin_terlambat';
                    const sakit = val === 'sakit';
                    const dispen = val === 'dispensasi';

                    blokJamMasuk.hidden = !terlambat;
                    if (inputJamMasuk) inputJamMasuk.required = terlambat;

                    if (blokDispenJp) blokDispenJp.hidden = !dispen;
                    if (blokDispenJenis) blokDispenJenis.hidden = !dispen;
                    
                    // Surat dokter (tanggal_selesai) hanya untuk Sakit
                    blokTanggalSelesai.hidden = !sakit;
                    
                    const textareaCatatan = document.querySelector('textarea[name="catatan"]');
                    if (labelSurat) {
                        if (val === 'izin_terlambat') {
                            labelSurat.textContent = 'Bukti Terlambat';
                            if (textareaCatatan) textareaCatatan.placeholder = 'Contoh: ban bocor / macet';
                        } else if (val === 'sakit') {
                            labelSurat.textContent = 'Bukti Sakit';
                            if (textareaCatatan) textareaCatatan.placeholder = 'Contoh: demam / pusing';
                        } else if (val === 'izin') {
                            labelSurat.textContent = 'Bukti Izin';
                            if (textareaCatatan) textareaCatatan.placeholder = 'Contoh: acara keluarga';
                        } else if (val === 'dispensasi') {
                            labelSurat.textContent = 'Surat Izin / Bukti Pendukung';
                            if (textareaCatatan) textareaCatatan.placeholder = 'Contoh: mewakili lomba';
                        } else {
                            labelSurat.textContent = 'Bukti';
                            if (textareaCatatan) textareaCatatan.placeholder = 'Contoh: mewakili lomba / izin keluarga / demam';
                        }
                    }
                }

                radios.forEach((r) => r.addEventListener('change', sync));
                sync();
            })();
        </script>
    @else
        <x-alert type="info" class="mb-5">Pilih kelas terlebih dahulu untuk menginput surat siswa.</x-alert>
    @endif

    <section>
        <h2 class="mb-3 text-base font-bold text-ink">Catatan tanggal {{ $tanggal->translatedFormat('d M Y') }}</h2>
        @if ($catatanPresensi->isEmpty())
            <x-ui.empty icon="event_busy" title="Belum ada presensi dari piket" />
        @else
            <div class="mb-3">
                <x-ui.search-bar id="cari-catatan-presensi" placeholder="Cari nama siswa atau kelas..." />
            </div>

            <div class="flex flex-col gap-2">
                @foreach ($catatanPresensi as $catatan)
                    <div data-baris-catatan-presensi data-cari="{{ strtolower($catatan->siswa->nama.' '.$catatan->siswa->kelas->nama) }}">
                        <x-ui.list-card
                            :title="$catatan->siswa->nama"
                            :meta="[$catatan->siswa->kelas->nama . ' · No. ' . ($catatan->siswa->no_absen ?? '—'), $catatan->catatan ?: 'Dicatat oleh ' . ($catatan->dicatatOleh?->name ?? 'piket')]"
                        >
                            <x-slot:badge>
                                <x-ui.status-badge :status="$catatan->status" />
                            </x-slot:badge>
                            <x-slot:actions>
                                <x-ui.action-button
                                    label="Ubah"
                                    icon="edit"
                                    :href="route('piket.presensi-siswa.index', ['tanggal' => $tanggal->toDateString(), 'kelas_id' => $catatan->siswa->kelas_id, 'siswa_id' => $catatan->siswa_id])"
                                />
                                @if ($catatan->surat_path)
                                    <x-ui.action-button label="Lihat Surat" icon="description" :href="Storage::url($catatan->surat_path)" target="_blank" />
                                @endif
                            </x-slot:actions>
                        </x-ui.list-card>
                    </div>
                @endforeach
            </div>

            <p id="catatan-presensi-kosong" hidden class="mt-2 rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
                Tidak ada catatan yang cocok dengan pencarian.
            </p>

            @push('scripts')
                <script>
                    (function () {
                        const cari = document.getElementById('cari-catatan-presensi');
                        const rows = document.querySelectorAll('[data-baris-catatan-presensi]');
                        const kosong = document.getElementById('catatan-presensi-kosong');
                        if (!cari) return;

                        cari.addEventListener('input', () => {
                            const q = cari.value.trim().toLowerCase();
                            let ada = false;
                            rows.forEach((row) => {
                                const cocok = !q || row.dataset.cari.includes(q);
                                row.hidden = !cocok;
                                if (cocok) ada = true;
                            });
                            if (kosong) kosong.hidden = ada;
                        });
                    })();
                </script>
            @endpush
        @endif
    </section>
</x-layouts.app>
