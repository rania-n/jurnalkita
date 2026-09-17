<x-layouts.app title="Form Jurnal Mengajar">
    <x-page-header
        title="Form Jurnal Mengajar"
        subtitle="Isi jurnal mengajar dan kehadiran siswa dalam satu langkah"
    />

    @if ($jumlahSudahDiisiHariIni > 0)
        {{-- Jadwal yang udah ada jurnalnya hari ini nggak muncul lagi di
             pilihan bawah -- baru bisa diisi ulang kalau jurnalnya dihapus. --}}
        <x-alert type="success" class="mb-4">
            {{ $jumlahSudahDiisiHariIni }} jadwal hari ini sudah Anda isi jurnalnya — nggak muncul lagi di pilihan bawah.
            <a href="{{ route('jurnal.index') }}" class="font-bold underline">Lihat di Riwayat</a>.
        </x-alert>
    @endif

    @if ($jadwals->isEmpty())
        <x-ui.empty
            icon="event_busy"
            :title="$jumlahSudahDiisiHariIni > 0 ? 'Semua jadwal hari ini sudah diisi' : 'Belum ada jadwal mengajar'"
            :desc="$jumlahSudahDiisiHariIni > 0 ? 'Mantap, kelar semua! Kalau ada yang perlu diubah, buka dari Riwayat.' : 'Hubungi admin untuk menambahkan jadwal Anda.'"
        />
    @else
        <form method="POST" action="{{ route('jurnal.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- Info pengajar & tanggal (otomatis) --}}
            <div class="flex items-center gap-2 rounded-xl bg-surface-alt px-3.5 py-3">
                <x-icon name="person" :size="18" class="text-navy" />
                <span class="text-sm font-semibold text-ink">{{ auth()->user()->name }}</span>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-surface-alt px-3.5 py-3">
                <x-icon name="calendar_month" :size="18" class="text-navy" />
                <span class="text-sm font-semibold text-ink">{{ now()->translatedFormat('d M Y') }}</span>
            </div>

            @php
                $mulaiAwal = $jadwalTerpilih->jam_ke_mulai ?? $jpSekarang;
                $selesaiAwal = old('jam_ke_selesai', $jadwalTerpilih->jam_ke_selesai ?? $jpSekarang);
                $hariJadwalTerpilih = $jadwalTerpilih->hari ?? \App\Support\HariSekolah::hariIni();
                $jamAwal = $hariJadwalTerpilih ? \App\Support\Waktu::rentangJamUntukHari($hariJadwalTerpilih, $mulaiAwal, $selesaiAwal) : null;
            @endphp

            @if ($jadwalTerkunci)
                <x-ui.field-static label="Kelas & Mata Pelajaran" icon="lock_clock" class="sm:col-span-2">
                    {{ $jadwalTerpilih->kelas->nama }} · {{ $jadwalTerpilih->mapel->nama }} — JP {{ $jadwalTerpilih->jam_ke_mulai }}–{{ $jadwalTerpilih->jam_ke_selesai }}
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
                <x-ui.select label="Kelas & Mata Pelajaran" name="jadwal_id" id="jadwal_id" class="sm:col-span-2">
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
                <x-ui.field-static label="Jam ke- (mulai)" icon="schedule">
                    <span id="tampilan-jam-mulai">Jam ke-{{ $mulaiAwal }}</span>
                </x-ui.field-static>
                <input type="hidden" name="jam_ke_mulai" id="jam_ke_mulai" value="{{ $mulaiAwal }}">
            </div>
            <div>
                <x-ui.field-static label="Jam ke- (selesai)" icon="schedule">
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
                :options="['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir']"
                :tones="['hadir' => 'hadir', 'tugas' => 'izin', 'tidak_hadir' => 'alpha']"
                :value="old('status_guru', 'hadir')"
            />
            </div>

            {{-- Semua field di sini full-width (sm:col-span-2), jadi nggak perlu ikut
                 grid 2-kolom di atas -- aman langsung disembunyikan/ditampilkan. --}}
            <div id="blok-hadir" class="mt-4 flex flex-col gap-4">
                <x-ui.textarea label="Materi" name="materi" :rows="3" placeholder="Materi yang diajarkan...">{{ old('materi') }}</x-ui.textarea>

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
                <x-ui.textarea label="Tugas Tambahan" name="tugas_tambahan" :rows="2" placeholder="Kerjakan LKS halaman...">{{ old('tugas_tambahan') }}</x-ui.textarea>
                <x-ui.textarea label="Alasan" name="alasan" :rows="2" placeholder="Alasan tidak hadir / tugas luar...">{{ old('alasan') }}</x-ui.textarea>
            </div>

            @if ($jadwalTerpilih)
                @include('guru.jurnal._presensi-grid')

                <div class="mt-4">
                    {{-- Nggak dikunci ke kamera (capture) lagi -- guru bebas pilih
                         foto dari galeri atau motret langsung, browser yang
                         nawarin pilihannya. --}}
                    <x-ui.upload
                        label="Foto Suasana Kelas"
                        name="foto_bukti"
                        title="Unggah Foto Suasana Kelas"
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
                        blokHadir.hidden = val !== 'hadir';
                        blokTidakHadir.hidden = val === 'hadir';
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
