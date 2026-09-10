<x-layouts.app title="Form Jurnal Mengajar">
    <x-page-header
        title="Form Jurnal Mengajar"
        subtitle="Isi jurnal mengajar dan kehadiran siswa"
    />

    <form method="GET" action="{{ route('jurnal.presensi') }}" class="flex flex-col gap-4">

        {{-- Info pengajar & tanggal --}}
        <div class="flex gap-2.5">
            <div class="flex flex-[1.5] items-center gap-2 rounded-xl bg-surface-alt px-3.5 py-2.5">
                <x-icon name="person" :size="16" class="text-navy" />
                <span class="text-[13px] font-semibold text-ink">Winartin, S.Pd</span>
            </div>
            <div class="flex flex-1 items-center gap-2 rounded-xl bg-surface-alt px-3.5 py-2.5">
                <x-icon name="calendar_month" :size="16" class="text-navy" />
                <span class="text-[13px] font-semibold text-ink">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <x-ui.select label="Kelas" name="kelas">
            <option value="" disabled selected hidden>Pilih Kelas (e.g. X RPL 1)</option>
            <option value="x-rpl-1">X RPL 1</option>
            <option value="x-rpl-2">X RPL 2</option>
        </x-ui.select>

        <div class="flex gap-3">
            <x-ui.select label="Jam Mulai" name="jam_mulai" class="flex-1">
                <option value="1">Jam ke-1 · 07:00</option>
                <option value="2">Jam ke-2 · 07:45</option>
                <option value="3">Jam ke-3 · 08:30</option>
            </x-ui.select>
            <x-ui.select label="Jam Selesai" name="jam_selesai" class="flex-1">
                <option value="3">Jam ke-3 · 09:15</option>
                <option value="4">Jam ke-4 · 10:00</option>
                <option value="5">Jam ke-5 · 10:45</option>
            </x-ui.select>
        </div>

        <x-ui.select label="Mata Pelajaran" name="mapel">
            <option value="" disabled selected hidden>Pilih Mata Pelajaran</option>
            <option value="pbo">Pemrograman Berorientasi Objek</option>
            <option value="web">Pemrograman Web</option>
        </x-ui.select>

        <x-ui.segmented
            label="Status Kehadiran Pengajar"
            name="status_pengajar"
            value="hadir"
            :options="['hadir' => 'Hadir', 'tugas' => 'Tugas', 'tidak_hadir' => 'Tidak Hadir']"
        />

        <x-ui.textarea label="Materi" name="materi" :rows="3"
            placeholder="Mempelajari pemrograman modular dan dekomposisi fungsi pada aplikasi mobile..." />

        <x-ui.textarea label="Metode Pembelajaran" name="metode" :rows="2"
            placeholder="Menerangkan, diskusi, ulangan, dll..." />

        <x-ui.textarea label="Tugas Tambahan (jika guru tidak hadir)" name="tugas_tambahan" :rows="2"
            placeholder="Mengerjakan materi halaman..." />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon-after="arrow_forward">Lanjut ke Presensi Siswa</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
