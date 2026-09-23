<x-layouts.app title="Form Jurnal Mengajar">
    <x-page-header
        title="Form Jurnal Mengajar"
        subtitle="Isi jurnal mengajar dan kehadiran siswa dalam satu langkah"
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

            <x-ui.choice
                label="Status Kehadiran Anda"
                name="status_guru"
                class="sm:col-span-2"
                :options="['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir']"
                :tones="['hadir' => 'hadir', 'tidak_hadir' => 'alpha']"
                :value="old('status_guru', 'hadir')"
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
                    />
                    <div id="metode_custom_wrap" hidden>
                        <x-ui.input name="metode_custom" placeholder="Tulis metode lainnya..." value="{{ $metodeCustom }}" />
                    </div>
                </div>
            </div>

            <div id="blok-tidak-hadir" class="mt-4 flex flex-col gap-4" hidden>
                <x-ui.textarea label="Tugas Tambahan" name="tugas_tambahan" :rows="2" placeholder="Kerjakan LKS halaman..." required>{{ old('tugas_tambahan') }}</x-ui.textarea>
                <x-ui.textarea label="Alasan" name="alasan" :rows="2" placeholder="Alasan tidak hadir..." required>{{ old('alasan') }}</x-ui.textarea>
            </div>

            @if ($jadwalTerpilih)
                @include('guru.jurnal._presensi-grid')

                <div class="mt-4">
                    {{-- Wajib jepret langsung dari kamera (nggak boleh unggah dari
                         galeri) -- biar beneran bukti sedang di kelas, bukan foto
                         lama. Jalan di HP maupun PC/laptop (lihat komponennya). --}}
                    <x-ui.upload-kamera
                        label="Foto Suasana Kelas"
                        name="foto_bukti"
                        hint="Wajib diisi — bukti pembelajaran sedang berlangsung"
                        required
                    />
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
                <x-ui.field-static label="Materi / Tugas"><span data-ringkasan="isi">—</span></x-ui.field-static>
                <x-ui.field-static label="Presensi Siswa"><span data-ringkasan="presensi">—</span></x-ui.field-static>
                <x-ui.field-static label="Foto Suasana Kelas"><span data-ringkasan="foto">—</span></x-ui.field-static>
            </div>
            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:gap-3">
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
                        const jadwalSelect = form.querySelector('select[name="jadwal_id"]');
                        const kelasMapel = jadwalSelect
                            ? (jadwalSelect.options[jadwalSelect.selectedIndex]?.textContent.trim() || '—')
                            : (document.querySelector('[data-jadwal-terkunci-teks]')?.textContent.trim() || '—');
                        modalRingkasan.querySelector('[data-ringkasan="kelas-mapel"]').textContent = kelasMapel;

                        const statusGuru = form.querySelector('input[name="status_guru"]:checked')?.value;
                        const hadir = statusGuru === 'hadir';
                        modalRingkasan.querySelector('[data-ringkasan="status-guru"]').textContent = hadir ? 'Hadir' : 'Tidak Hadir';

                        const isiEl = modalRingkasan.querySelector('[data-ringkasan="isi"]');
                        if (hadir) {
                            const materi = form.querySelector('[name="materi"]')?.value.trim();
                            isiEl.textContent = materi || '(belum diisi)';
                        } else {
                            const tugas = form.querySelector('[name="tugas_tambahan"]')?.value.trim();
                            const alasan = form.querySelector('[name="alasan"]')?.value.trim();
                            isiEl.textContent = `Tugas: ${tugas || '(belum diisi)'} — Alasan: ${alasan || '(belum diisi)'}`;
                        }

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

                        const fotoInput = form.querySelector('[data-kamera-input]');
                        modalRingkasan.querySelector('[data-ringkasan="foto"]').textContent =
                            (fotoInput?.files?.length > 0) ? 'Sudah diambil' : 'Belum diambil';
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
                        // dari server, jadi muat ulang halaman dengan jadwal itu.
                        jadwal.addEventListener('change', () => {
                            if (!jadwal.value) return;
                            window.location.href = '{{ route('jurnal.create') }}?jadwal=' + jadwal.value;
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

                        // Atribut "required" bawaan HTML TETAP ngecek elemen yang
                        // disembunyiin lewat ancestor "hidden" (nggak otomatis
                        // dikecualiin kayak dugaan awal) -- kalau nggak dicopot
                        // manual di sini, form nggak akan pernah lolos validitas
                        // native pas blok yang lagi disembunyiin isinya kosong.
                        blokHadir.querySelectorAll('[required]').forEach((el) => { el.disabled = !hadir; });
                        blokTidakHadir.querySelectorAll('[required]').forEach((el) => { el.disabled = hadir; });
                    }
                    document.querySelectorAll('input[name="status_guru"]').forEach((el) => el.addEventListener('change', syncStatusGuru));
                    syncStatusGuru();

                    // Metode Pembelajaran "Lainnya" -> munculin kotak teks bebas.
                    const metodeCustom = document.getElementById('metode_custom_wrap');
                    function syncMetode() {
                        const val = document.querySelector('input[name="metode_pilihan"]:checked')?.value;
                        metodeCustom.hidden = val !== 'lainnya';
                    }
                    document.querySelectorAll('input[name="metode_pilihan"]').forEach((el) => el.addEventListener('change', syncMetode));
                    syncMetode();
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
