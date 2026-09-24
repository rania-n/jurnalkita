<x-layouts.app title="Form Jurnal Mengajar">
    {{-- Judul tetap ditulis (size="sm" -- dikecilin, bukan dihilangin total)
         biar halaman nggak kesan cuma numpang teks tanpa kop sama sekali,
         walau isinya sama kayak yang udah disorot di navbar/sidebar. --}}
    <x-page-header
        title="Isi Jurnal"
        subtitle="Isi jurnal mengajar dan kehadiran siswa dalam satu langkah"
        size="sm"
    />

    @if ($modeJurnal === 'bebas_kemarin')
        {{-- Cuma muncul kalau admin udah ngizinin mode "bebas isi hari ini +
             kemarin" -- lihat Admin\PengaturanJurnalController. Ganti tab
             muat ulang halaman (bukan AJAX), biar semua data (jadwal, status
             udah-diisi, dll) kerender ulang dari server sesuai tanggalnya. --}}
        <div class="mb-4 flex gap-1 rounded-lg border border-surface-alt bg-card p-1">
            <a href="{{ route('jurnal.create') }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold transition-colors', 'bg-navy text-card' => ! $pakaiKemarin, 'text-muted-2 hover:text-ink' => $pakaiKemarin])>
                Hari Ini
            </a>
            <a href="{{ route('jurnal.create', ['hari' => 'kemarin']) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold transition-colors', 'bg-navy text-card' => $pakaiKemarin, 'text-muted-2 hover:text-ink' => ! $pakaiKemarin])>
                Kemarin (Susulan)
            </a>
        </div>
    @endif

    @if ($jumlahSudahDiisiHariIni > 0)
        {{-- Jadwal yang udah ada jurnalnya di tanggal ini nggak muncul lagi di
             pilihan bawah -- baru bisa diisi ulang kalau jurnalnya dihapus. --}}
        <x-alert type="success" class="mb-4">
            {{ $jumlahSudahDiisiHariIni }} jadwal {{ $pakaiKemarin ? 'kemarin' : 'hari ini' }} sudah Anda isi jurnalnya — nggak muncul lagi di pilihan bawah.
            <a href="{{ route('jurnal.index') }}" class="font-bold underline">Lihat di Riwayat</a>.
        </x-alert>
    @endif

    @if ($jadwals->isEmpty())
        <x-ui.empty
            icon="event_busy"
            :title="$jumlahSudahDiisiHariIni > 0 ? 'Semua jadwal hari ini sudah diisi' : 'Belum ada jadwal mengajar'"
            :desc="$jumlahSudahDiisiHariIni > 0 ? 'Mantap, kelar semua! Kalau ada yang perlu diubah, buka dari Riwayat.' : 'Hubungi admin untuk menambahkan jadwal Anda.'"
        />
    @elseif ($jurnalDiblokirIstirahat)
        {{-- Lagi istirahat/pergantian jam (masih dalam rentang jam sekolah,
             tapi nggak ada jadwal yang beneran lagi berlangsung buat guru
             ini) -- sengaja nggak dikasih akses milih jadwal lain di sini,
             biar nggak ada celah isi jurnal buat jam yang belum/nggak
             beneran dijalani. Baru bebas milih lagi begitu jam pelajaran
             berikutnya mulai. Akses bebas pilih jadwal DI LUAR jam sekolah
             tetap ada di jadwalBolehDiisi() (buat testing), tapi sengaja
             nggak disebut di teks ini -- jangan dikasih tau ke guru, nanti
             jadi celah buat "ngisi jurnal" jam yang nggak beneran dijalani. --}}
        <x-ui.empty
            icon="hourglass_empty"
            title="Belum waktunya isi jurnal"
            desc="Sedang di luar jam pelajaran (istirahat/pergantian jam). Coba lagi begitu jam pelajaran Anda mulai."
        />
    @else
        <form id="form-jurnal" method="POST" action="{{ route('jurnal.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- Info pengajar & tanggal (otomatis) --}}
            <div class="flex items-center gap-2 rounded-xl bg-surface-alt px-3.5 py-3">
                <x-icon name="person" :size="18" class="text-navy" />
                <span class="text-sm font-semibold text-ink">{{ auth()->user()->name }}</span>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-surface-alt px-3.5 py-3">
                <x-icon name="calendar_month" :size="18" class="text-navy" />
                <span class="text-sm font-semibold text-ink">
                    {{ $tanggalAktif->translatedFormat('d M Y') }}
                    @if ($pakaiKemarin) <span class="text-xs font-normal text-muted-2">(Kemarin)</span> @endif
                </span>
            </div>

            @php
                $mulaiAwal = $jadwalTerpilih->jam_ke_mulai ?? $jpSekarang;
                $selesaiAwal = old('jam_ke_selesai', $jadwalTerpilih->jam_ke_selesai ?? $jpSekarang);
                $hariJadwalTerpilih = $jadwalTerpilih->hari ?? \App\Support\HariSekolah::hariIni();
                $jamAwal = $hariJadwalTerpilih ? \App\Support\Waktu::rentangJamUntukHari($hariJadwalTerpilih, $mulaiAwal, $selesaiAwal) : null;
            @endphp

            @if ($jadwalTerkunci)
                <x-ui.field-static label="Kelas & Mata Pelajaran" icon="lock_clock" tone="muted" class="sm:col-span-2">
                    <span data-jadwal-terkunci-teks>
                        {{ $jadwalTerpilih->kelas->nama }} · {{ $jadwalTerpilih->mapel->nama }} — JP {{ $jadwalTerpilih->jam_ke_mulai }}–{{ $jadwalTerpilih->jam_ke_selesai }}
                    </span>
                    @if ($jamAwal)
                        <span class="text-muted-2">({{ $jamAwal }})</span>
                    @endif
                </x-ui.field-static>
                <input type="hidden" name="jadwal_id" value="{{ $jadwalTerpilih->id }}">
                <p class="-mt-2 text-xs text-muted-2 sm:col-span-2">Otomatis ikut jadwal Anda sekarang. Salah jadwal? Hubungi Admin.</p>
            @else
                {{-- Ganti jadwal -> muat ulang halaman (bukan AJAX) biar presensi kelas
                     yang tepat ikut kerender dari server. Materi/dll yang sudah
                     diketik sebelum ganti jadwal memang akan hilang -- wajar karena
                     pindah kelas = konteks jurnalnya beda total. --}}
                <x-ui.select label="Kelas & Mata Pelajaran" name="jadwal_id" id="jadwal_id" class="sm:col-span-2" required>
                    <option value="" disabled @selected(! $jadwalTerpilih) hidden>Pilih jadwal</option>
                    @foreach ($jadwals as $j)
                        @php $jamOpsi = \App\Support\Waktu::rentangJamUntukHari($j->hari, $j->jam_ke_mulai, $j->jam_ke_selesai); @endphp
                        <option value="{{ $j->id }}" data-mulai="{{ $j->jam_ke_mulai }}" data-selesai="{{ $j->jam_ke_selesai }}" @selected($jadwalTerpilih?->id === $j->id)>
                            {{ $j->kelas->nama }} · {{ $j->mapel->nama }} — {{ ucfirst($j->hari) }} JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}{{ $jamOpsi ? " ({$jamOpsi})" : '' }}
                        </option>
                    @endforeach
                </x-ui.select>
            @endif

            {{-- Jam mulai & selesai SELALU ngikut jadwal (statis, nggak bisa diedit
                 manual) -- guru nggak perlu (dan nggak boleh) ngarang jam sendiri,
                 itu udah ditentuin jadwalnya. Pas jadwal diganti lewat dropdown di
                 atas, dua-duanya ikut kesinkron otomatis (lihat sync() di bawah). --}}
            <div>
                <x-ui.field-static label="Jam ke- (mulai)" icon="schedule" tone="muted">
                    <span id="tampilan-jam-mulai">Jam ke-{{ $mulaiAwal }}</span>
                </x-ui.field-static>
                <input type="hidden" name="jam_ke_mulai" id="jam_ke_mulai" value="{{ $mulaiAwal }}">
            </div>
            <div>
                <x-ui.field-static label="Jam ke- (selesai)" icon="schedule" tone="muted">
                    <span id="tampilan-jam-selesai">Jam ke-{{ $selesaiAwal }}</span>
                </x-ui.field-static>
                <input type="hidden" name="jam_ke_selesai" id="jam_ke_selesai" value="{{ $selesaiAwal }}">
            </div>
            <p class="-mt-2 text-xs text-muted-2 sm:col-span-2" id="keterangan-jam">
                Jam mengajar otomatis mengikuti jadwal yang dipilih.
                @if ($jamAwal) <span id="keterangan-jam-aktual">Waktunya {{ $jamAwal }}.</span> @endif
            </p>

            {{-- Acuan dari jurnal TERAKHIR di jadwal yang sama (bisa minggu lalu,
                 bisa lebih lama kalau libur) -- biar guru/pengurus kelas yang isi
                 nggak lupa nyambungin dari mana terakhir kali, tanpa harus buka
                 Riwayat Jurnal dulu di tab lain. --}}
            @if ($jurnalSebelumnya)
                <div class="sm:col-span-2 rounded-xl bg-surface-alt/60 px-3.5 py-2.5 text-xs text-muted">
                    <span class="font-semibold text-ink">
                        Terakhir diisi ({{ $jurnalSebelumnya->tanggal->translatedFormat('d M Y') }}{{ $jurnalSebelumnya->status_guru === 'tidak_hadir' ? ', gurunya tidak hadir' : '' }}):
                    </span>
                    {{ ($jurnalSebelumnya->status_guru === 'hadir' ? $jurnalSebelumnya->materi : $jurnalSebelumnya->tugas_tambahan) ?: '—' }}
                </div>
            @endif

            {{-- Ganti jadwal (dropdown di atas) muat ulang HALAMAN PENUH --
                 kalau guru udah sempat pilih "Tidak Hadir" duluan sebelum
                 ganti jadwal, itu bakal ke-reset balik ke "Hadir" tanpa
                 kesadaran (bug yang sempat dilaporkan). request()->query
                 jadi jaring kedua setelah old() -- JS di bawah nyisipin
                 status_guru yang lagi kepilih ke URL pas jadwal diganti,
                 biar ikut kebawa lagi pas halaman render ulang. --}}
            <x-ui.choice
                label="Status Kehadiran Anda"
                name="status_guru"
                class="sm:col-span-2"
                :options="['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir']"
                :tones="['hadir' => 'hadir', 'tidak_hadir' => 'alpha']"
                :value="old('status_guru', request()->query('status_guru', 'hadir'))"
            />
            </div>

            {{-- Semua field di sini full-width (sm:col-span-2), jadi nggak perlu ikut
                 grid 2-kolom di atas -- aman langsung disembunyikan/ditampilkan. --}}
            <div id="blok-hadir" class="mt-4 flex flex-col gap-4">
                <x-ui.textarea label="Materi" name="materi" :rows="3" placeholder="Materi yang diajarkan..." required>{{ old('materi') }}</x-ui.textarea>

                <div class="flex flex-col gap-1.5">
                    <x-ui.choice
                        label="Metode Pembelajaran"
                        name="metode_pilihan"
                        :options="$metodeLabel"
                        :value="$metodeTerpilih"
                        required
                    />
                    <div id="metode_custom_wrap" hidden>
                        <x-ui.input name="metode_custom" placeholder="Tulis metode lainnya..." value="{{ $metodeCustom }}" required />
                    </div>
                </div>

                @if ($jadwalTerpilih)
                    {{-- Wajib jepret langsung dari kamera (nggak boleh unggah dari
                         galeri) -- biar beneran bukti sedang di kelas, bukan foto
                         lama. Jalan di HP maupun PC/laptop (lihat komponennya). --}}
                    <x-ui.upload-kamera
                        label="Foto Suasana Kelas"
                        name="foto_bukti"
                        hint="Wajib diisi — bukti pembelajaran sedang berlangsung"
                        required
                    />
                @endif
            </div>

            <div id="blok-tidak-hadir" class="mt-4 flex flex-col gap-4" hidden>
                {{-- Alasan duluan (langsung di bawah Status Kehadiran) --
                     itu pertanyaan paling dasar begitu "Tidak Hadir" dipilih,
                     baru abis itu urusan cakupannya (1 kelas ini/semua kelas)
                     & tugas buat siswa. --}}
                <x-ui.choice
                    label="Alasan"
                    name="alasan"
                    :options="$alasanLabel"
                    :value="old('alasan')"
                    required
                />

                @if ($jadwals->count() > 1)
                    {{-- Izin/sakit biasanya bukan cuma 1 jam pelajaran -- kalau guru
                         megang lebih dari 1 jadwal hari ini, tawarin tandain
                         sekaligus. Pilih "Ya" -> checklist kelas muncul LANGSUNG
                         di bawah (nggak pindah halaman lagi), sama pola kayak
                         milih Kelas di form ini -- baru abis dipilih, bagian
                         yang relevan (presensi/checklist) muncul. --}}
                    <x-ui.choice
                        label="Tidak Hadir 1 Hari Penuh?"
                        name="tidak_hadir_sehari_penuh"
                        :options="['tidak' => 'Cuma Kelas Ini', 'ya' => 'Ya, Semua Kelas']"
                        value="tidak"
                        data-toggle-massal
                    />

                    {{-- Checklist ini SEBAGIAN GEDE tersembunyi & field-nya
                         disabled selama "Cuma Kelas Ini" -- sync() di script
                         bawah yang nampilin/nyembunyiin & disable/enable-nya. --}}
                    <div id="blok-massal-kelas" class="flex flex-col gap-2" hidden>
                        <x-ui.label>Kelas yang Ditandai</x-ui.label>
                        <p class="-mt-1 text-xs text-muted-2">Semua tercentang otomatis -- ketuk kartunya buat centang/batal. Alasan & Tugas Tambahan di bawah berlaku buat semua yang tercentang, kecuali diisi khusus.</p>

                        <div class="flex flex-col gap-2">
                            @foreach ($jadwals as $j)
                                {{-- Sengaja pakai <div>, BUKAN <label> buat bungkus semua --
                                     kalau seluruh kartu jadi <label>, ngeklik tombol "Tugas
                                     khusus" atau ngetik di kotak teksnya bakal ikut nge-toggle
                                     checkbox di atasnya (perilaku bawaan <label>). Checkbox-nya
                                     sendiri tetap dibungkus <label> kecil biar area klik-nya
                                     tetap nyaman. --}}
                                <div class="flex flex-col gap-2 rounded-2xl border border-surface-alt bg-card p-3 transition-colors has-[[data-checkbox-jadwal-massal]:checked]:border-navy has-[[data-checkbox-jadwal-massal]:checked]:bg-surface-alt/60" data-baris-jadwal-massal>
                                    <label class="flex cursor-pointer items-center gap-2.5">
                                        <input type="checkbox" name="jadwal_ids[]" value="{{ $j->id }}" checked
                                            class="h-4 w-4 shrink-0 rounded border-surface-alt text-navy focus:ring-navy" data-checkbox-jadwal-massal>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-ink">{{ $j->kelas->nama }} · {{ $j->mapel->nama }}</span>
                                            <span class="block text-xs text-muted-2">{{ ucfirst($j->hari) }} JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</span>
                                        </span>
                                    </label>

                                    <div class="border-t border-surface-alt pt-2">
                                        <button type="button" data-toggle-khusus="{{ $j->id }}" class="flex items-center gap-1 text-xs font-semibold text-navy hover:underline">
                                            <x-icon name="add_circle" :size="14" />
                                            Tugas khusus buat kelas ini
                                        </button>
                                        <div id="tugas-khusus-{{ $j->id }}" class="mt-2" hidden>
                                            <x-ui.input name="tugas_khusus[{{ $j->id }}]" placeholder="Tugas khusus (kosongkan buat pakai default di bawah)" />
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <x-ui.textarea label="Tugas Tambahan" name="tugas_tambahan" :rows="2" placeholder="Kerjakan LKS halaman..." required>{{ old('tugas_tambahan') }}</x-ui.textarea>

                @if ($jadwalTerpilih)
                    {{-- Opsional (BEDA dari foto suasana kelas di atas) -- guru
                         nggak di sekolah, jadi nggak wajib jepret kamera, cukup
                         lampirin foto/scan surat izin dari galeri kalau ada.
                         Dipakai juga buat mode "Ya, Semua Kelas" -- 1 lampiran
                         yang sama berlaku ke semua kelas yang ditandai. --}}
                    {{-- id BEDA dari yang di blok-hadir (walau name-nya sama
                         persis "foto_bukti") -- dua elemen id kembar bikin
                         getElementById/dst nebak-nebak (sama kasusnya kayak
                         cari-pilihan, lihat catatan di komponen itu). --}}
                    <x-ui.upload
                        id="foto_bukti_tidak_hadir"
                        label="Surat Izin/Sakit (opsional)"
                        name="foto_bukti"
                        title="Lampirkan Surat / Foto Bukti"
                        hint="JPG, PNG, atau PDF"
                        accept="image/*,application/pdf"
                    />
                @endif
            </div>

            @if ($jadwalTerpilih)
                {{-- Disembunyikan pas mode massal aktif -- presensi 1 kelas ini
                     nggak relevan lagi kalau guru nyatain absen dari SEMUA
                     kelas (server juga nggak makai kiriman presensi buat
                     storeMassal(), lihat sync() di script bawah). --}}
                <div id="blok-presensi">
                    @include('guru.jurnal._presensi-grid')
                </div>
            @else
                <x-alert type="info" class="mt-6">Pilih kelas & mata pelajaran dulu di atas untuk mengisi presensi siswa.</x-alert>
            @endif

            <x-ui.sticky-bar>
                <x-ui.button type="submit" block icon="save">Simpan Jurnal &amp; Presensi</x-ui.button>
            </x-ui.sticky-bar>
        </form>

        {{-- Ringkasan sebelum beneran terkirim -- guru sempat cek dulu semua
             udah bener (biasanya isi jurnal buru-buru pas lagi jalan ke kelas
             lain), baru pilih "Kirim". Bukan validasi ulang (itu tetap di
             server) -- cuma tampilan ringkas dari apa yang udah diisi di form. --}}
        <x-ui.modal id="modal-ringkasan-jurnal" title="Cek Dulu Sebelum Kirim">
            <div class="flex flex-col gap-3 text-sm">
                <x-ui.field-static label="Kelas & Mata Pelajaran"><span data-ringkasan="kelas-mapel">—</span></x-ui.field-static>
                <x-ui.field-static label="Status Kehadiran Anda"><span data-ringkasan="status-guru">—</span></x-ui.field-static>

                {{-- Beda field & label tergantung Status Kehadiran -- jurnal
                     Tidak Hadir nggak ada "Materi"/"Foto Suasana Kelas", jadi
                     jangan dipaksa pakai label yang sama kayak Hadir. --}}
                <div data-ringkasan-blok-hadir class="flex flex-col gap-3">
                    <x-ui.field-static label="Materi"><span data-ringkasan="materi">—</span></x-ui.field-static>
                    <x-ui.field-static label="Foto Suasana Kelas"><span data-ringkasan="foto-hadir">—</span></x-ui.field-static>
                </div>
                <div data-ringkasan-blok-tidak-hadir class="flex flex-col gap-3" hidden>
                    <x-ui.field-static label="Alasan"><span data-ringkasan="alasan">—</span></x-ui.field-static>
                    <x-ui.field-static label="Tugas Tambahan"><span data-ringkasan="tugas">—</span></x-ui.field-static>
                    <x-ui.field-static label="Surat Izin/Sakit"><span data-ringkasan="surat">—</span></x-ui.field-static>
                </div>

                {{-- Presensi cuma relevan kalau BENERAN ada 1 kelas yang lagi
                     diisi presensinya di halaman ini -- disembunyiin total pas
                     mode massal (guru nggak ngisi presensi manual sama sekali
                     di situ, lihat blok-presensi di form). --}}
                <x-ui.field-static data-ringkasan-blok-presensi label="Presensi Siswa"><span data-ringkasan="presensi">—</span></x-ui.field-static>
            </div>
            {{-- Sejajar kanan-kiri langsung (bukan numpuk di HP dulu) -- samain
                 sama pola tombol submit/batal modal lain di app (mis. modal
                 Tambah/Ubah di Admin), bukan bikin pola baru. --}}
            <div class="mt-4 flex gap-2">
                <x-ui.button type="button" id="tombol-kirim-jurnal" icon="send" class="flex-1">Sudah Benar, Kirim</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Cek Lagi</x-ui.button>
            </div>
        </x-ui.modal>

        @push('scripts')
            <script>
                (function () {
                    const form = document.getElementById('form-jurnal');
                    const modalRingkasan = document.getElementById('modal-ringkasan-jurnal');
                    if (!form || !modalRingkasan) return;

                    let dikonfirmasi = false;

                    function teksTerpilih(name) {
                        const opt = form.querySelector(`select[name="${name}"] option:checked`);
                        return opt ? opt.textContent.trim() : null;
                    }

                    function isiRingkasan() {
                        // Mode massal (semua kelas) -> daftar kelas yang beneran
                        // ditandai, bukan cuma jadwal yang kepilih di atas.
                        const kelasTertandai = Array.from(form.querySelectorAll('[data-checkbox-jadwal-massal]:not(:disabled):checked'))
                            .map((cb) => cb.closest('[data-baris-jadwal-massal]')?.querySelector('.text-ink')?.textContent.trim())
                            .filter(Boolean);
                        const massal = kelasTertandai.length > 0;

                        let kelasMapel;
                        if (massal) {
                            kelasMapel = kelasTertandai.join(', ');
                        } else {
                            const jadwalSelect = form.querySelector('select[name="jadwal_id"]');
                            kelasMapel = jadwalSelect
                                ? (jadwalSelect.options[jadwalSelect.selectedIndex]?.textContent.trim() || '—')
                                : (document.querySelector('[data-jadwal-terkunci-teks]')?.textContent.trim() || '—');
                        }
                        modalRingkasan.querySelector('[data-ringkasan="kelas-mapel"]').textContent = kelasMapel;

                        const statusGuru = form.querySelector('input[name="status_guru"]:checked')?.value;
                        const hadir = statusGuru === 'hadir';
                        modalRingkasan.querySelector('[data-ringkasan="status-guru"]').textContent = hadir ? 'Hadir' : 'Tidak Hadir';

                        // Field yang ditampilin BEDA total antara Hadir & Tidak
                        // Hadir (bukan cuma isinya -- labelnya juga nggak masuk
                        // akal dipakai bareng, mis. "Foto Suasana Kelas" nggak
                        // relevan buat guru yang nggak di sekolah).
                        modalRingkasan.querySelector('[data-ringkasan-blok-hadir]').hidden = ! hadir;
                        modalRingkasan.querySelector('[data-ringkasan-blok-tidak-hadir]').hidden = hadir;

                        if (hadir) {
                            const materi = form.querySelector('[name="materi"]')?.value.trim();
                            modalRingkasan.querySelector('[data-ringkasan="materi"]').textContent = materi || '(belum diisi)';

                            const fotoKamera = form.querySelector('[data-kamera-input]:not(:disabled)');
                            modalRingkasan.querySelector('[data-ringkasan="foto-hadir"]').textContent =
                                (fotoKamera?.files?.length > 0) ? 'Sudah dilampirkan' : 'Belum diambil';
                        } else {
                            const tugas = form.querySelector('[name="tugas_tambahan"]:not(:disabled)')?.value.trim();
                            modalRingkasan.querySelector('[data-ringkasan="tugas"]').textContent = tugas || '(belum diisi)';

                            // Alasan bar (radio), bukan teks bebas -- ambil yang
                            // BENERAN kecentang, bukan cuma elemen pertama.
                            const alasanLabel = { sakit: 'Sakit', izin: 'Izin' };
                            const alasan = form.querySelector('input[name="alasan"]:checked')?.value;
                            modalRingkasan.querySelector('[data-ringkasan="alasan"]').textContent =
                                alasan ? (alasanLabel[alasan] ?? alasan) : '(belum dipilih)';

                            const suratInput = form.querySelector('[data-upload-input]:not(:disabled)');
                            modalRingkasan.querySelector('[data-ringkasan="surat"]').textContent =
                                (suratInput?.files?.length > 0) ? 'Sudah dilampirkan' : 'Tidak dilampirkan (opsional)';
                        }

                        // Presensi cuma relevan kalau ada grid-nya beneran kelihatan
                        // di halaman (nggak ada sama sekali pas mode massal -- guru
                        // nggak di kelas manapun buat nentuin presensi manual).
                        const blokPresensiEl = document.getElementById('blok-presensi');
                        const presensiField = modalRingkasan.querySelector('[data-ringkasan-blok-presensi]');
                        const presensiKelihatan = blokPresensiEl && ! blokPresensiEl.hidden;
                        presensiField.hidden = ! presensiKelihatan;

                        if (presensiKelihatan) {
                            // Yang ditampilin cuma nama yang BUKAN Hadir -- itu yang
                            // paling penting dicek ulang sebelum kirim (semuanya
                            // udah Hadir emang defaultnya, nge-list 36 nama satu-satu
                            // di sini nggak nambah info, cuma bikin ringkasan panjang).
                            const rowsSiswa = form.querySelectorAll('[data-siswa-row]');
                            const kelompok = { sakit: [], izin: [], alpha: [], dispensasi: [] };
                            let jumlahHadir = 0;
                            rowsSiswa.forEach((row) => {
                                const status = row.querySelector('input[type="radio"]:checked')?.value ?? 'hadir';
                                if (status === 'hadir') { jumlahHadir++; return; }
                                const nama = row.querySelector('.text-ink')?.textContent.trim() || '(tanpa nama)';
                                (kelompok[status] ?? (kelompok[status] = [])).push(nama);
                            });
                            const label = { sakit: 'Sakit', izin: 'Izin', alpha: 'Alpha', dispensasi: 'Dispensasi' };
                            const bagianTidakHadir = Object.entries(kelompok)
                                .filter(([, arr]) => arr.length > 0)
                                .map(([k, arr]) => `${label[k] || k}: ${arr.join(', ')}`)
                                .join(' · ');
                            modalRingkasan.querySelector('[data-ringkasan="presensi"]').textContent = rowsSiswa.length
                                ? (bagianTidakHadir ? `${bagianTidakHadir} (sisanya ${jumlahHadir} Hadir)` : `Semua ${rowsSiswa.length} siswa Hadir`)
                                : '—';
                        }
                    }

                    form.addEventListener('submit', (e) => {
                        if (dikonfirmasi) return;
                        e.preventDefault();
                        isiRingkasan();
                        modalRingkasan.showModal();
                    });

                    document.getElementById('tombol-kirim-jurnal')?.addEventListener('click', () => {
                        dikonfirmasi = true;
                        modalRingkasan.close();
                        form.requestSubmit();
                    });
                })();
            </script>
        @endpush

        @push('scripts')
            <script>
                (function () {
                    const form = document.getElementById('form-jurnal');

                    // Jadwal terkunci (jadwalTerkunci=true) -> select-nya nggak dirender
                    // sama sekali, jadi elemen ini bisa null.
                    const jadwal = document.getElementById('jadwal_id');
                    if (jadwal) {
                        const tampilanMulai = document.getElementById('tampilan-jam-mulai');
                        const inputMulai = document.getElementById('jam_ke_mulai');
                        const tampilanSelesai = document.getElementById('tampilan-jam-selesai');
                        const inputSelesai = document.getElementById('jam_ke_selesai');

                        // Teks "keterangan-jam" (termasuk jam aktualnya, mis. "07:00–08:30")
                        // udah di-render server sesuai jadwal yang kepilih -- nggak perlu
                        // diutak-atik JS di sini. Ganti jadwal lewat dropdown SELALU muat
                        // ulang halaman (lihat listener 'change' di bawah), jadi yang perlu
                        // disinkron JS cuma buat kasus browser auto-select opsi tunggal TANPA
                        // memicu 'change' -- dan di situ pun server udah render value yang
                        // benar dari awal, sync() ini cuma jaga-jaga.
                        function sync() {
                            const opt = jadwal.selectedOptions[0];
                            const mulai = opt?.dataset.mulai;
                            const selesai = opt?.dataset.selesai;
                            if (!mulai) return;

                            tampilanMulai.textContent = 'Jam ke-' + mulai;
                            inputMulai.value = mulai;
                            tampilanSelesai.textContent = 'Jam ke-' + selesai;
                            inputSelesai.value = selesai;
                        }

                        // Ganti jadwal -> presensi kelas yang beda perlu dirender ulang
                        // dari server, jadi muat ulang halaman dengan jadwal itu. Ikut
                        // sisipin status_guru yang LAGI kepilih (dan pertahanin query
                        // lain kayak ?hari=kemarin) -- kalau nggak, pilihan "Tidak
                        // Hadir" yang udah dipilih duluan ke-reset balik ke "Hadir"
                        // begitu halaman render ulang (bug yang sempat dilaporkan).
                        jadwal.addEventListener('change', () => {
                            if (!jadwal.value) return;
                            const params = new URLSearchParams(window.location.search);
                            params.set('jadwal', jadwal.value);
                            const statusGuru = document.querySelector('input[name="status_guru"]:checked')?.value;
                            if (statusGuru) params.set('status_guru', statusGuru);
                            window.location.href = '{{ route('jurnal.create') }}?' + params.toString();
                        });

                        // Browser bisa langsung memilih satu-satunya opsi jadwal saat halaman
                        // dimuat (placeholder "Pilih jadwal" disembunyikan) tanpa memicu event
                        // "change" -- sinkronkan sekali di awal biar jam yang tampil ga meleset.
                        sync();
                    }

                    // Status Kehadiran -> Hadir nampilin Materi+Metode, selain itu
                    // nampilin Tugas Tambahan+Alasan.
                    const blokHadir = document.getElementById('blok-hadir');
                    const blokTidakHadir = document.getElementById('blok-tidak-hadir');
                    function syncStatusGuru() {
                        const val = document.querySelector('input[name="status_guru"]:checked')?.value;
                        const hadir = val === 'hadir';
                        blokHadir.hidden = !hadir;
                        blokTidakHadir.hidden = hadir;

                        // Disable SEMUA field (bukan cuma yang "required") di blok
                        // yang lagi disembunyiin -- dua alasan: (1) atribut
                        // "required" bawaan HTML tetap ngecek elemen yang
                        // disembunyiin lewat ancestor "hidden", nggak otomatis
                        // dikecualiin; (2) dua-duanya sama-sama punya field
                        // name="foto_bukti" (beda id, kamera vs upload biasa) --
                        // kalau yang disembunyiin nggak di-disable, dua-duanya
                        // ikut kesubmit bareng & yang kepakai jadi nggak pasti.
                        blokHadir.querySelectorAll('input, textarea, select').forEach((el) => { el.disabled = !hadir; });
                        blokTidakHadir.querySelectorAll('input, textarea, select').forEach((el) => { el.disabled = hadir; });
                    }
                    document.querySelectorAll('input[name="status_guru"]').forEach((el) => el.addEventListener('change', syncStatusGuru));
                    syncStatusGuru();

                    // "Tidak Hadir 1 Hari Penuh?" -> pilih "Ya" munculin checklist
                    // kelas LANGSUNG di bawahnya (nggak pindah halaman), sekalian
                    // ganti tujuan form ke endpoint massal.
                    const blokMassal = document.getElementById('blok-massal-kelas');
                    const blokPresensi = document.getElementById('blok-presensi');
                    if (blokMassal) {
                        function syncMassal() {
                            const massal = document.querySelector('input[name="tidak_hadir_sehari_penuh"]:checked')?.value === 'ya';
                            blokMassal.hidden = !massal;
                            blokMassal.querySelectorAll('input, textarea, select').forEach((el) => { el.disabled = !massal; });
                            form.action = massal
                                ? '{{ route('jurnal.massal.store') }}'
                                : '{{ route('jurnal.store') }}';

                            // Dua hal ini nggak dipakai sama sekali sama
                            // storeMassal() -- jadwal_id (1 kelas doang) nggak
                            // relevan lagi (ditandai LEBIH dari 1 kelas lewat
                            // checklist di atas), dan presensi cuma bisa diisi
                            // manual kalau guru beneran ada di kelasnya.
                            // "required"-nya jadwal_id ikut nggak ngeblok
                            // submit begitu di-disable.
                            if (jadwal) jadwal.disabled = massal;
                            if (blokPresensi) blokPresensi.hidden = massal;
                        }
                        document.querySelectorAll('input[name="tidak_hadir_sehari_penuh"]').forEach((el) => el.addEventListener('change', syncMassal));
                        syncMassal();

                        // Kartu kelas yang nggak dicentang -> field "tugas khusus"-nya
                        // ikut di-disable, biar nggak ketinggalan kesubmit walau
                        // kelasnya sendiri nggak ditandai. Minimal 1 kelas HARUS
                        // tetap tercentang -- kalau ini yang terakhir, batalin
                        // uncheck-nya (server juga nolak jadwal_ids kosong, tapi
                        // dicegah dari sini biar guru langsung ngerti kenapa).
                        const semuaCheckboxMassal = blokMassal.querySelectorAll('[data-checkbox-jadwal-massal]');
                        semuaCheckboxMassal.forEach((cb) => {
                            cb.addEventListener('change', () => {
                                if (!cb.checked && ! Array.from(semuaCheckboxMassal).some((c) => c.checked)) {
                                    cb.checked = true;
                                    alert('Minimal 1 kelas harus tetap ditandai.');
                                    return;
                                }
                                const khusus = document.getElementById('tugas-khusus-' + cb.value);
                                if (!cb.checked) khusus.querySelector('input').disabled = true;
                            });
                        });

                        // Tombol "Tugas khusus" -> munculin kotak teksnya, TANPA ikut
                        // nge-toggle checkbox kelas (makanya kartu di atas sengaja
                        // bukan <label> tunggal buat semuanya).
                        blokMassal.querySelectorAll('[data-toggle-khusus]').forEach((btn) => {
                            btn.addEventListener('click', () => {
                                const khusus = document.getElementById('tugas-khusus-' + btn.dataset.toggleKhusus);
                                khusus.hidden = false;
                                khusus.querySelector('input').disabled = false;
                                btn.hidden = true;
                                khusus.querySelector('input').focus();
                            });
                        });
                    }

                    // Metode Pembelajaran "Lainnya" -> munculin kotak teks bebas
                    // (wajib diisi kalau "Lainnya" dipilih -- disabled pas
                    // disembunyiin biar required-nya nggak ikut ngeblok submit
                    // pas metode-nya BUKAN "Lainnya").
                    const metodeCustom = document.getElementById('metode_custom_wrap');
                    function syncMetode() {
                        const val = document.querySelector('input[name="metode_pilihan"]:checked')?.value;
                        const lainnya = val === 'lainnya';
                        metodeCustom.hidden = ! lainnya;
                        metodeCustom.querySelector('input').disabled = ! lainnya;
                    }
                    document.querySelectorAll('input[name="metode_pilihan"]').forEach((el) => el.addEventListener('change', syncMetode));
                    syncMetode();
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
