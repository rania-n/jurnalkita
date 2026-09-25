{{--
    Isi popup "Ubah Jurnal" -- nempel di popup "Lihat" yang sama (lihat tombol
    "Ubah Jurnal" di _detail-fragment.blade.php & setFragmentHtml() di
    app.js), bukan halaman/popup terpisah. Nggak ada layout & page-header di
    sini karena ini nempel di dalam modal.

    Submit form-nya native (bukan fetch/AJAX) -- sukses/gagal dua-duanya
    REDIRECT ke Riwayat Jurnal dengan ?lihat=<id> (sukses) atau
    ?lihat=<id>&ubah=1 (gagal validasi, lihat JurnalController@update),
    dua-duanya bikin popup ini otomatis kebuka lagi lewat auto-open di
    guru/jurnal/index.blade.php. Makanya nggak perlu ringkasan-konfirmasi
    kayak Form Jurnal baru -- guru udah lihat detailnya duluan lewat popup
    "Lihat" sebelum pencet "Ubah Jurnal".

    Variabel yang wajib ada di scope pemanggil (lihat JurnalController@editFragment):
      $jurnal, $siswas, $presensiAwal, $jurnalSebelumnya, $alasanTerpilih,
      $metodeLabel, $metodeTerpilih, $metodeCustom, $alasanLabel
--}}
@php
    $statusGuru = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];
    $jamJurnal = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
@endphp

@if ($jurnal->status_verifikasi === 'revisi')
    <x-alert type="error" class="mb-4">
        Pengurus kelas meminta perbaikan: <strong>{{ $jurnal->catatan_verifikasi ?: 'tidak ada catatan' }}</strong>.
        Perbaiki lalu simpan — jurnal akan diperiksa ulang.
    </x-alert>
@endif

<form id="form-ubah-jurnal" method="POST" action="{{ route('jurnal.update', $jurnal) }}" enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {{-- Jam mulai & selesai ikut jadwal, nggak bisa diedit manual (sama
             kayak Form Jurnal baru). --}}
        <x-ui.field-static label="Jam Pelajaran" icon="schedule" tone="muted" class="sm:col-span-2">
            JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}
            @if ($jamJurnal)
                <span class="text-muted-2">· {{ $jamJurnal }}</span>
            @endif
        </x-ui.field-static>
        <input type="hidden" name="jam_ke_selesai" value="{{ $jurnal->jam_ke_selesai }}">

        @if ($jurnalSebelumnya)
            <div class="sm:col-span-2 rounded-xl bg-surface-alt/60 px-3.5 py-2.5 text-xs text-muted">
                <span class="font-semibold text-ink">
                    Terakhir diisi ({{ $jurnalSebelumnya->tanggal->translatedFormat('d M Y') }}{{ $jurnalSebelumnya->status_guru === 'tidak_hadir' ? ', gurunya tidak hadir' : '' }}):
                </span>
                {{ ($jurnalSebelumnya->status_guru === 'hadir' ? $jurnalSebelumnya->materi : $jurnalSebelumnya->tugas_tambahan) ?: '—' }}
            </div>
        @endif

        <x-ui.choice
            label="Status Kehadiran Anda"
            name="status_guru"
            class="sm:col-span-2"
            :options="$statusGuru"
            :tones="['hadir' => 'hadir', 'tidak_hadir' => 'alpha']"
            :value="old('status_guru', $jurnal->status_guru)"
        />
    </div>

    {{-- Foto lama (kalau ada) ditampilin sekali di luar blok hadir/tidak --
         dua-duanya sama-sama bisa punya foto lama yang mau dipertahankan. --}}
    @if ($jurnal->foto_bukti)
        <div class="mt-4 mb-2 flex flex-col gap-1.5">
            <x-ui.label>{{ $jurnal->status_guru === 'hadir' ? 'Foto Suasana Kelas' : 'Surat Izin/Sakit' }} (sudah diunggah)</x-ui.label>
            <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto/surat bukti"
                     class="max-h-56 w-full rounded-xl border border-surface-alt object-cover">
            </a>
        </div>
    @endif

    {{-- Semua field di sini full-width, jadi nggak perlu ikut grid 2-kolom di atas. --}}
    <div id="blok-hadir" class="mt-4 flex flex-col gap-4">
        <x-ui.textarea label="Materi" name="materi" :rows="3" required>{{ old('materi', $jurnal->materi) }}</x-ui.textarea>

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

        <x-ui.upload-kamera
            :label="$jurnal->foto_bukti ? 'Ganti Foto Suasana Kelas (opsional)' : 'Foto Suasana Kelas'"
            name="foto_bukti"
            :placeholder="$jurnal->foto_bukti ? 'Buka kamera buat ganti foto' : 'Wajib buka kamera'"
            :hint="$jurnal->foto_bukti ? 'Opsional — biarin kosong kalau foto lama masih dipakai' : 'Wajib diisi — bukti pembelajaran sedang berlangsung'"
            :required="! $jurnal->foto_bukti"
        />
    </div>

    <div id="blok-tidak-hadir" class="mt-4 flex flex-col gap-4" hidden>
        <x-ui.choice
            label="Alasan"
            name="alasan"
            :options="$alasanLabel"
            :value="$alasanTerpilih"
            required
        />
        <x-ui.textarea label="Tugas untuk Siswa" name="tugas_tambahan" :rows="2" required>{{ old('tugas_tambahan', $jurnal->tugas_tambahan) }}</x-ui.textarea>

        {{-- id BEDA dari yang di blok-hadir (name-nya sama "foto_bukti") --
             lihat catatan lebih detail di guru/jurnal/create.blade.php. --}}
        <x-ui.upload
            id="foto_bukti_tidak_hadir"
            :label="$jurnal->foto_bukti ? 'Ganti Surat Izin/Sakit (opsional)' : 'Surat Izin/Sakit (opsional)'"
            name="foto_bukti"
            title="Lampirkan Surat / Foto Bukti"
            hint="JPG, PNG, atau PDF"
            accept="image/*,application/pdf"
        />
    </div>

    @include('guru.jurnal._presensi-grid')

    <div class="mt-6 flex justify-end">
        <x-ui.button type="submit" icon="save">Simpan Perubahan</x-ui.button>
    </div>
</form>

<script>
    (function () {
        const blokHadir = document.getElementById('blok-hadir');
        const blokTidakHadir = document.getElementById('blok-tidak-hadir');
        function syncStatusGuru() {
            const val = document.querySelector('input[name="status_guru"]:checked')?.value;
            const hadir = val === 'hadir';
            blokHadir.hidden = !hadir;
            blokTidakHadir.hidden = hadir;

            // Disable SEMUA field (bukan cuma yang "required") di blok yang
            // disembunyiin -- selain biar validitas native nggak kesandung,
            // dua-duanya sama-sama punya field name="foto_bukti" (beda id),
            // kalau nggak di-disable dua-duanya ikut kesubmit bareng.
            blokHadir.querySelectorAll('input, textarea, select').forEach((el) => { el.disabled = !hadir; });
            blokTidakHadir.querySelectorAll('input, textarea, select').forEach((el) => { el.disabled = hadir; });
        }
        document.querySelectorAll('input[name="status_guru"]').forEach((el) => el.addEventListener('change', syncStatusGuru));
        syncStatusGuru();

        // Metode Pembelajaran "Lainnya" -> munculin kotak teks bebas (wajib
        // diisi kalau "Lainnya" dipilih -- disabled pas disembunyiin biar
        // required-nya nggak ikut ngeblok submit pas metode-nya BUKAN "Lainnya").
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
