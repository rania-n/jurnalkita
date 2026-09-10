<x-layouts.app title="Form Jurnal Mengajar">
    <x-page-header
        title="Form Jurnal Mengajar"
        subtitle="Isi jurnal mengajar dan kehadiran siswa"
        :back="route('guru.dashboard')"
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

            <x-ui.select label="Kelas & Mata Pelajaran" name="jadwal_id" class="sm:col-span-2">
                <option value="" disabled @selected(! $jadwalTerpilih) hidden>Pilih jadwal</option>
                @foreach ($jadwals as $j)
                    <option value="{{ $j->id }}" @selected($jadwalTerpilih?->id === $j->id)>
                        {{ $j->kelas->nama }} · {{ $j->mapel->nama }} — {{ ucfirst($j->hari) }} JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}
                    </option>
                @endforeach
            </x-ui.select>

            <div>
                <x-ui.field-static label="Jam ke- (mulai)" icon="lock_clock">Jam ke-{{ $jpSekarang }}</x-ui.field-static>
                <input type="hidden" name="jam_ke_mulai" value="{{ $jpSekarang }}">
            </div>
            <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai">
                @for ($i = $jpSekarang; $i <= $jpMaks; $i++)
                    <option value="{{ $i }}" @selected(old('jam_ke_selesai', $jpSekarang) == $i)>Jam ke-{{ $i }}</option>
                @endfor
            </x-ui.select>
            <p class="-mt-2 text-xs text-muted-2 sm:col-span-2">Jam mulai terkunci mengikuti jam pelajaran sekarang (Jam ke-{{ $jpSekarang }}). Atur jam selesai sesuai lama mengajar.</p>

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
    @endif
</x-layouts.app>
