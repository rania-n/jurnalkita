<x-layouts.app title="Jurnal Pengganti">
    <x-page-header
        title="Isi Jurnal Pengganti"
        subtitle="Untuk guru yang memberi tugas via WA & tidak sempat mengisi sendiri"
        :back="route('sekretaris.jurnal.index')"
    />

    <x-alert type="info" class="mb-4">Hanya untuk status <strong>Tugas Luar</strong> atau <strong>Tidak Hadir</strong>. Jurnal ini otomatis terverifikasi.</x-alert>

    @php
        $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
        $tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];
    @endphp

    @if ($jadwals->isEmpty())
        <x-ui.empty icon="event_busy" title="Tidak ada jadwal kelas ini hari ini" />
    @else
        <form method="POST" action="{{ route('sekretaris.jurnal.pengganti.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.select label="Mata Pelajaran (jadwal)" name="jadwal_id" id="jadwal_id" class="sm:col-span-2">
                    <option value="" disabled selected hidden>Pilih jadwal</option>
                    @foreach ($jadwals as $j)
                        <option value="{{ $j->id }}" data-mulai="{{ $j->jam_ke_mulai }}" data-selesai="{{ $j->jam_ke_selesai }}" @selected(old('jadwal_id') == $j->id)>
                            {{ $j->mapel->nama }} — {{ $j->guru->nama }} (JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }})
                        </option>
                    @endforeach
                </x-ui.select>

                <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai" id="jam_ke_mulai">
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_mulai') == $i)>Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
                <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai" id="jam_ke_selesai">
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_selesai') == $i)>Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
                <p class="-mt-2 text-xs text-muted-2 sm:col-span-2" id="keterangan-jam">Pilih jadwal dulu — jam mulai & selesai otomatis mengikuti jadwal itu. Boleh diubah manual kalau perlu.</p>

                <x-ui.choice
                    label="Status Kehadiran Guru"
                    name="status_guru"
                    class="sm:col-span-2"
                    :options="['tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir']"
                    :tones="['tugas' => 'izin', 'tidak_hadir' => 'alpha']"
                    :value="old('status_guru', 'tugas')"
                />

                <x-ui.textarea label="Materi / Tugas yang diberikan" name="materi" :rows="3" class="sm:col-span-2" placeholder="Contoh: mengerjakan LKS halaman 12–15.">{{ old('materi') }}</x-ui.textarea>
                <x-ui.textarea label="Tugas Tambahan (opsional)" name="tugas_tambahan" :rows="2" class="sm:col-span-2">{{ old('tugas_tambahan') }}</x-ui.textarea>
            </div>

            {{-- Presensi diisi bareng jurnalnya -- kamu yang ada di kelas paling
                 tau siapa yang beneran nggak hadir hari ini, jadi jangan asal
                 ditandai hadir semua. --}}
            <div class="mt-6">
                <h2 class="mb-3 text-sm font-bold text-ink">Presensi ({{ $siswas->count() }} siswa)</h2>
                <p class="-mt-2 mb-3 text-xs text-muted-2">Semua siswa awalnya <strong>Hadir</strong> — ketuk status buat ubah manual kalau ada yang sakit/izin/alpha/dispensasi.</p>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($siswas as $s)
                        <div class="flex flex-col gap-3 rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]">
                            <div class="flex items-center gap-2.5">
                                <x-ui.avatar :label="$s->no_absen ?? '–'" :gender="$s->jenis_kelamin" />
                                <div class="flex min-w-0 flex-col">
                                    <span class="truncate text-sm font-semibold text-ink">{{ $s->nama }}</span>
                                    <span class="text-[11px] font-semibold text-muted-2">NIS: {{ $s->nis }}</span>
                                </div>
                            </div>
                            <x-ui.choice
                                :name="'presensi[' . $s->id . '][status]'"
                                :options="$statuses"
                                :tones="$tones"
                                value="hadir"
                                size="sm"
                            />
                            <x-ui.input :name="'presensi[' . $s->id . '][catatan]'" placeholder="Catatan (opsional)" />
                        </div>
                    @endforeach
                </div>
            </div>

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

                    function sync() {
                        const opt = jadwal.selectedOptions[0];
                        const mulai = opt?.dataset.mulai;
                        const selesai = opt?.dataset.selesai;
                        if (!mulai) return;

                        selectMulai.value = mulai;
                        selectSelesai.value = selesai;
                        keterangan.textContent = 'Jam otomatis ikut jadwal ini (JP ' + mulai + '–' + selesai + '). Boleh diubah manual kalau perlu.';
                    }

                    jadwal.addEventListener('change', sync);
                    sync();
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
