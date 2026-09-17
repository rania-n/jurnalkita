@php
    $statusGuru = ['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir'];
    $jamJurnal = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
@endphp

<x-layouts.app title="Ubah Jurnal">
    <x-page-header
        :title="'Ubah ' . $jurnal->jadwal->mapel->nama"
        :subtitle="$jurnal->jadwal->kelas->nama . ' · ' . $jurnal->tanggal->translatedFormat('d M Y')"
        :back="route('jurnal.show', $jurnal)"
    />

    @if ($jurnal->status_verifikasi === 'revisi')
        <x-alert type="error" class="mb-4">
            Pengurus kelas meminta perbaikan: <strong>{{ $jurnal->catatan_verifikasi ?: 'tidak ada catatan' }}</strong>.
            Perbaiki lalu simpan — jurnal akan diperiksa ulang.
        </x-alert>
    @endif

    <form method="POST" action="{{ route('jurnal.update', $jurnal) }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Jam mulai & selesai ikut jadwal, nggak bisa diedit manual (sama
                 kayak Form Jurnal baru). --}}
            <x-ui.field-static label="Jam Pelajaran" icon="schedule" class="sm:col-span-2">
                JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}
                @if ($jamJurnal)
                    <span class="text-muted-2">· {{ $jamJurnal }}</span>
                @endif
            </x-ui.field-static>
            <input type="hidden" name="jam_ke_selesai" value="{{ $jurnal->jam_ke_selesai }}">

            <x-ui.choice
                label="Status Kehadiran Anda"
                name="status_guru"
                class="sm:col-span-2"
                :options="$statusGuru"
                :tones="['hadir' => 'hadir', 'tugas' => 'izin', 'tidak_hadir' => 'alpha']"
                :value="old('status_guru', $jurnal->status_guru)"
            />
        </div>

        {{-- Semua field di sini full-width, jadi nggak perlu ikut grid 2-kolom di atas. --}}
        <div id="blok-hadir" class="mt-4 flex flex-col gap-4">
            <x-ui.textarea label="Materi" name="materi" :rows="3">{{ old('materi', $jurnal->materi) }}</x-ui.textarea>

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
            <x-ui.textarea label="Tugas Tambahan" name="tugas_tambahan" :rows="2">{{ old('tugas_tambahan', $jurnal->tugas_tambahan) }}</x-ui.textarea>
            <x-ui.textarea label="Alasan" name="alasan" :rows="2">{{ old('alasan', $jurnal->alasan) }}</x-ui.textarea>
        </div>

        @include('guru.jurnal._presensi-grid')

        <div class="mt-4">
            @if ($jurnal->foto_bukti)
                <div class="mb-2 flex flex-col gap-1.5">
                    <x-ui.label>Foto Suasana Kelas (sudah diunggah)</x-ui.label>
                    <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                        <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas"
                             class="max-h-56 w-full rounded-xl border border-surface-alt object-cover">
                    </a>
                </div>
            @endif
            <x-ui.upload
                :label="$jurnal->foto_bukti ? 'Ganti Foto Suasana Kelas (opsional)' : 'Foto Suasana Kelas'"
                name="foto_bukti"
                title="Unggah Foto Suasana Kelas"
                :hint="$jurnal->foto_bukti ? 'Opsional — biarin kosong kalau foto lama masih dipakai' : 'Wajib diisi — bukti pembelajaran sedang berlangsung'"
                :required="! $jurnal->foto_bukti"
            />
        </div>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan Perubahan</x-ui.button>
        </x-ui.sticky-bar>
    </form>

    @push('scripts')
        <script>
            (function () {
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
</x-layouts.app>
