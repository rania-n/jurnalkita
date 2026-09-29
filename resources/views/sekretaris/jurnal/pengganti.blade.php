<x-layouts.app title="Jurnal Pengganti">
    <x-page-header
        title="Isi Jurnal Pengganti"
        subtitle="Catat tugas dari guru yang tidak hadir."
        :back="route('sekretaris.jurnal.index')"
        size="sm"
    />

    <x-alert type="info" class="mb-4">Hanya untuk guru yang <strong>Tidak Hadir</strong> dan tidak sempat mengisi sendiri. Tugas langsung tercatat disetujui.</x-alert>

    {{-- Tata letak & aturan tanggal/jadwal sengaja disamakan sama Isi Jurnal
         biasa (Guru\JurnalController::create()) -- lihat catatan di
         Sekretaris\JurnalController::createPengganti(). --}}
    @if ($modeJurnal === 'bebas_selamanya')
        <div class="mb-4 rounded-2xl border border-surface-alt bg-card p-3 sm:p-4 shadow-[var(--shadow-soft)]">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy">
                        <x-icon name="history_edu" :size="20" />
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-ink">Bebas Isi Jurnal (Tanggal Pilihan Sendiri)</h3>
                        <p class="text-xs text-muted">Pilih tanggal hari ini atau sebelumnya.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <label for="pilih_tanggal_pengganti" class="text-xs font-semibold text-muted whitespace-nowrap">Tanggal:</label>
                    <input
                        type="date"
                        id="pilih_tanggal_pengganti"
                        value="{{ $tanggalAktif->toDateString() }}"
                        max="{{ today()->toDateString() }}"
                        class="rounded-xl border border-surface-alt bg-surface-alt/70 px-3 py-1.5 text-xs font-bold text-ink focus:border-navy focus:outline-none cursor-pointer"
                    >
                </div>
            </div>
        </div>
    @elseif ($modeJurnal === 'bebas_kemarin')
        <div class="mb-4 flex gap-1 rounded-lg border border-surface-alt bg-card p-1">
            <a href="{{ route('sekretaris.jurnal.pengganti') }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold transition-colors', 'bg-navy text-card' => $tanggalAktif->isToday(), 'text-muted-2 hover:text-ink' => ! $tanggalAktif->isToday()])>
                Hari Ini
            </a>
            <a href="{{ route('sekretaris.jurnal.pengganti', ['tanggal' => now()->subDay()->toDateString()]) }}"
               @class(['flex-1 rounded-md px-2.5 py-1.5 text-center text-xs font-semibold transition-colors', 'bg-navy text-card' => ! $tanggalAktif->isToday(), 'text-muted-2 hover:text-ink' => $tanggalAktif->isToday()])>
                Kemarin (Susulan)
            </a>
        </div>
    @endif

    @if ($jumlahSudahDiisi > 0)
        <x-alert type="success" class="mb-4">
            {{ $jumlahSudahDiisi }} jadwal pada {{ $tanggalAktif->isToday() ? 'hari ini' : $tanggalAktif->translatedFormat('d M Y') }} sudah ada jurnalnya — tidak muncul lagi di pilihan bawah.
            <a href="{{ route('sekretaris.jurnal.index', ['dari' => $tanggalAktif->toDateString(), 'sampai' => $tanggalAktif->toDateString()]) }}" class="font-bold underline">Lihat di Riwayat</a>.
        </x-alert>
    @endif

    @if ($jurnalDiblokirIstirahat)
        <x-ui.empty
            icon="hourglass_empty"
            title="Belum waktunya mengisi jurnal"
            desc="Jurnal dapat diisi saat pelajaran berikutnya dimulai."
        />
    @elseif ($jadwals->isEmpty())
        <x-ui.empty
            icon="event_busy"
            :title="$hariTanpaKbm ? $hariKhusus->nama : ($jumlahSudahDiisi > 0 ? 'Semua jadwal sudah diisi' : 'Tidak ada jadwal kelas ini pada tanggal tersebut')"
            :desc="$hariTanpaKbm ? 'KBM dan piket ditiadakan pada tanggal ini.' : ($jumlahSudahDiisi > 0 ? 'Semua jadwal pada tanggal ini sudah diisi. Jika ada yang perlu diubah, buka dari Riwayat.' : null)"
        />
    @else
        <form method="POST" action="{{ route('sekretaris.jurnal.pengganti.store') }}">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggalAktif->toDateString() }}">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @if ($jadwalTerkunci)
                    @php $jamOpsi = \App\Support\Waktu::rentangJam($jadwalTunggalTerkunci->jam_ke_mulai, $jadwalTunggalTerkunci->jam_ke_selesai); @endphp
                    <x-ui.field-static label="Mata Pelajaran (jadwal)" icon="lock_clock" tone="muted" class="sm:col-span-2">
                        {{ $jadwalTunggalTerkunci->mapel->nama }} — {{ $jadwalTunggalTerkunci->guru->nama }} (JP {{ $jadwalTunggalTerkunci->jam_ke_mulai }}–{{ $jadwalTunggalTerkunci->jam_ke_selesai }}{{ $jamOpsi ? " · {$jamOpsi}" : '' }})
                    </x-ui.field-static>
                    <input type="hidden" name="jadwal_id" id="jadwal_id" value="{{ $jadwalTunggalTerkunci->id }}" data-mulai="{{ $jadwalTunggalTerkunci->jam_ke_mulai }}" data-selesai="{{ $jadwalTunggalTerkunci->jam_ke_selesai }}">
                    <p class="-mt-2 text-xs text-muted-2 sm:col-span-2">Otomatis mengikuti jam pelajaran yang sedang berlangsung sekarang.</p>
                @else
                    <x-ui.select label="Mata Pelajaran (jadwal)" name="jadwal_id" id="jadwal_id" class="sm:col-span-2" required>
                        <option value="" disabled selected hidden>Pilih jadwal</option>
                        @foreach ($jadwals as $j)
                            @php $jamOpsi = \App\Support\Waktu::rentangJam($j->jam_ke_mulai, $j->jam_ke_selesai); @endphp
                            <option value="{{ $j->id }}" data-mulai="{{ $j->jam_ke_mulai }}" data-selesai="{{ $j->jam_ke_selesai }}" @selected(old('jadwal_id') == $j->id)>
                                {{ ucfirst($j->hari) }} · {{ $j->mapel->nama }} — {{ $j->guru->nama }} (JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}{{ $jamOpsi ? " · {$jamOpsi}" : '' }})
                            </option>
                        @endforeach
                    </x-ui.select>
                @endif

                <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai" id="jam_ke_mulai">
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_mulai') == $i)>Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
                <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai" id="jam_ke_selesai">
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_selesai') == $i)>Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
                <p class="-mt-2 text-xs text-muted-2 sm:col-span-2" id="keterangan-jam">Pilih jadwal. Sesuaikan jam selesai jika pelajaran berakhir lebih lambat.</p>

                {{-- status_guru nggak lagi dipilih di sini -- pengganti = guru
                     nggak hadir, jadi server selalu simpen 'tidak_hadir'
                     (lihat Sekretaris\JurnalController::storePengganti()).
                     Yang diisi itu tugas untuk siswa.
                     + Alasan (kenapa gurunya nggak hadir), bukan "Materi"
                     (itu khusus kalau gurunya beneran hadir). --}}
                <x-ui.textarea label="Tugas untuk Siswa" name="tugas_tambahan" :rows="3" class="sm:col-span-2" placeholder="Contoh: mengerjakan LKS halaman 12–15." required>{{ old('tugas_tambahan') }}</x-ui.textarea>
                <x-ui.textarea label="Alasan" name="alasan" :rows="2" class="sm:col-span-2" placeholder="Contoh: rapat dinas luar kota, izin sakit, dll." required>{{ old('alasan') }}</x-ui.textarea>
            </div>

            {{-- Presensi diisi bareng jurnalnya -- kamu yang ada di kelas paling
                 tau siapa yang beneran nggak hadir hari ini, jadi jangan asal
                 ditandai hadir semua. --}}
            @include('sekretaris.jurnal._presensi-grid', ['siswas' => $siswas, 'presensiAwal' => $presensiAwal])

            <x-ui.sticky-bar>
                <x-ui.button type="submit" block icon="save">Simpan Jurnal Pengganti</x-ui.button>
            </x-ui.sticky-bar>
        </form>

        @push('scripts')
            <script>
                (function () {
                    const jadwal = document.getElementById('jadwal_id');
                    const selectMulai = document.getElementById('jam_ke_mulai');
                    const selectSelesai = document.getElementById('jam_ke_selesai');
                    const keterangan = document.getElementById('keterangan-jam');
                    const jamPelajaran = @json($jamPelajaranHariIni);

                    // Waktunya (mis. "07:00-08:30") dihitung ulang tiap kali JP mulai/
                    // selesai berubah -- baik otomatis lewat pilih jadwal, maupun manual
                    // lewat 2 select JP di bawahnya (jam di sini emang boleh diubah manual).
                    function tampilkanWaktu() {
                        const jpMulai = jamPelajaran[selectMulai.value];
                        const jpSelesai = jamPelajaran[selectSelesai.value];
                        const waktu = (jpMulai && jpSelesai) ? ` Waktunya ${jpMulai.mulai}–${jpSelesai.selesai}.` : '';
                        keterangan.textContent = 'Jam otomatis mengikuti jadwal yang dipilih.' + waktu + ' Dapat diubah manual jika perlu.';
                    }

                    function sync() {
                        // Jadwal terkunci (mode disiplin) render <input type=hidden>,
                        // bukan <select> -- data-mulai/selesai-nya diambil langsung
                        // dari elemen itu sendiri, bukan dari selectedOptions.
                        const opt = jadwal.tagName === 'SELECT' ? jadwal.selectedOptions[0] : jadwal;
                        const mulai = opt?.dataset.mulai;
                        const selesai = opt?.dataset.selesai;
                        if (!mulai) return;

                        selectMulai.value = mulai;
                        selectSelesai.value = selesai;
                        tampilkanWaktu();
                    }

                    jadwal.addEventListener('change', sync);
                    selectMulai.addEventListener('change', tampilkanWaktu);
                    selectSelesai.addEventListener('change', tampilkanWaktu);
                    sync();

                    // Ganti tanggal (mode bebas_selamanya) -> muat ulang halaman
                    // penuh, sama pola kayak Isi Jurnal biasa.
                    const datePicker = document.getElementById('pilih_tanggal_pengganti');
                    if (datePicker) {
                        datePicker.addEventListener('change', () => {
                            if (!datePicker.value) return;
                            window.location.href = '{{ route('sekretaris.jurnal.pengganti') }}?tanggal=' + datePicker.value;
                        });
                    }
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
