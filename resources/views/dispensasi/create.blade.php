<x-layouts.app title="Ajukan Lomba / Izin">
    <x-page-header
        title="Form Pengajuan Lomba / Izin"
        subtitle="Izin meninggalkan sekolah yang harus disetujui Waka"
        :back="route('dispensasi.index')"
    />

    <form method="POST" action="{{ route('dispensasi.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-ui.choice
                name="jenis"
                label="Jenis Pengajuan"
                :options="['izin_keluar' => 'Izin Keluar', 'lomba' => 'Lomba / Dinas']"
                :tones="['izin_keluar' => 'izin', 'lomba' => 'hadir']"
                :value="old('jenis', 'izin_keluar')"
                class="sm:col-span-2"
                required
            />
            <p class="-mt-2 mb-2 text-xs text-muted-2 sm:col-span-2" id="hint-jenis">
                Izin Keluar memerlukan persetujuan Waka lewat WhatsApp. Lomba langsung disetujui tanpa perlu dikirimkan ke Waka.
            </p>

            <x-ui.cari-checkbox
                label="Siswa"
                name="siswa_ids"
                :options="$siswaList"
                hint="Pilih satu atau beberapa siswa yang mengikuti kegiatan yang sama."
                class="sm:col-span-2"
                required
            />

            <x-ui.input label="Tanggal" name="tanggal" type="date" :value="old('tanggal', now()->toDateString())" required />
            <x-ui.input label="Sampai Tanggal (opsional)" name="tanggal_selesai" type="date" :value="old('tanggal_selesai')" />
            <p class="-mt-2 text-xs text-muted-2 sm:col-span-2">Kosongkan kolom ini jika izin hanya berlaku satu hari. Isi tanggal akhir jika izin berlaku beberapa hari.</p>

            <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai">
                <option value="">Sehari penuh</option>
                @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_mulai') == $i)>Jam ke-{{ $i }}</option>@endfor
            </x-ui.select>
            <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai">
                <option value="">Sampai selesai hari itu</option>
                @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_selesai') == $i)>Jam ke-{{ $i }}</option>@endfor
            </x-ui.select>
            <p class="-mt-2 text-xs text-muted-2 sm:col-span-2">
                Kosongkan kedua kolom jika izin berlaku sehari penuh. Jika izin
                dimulai pada jam tertentu hingga akhir kegiatan sekolah, isi jam mulai saja.
            </p>

            <x-ui.textarea label="Alasan / Kegiatan" name="alasan" id="input-alasan" :rows="3" class="sm:col-span-2" placeholder="Contoh: keperluan keluarga." required>{{ old('alasan') }}</x-ui.textarea>

            <x-ui.upload label="Surat / Bukti Pendukung (opsional)" id="input-surat" name="surat" accept="image/*,application/pdf" title="Lampirkan surat atau foto" hint="JPG, PNG, atau PDF" class="sm:col-span-2" />
        </div>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" id="btn-submit" block icon="send">Ajukan ke Waka</x-ui.button>
        </x-ui.sticky-bar>
    </form>

    @push('scripts')
        <script>
            (function () {
                const radios = document.querySelectorAll('input[name="jenis"]');
                const labelAlasan = document.querySelector('label[for="input-alasan"]');
                const textareaAlasan = document.getElementById('input-alasan');
                const btnSubmit = document.getElementById('btn-submit');
                const iconSubmit = btnSubmit?.querySelector('span.material-symbols-rounded');

                function sync() {
                    const val = document.querySelector('input[name="jenis"]:checked')?.value || 'izin_keluar';
                    
                    if (val === 'lomba') {
                        if (labelAlasan) labelAlasan.textContent = 'Nama Lomba / Kegiatan Dinas';
                        if (textareaAlasan) textareaAlasan.placeholder = 'Contoh: Lomba LKS tingkat Provinsi.';
                        if (btnSubmit) {
                            btnSubmit.querySelector('span:not(.material-symbols-rounded)').textContent = 'Simpan & Setujui Langsung';
                            if (iconSubmit) iconSubmit.textContent = 'check_circle';
                        }
                    } else {
                        if (labelAlasan) labelAlasan.textContent = 'Alasan Izin Keluar';
                        if (textareaAlasan) textareaAlasan.placeholder = 'Contoh: keperluan keluarga.';
                        if (btnSubmit) {
                            btnSubmit.querySelector('span:not(.material-symbols-rounded)').textContent = 'Ajukan ke Waka';
                            if (iconSubmit) iconSubmit.textContent = 'send';
                        }
                    }
                }

                radios.forEach((r) => r.addEventListener('change', sync));
                sync();
            })();
        </script>
    @endpush
</x-layouts.app>
