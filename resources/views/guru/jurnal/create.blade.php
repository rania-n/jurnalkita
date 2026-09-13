<x-layouts.app title="Form Jurnal Mengajar">
    <x-page-header
        title="Form Jurnal Mengajar"
        subtitle="Isi jurnal mengajar dan kehadiran siswa"
    />

    @if ($jadwals->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada jadwal mengajar" desc="Hubungi admin untuk menambahkan jadwal Anda." />
    @else
        <form method="POST" action="{{ route('jurnal.store') }}">
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
            @endphp

            <x-ui.select label="Kelas & Mata Pelajaran" name="jadwal_id" id="jadwal_id" class="sm:col-span-2">
                <option value="" disabled @selected(! $jadwalTerpilih) hidden>Pilih jadwal</option>
                @foreach ($jadwals as $j)
                    <option value="{{ $j->id }}" data-mulai="{{ $j->jam_ke_mulai }}" data-selesai="{{ $j->jam_ke_selesai }}" @selected($jadwalTerpilih?->id === $j->id)>
                        {{ $j->kelas->nama }} · {{ $j->mapel->nama }} — {{ ucfirst($j->hari) }} JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}
                    </option>
                @endforeach
            </x-ui.select>

            <div>
                <x-ui.field-static label="Jam ke- (mulai)" icon="schedule">
                    <span id="tampilan-jam-mulai">Jam ke-{{ $mulaiAwal }}</span>
                </x-ui.field-static>
                <input type="hidden" name="jam_ke_mulai" id="jam_ke_mulai" value="{{ $mulaiAwal }}">
            </div>
            <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai" id="jam_ke_selesai">
                @for ($i = 1; $i <= $jpMaks; $i++)
                    <option value="{{ $i }}" @selected($selesaiAwal == $i)>Jam ke-{{ $i }}</option>
                @endfor
            </x-ui.select>
            <p class="-mt-2 text-xs text-muted-2 sm:col-span-2" id="keterangan-jam">Pilih jadwal dulu — jam mulai otomatis mengikuti jadwal itu. Atur jam selesai kalau mengajarnya lebih lama.</p>

            <x-ui.choice
                label="Status Kehadiran Anda"
                name="status_guru"
                class="sm:col-span-2"
                :options="['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir']"
                :tones="['hadir' => 'hadir', 'tugas' => 'izin', 'tidak_hadir' => 'alpha']"
                :value="old('status_guru', 'hadir')"
            />

            <x-ui.textarea label="Materi" name="materi" :rows="3" class="sm:col-span-2" placeholder="Materi yang diajarkan...">{{ old('materi') }}</x-ui.textarea>
            <x-ui.textarea label="Metode Pembelajaran" name="metode" :rows="2" placeholder="Ceramah, diskusi, praktik, ulangan, dll...">{{ old('metode') }}</x-ui.textarea>
            <x-ui.textarea label="Tugas Tambahan (jika Anda tidak hadir)" name="tugas_tambahan" :rows="2" placeholder="Kerjakan LKS halaman...">{{ old('tugas_tambahan') }}</x-ui.textarea>
            </div>

            <x-ui.sticky-bar>
                <x-ui.button type="submit" block icon-after="arrow_forward">Simpan &amp; Lanjut Presensi</x-ui.button>
            </x-ui.sticky-bar>
        </form>

        @push('scripts')
            <script>
                (function () {
                    const jadwal = document.getElementById('jadwal_id');
                    const tampilan = document.getElementById('tampilan-jam-mulai');
                    const inputMulai = document.getElementById('jam_ke_mulai');
                    const selectSelesai = document.getElementById('jam_ke_selesai');
                    const keterangan = document.getElementById('keterangan-jam');

                    function sync() {
                        const opt = jadwal.selectedOptions[0];
                        const mulai = opt?.dataset.mulai;
                        const selesai = opt?.dataset.selesai;
                        if (!mulai) return;

                        tampilan.textContent = 'Jam ke-' + mulai;
                        inputMulai.value = mulai;
                        selectSelesai.value = selesai;
                        keterangan.textContent = 'Jam mulai otomatis ikut jadwal ini (Jam ke-' + mulai + '). Atur jam selesai kalau mengajarnya lebih lama.';
                    }

                    jadwal.addEventListener('change', sync);

                    // Browser bisa langsung memilih satu-satunya opsi jadwal saat halaman
                    // dimuat (placeholder "Pilih jadwal" disembunyikan) tanpa memicu event
                    // "change" -- sinkronkan sekali di awal biar jam yang tampil ga meleset.
                    sync();
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
