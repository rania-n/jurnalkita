@php
    $statusGuru = ['hadir' => 'Hadir', 'tugas' => 'Tugas Luar', 'tidak_hadir' => 'Tidak Hadir'];
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
            <x-ui.field-static label="Jam ke- (mulai)" icon="lock_clock">Jam ke-{{ $jurnal->jam_ke_mulai }}</x-ui.field-static>
            <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai">
                @for ($i = $jurnal->jam_ke_mulai; $i <= 13; $i++)
                    <option value="{{ $i }}" @selected(old('jam_ke_selesai', $jurnal->jam_ke_selesai) == $i)>Jam ke-{{ $i }}</option>
                @endfor
            </x-ui.select>

            <x-ui.choice
                label="Status Kehadiran Anda"
                name="status_guru"
                class="sm:col-span-2"
                :options="$statusGuru"
                :tones="['hadir' => 'hadir', 'tugas' => 'izin', 'tidak_hadir' => 'alpha']"
                :value="old('status_guru', $jurnal->status_guru)"
            />

            <x-ui.textarea label="Materi" name="materi" :rows="3" class="sm:col-span-2">{{ old('materi', $jurnal->materi) }}</x-ui.textarea>
            <x-ui.textarea label="Metode Pembelajaran" name="metode" :rows="2">{{ old('metode', $jurnal->metode) }}</x-ui.textarea>
            <x-ui.textarea label="Tugas Tambahan" name="tugas_tambahan" :rows="2">{{ old('tugas_tambahan', $jurnal->tugas_tambahan) }}</x-ui.textarea>
        </div>

        @include('guru.jurnal._presensi-grid')

        <div class="mt-4 max-w-sm">
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
                :label="$jurnal->foto_bukti ? 'Ganti Foto Suasana Kelas (opsional)' : 'Foto Suasana Kelas (opsional)'"
                name="foto_bukti"
                title="Lampirkan Foto Suasana Kelas"
                hint="Bukti pembelajaran sedang berlangsung"
            />
        </div>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan Perubahan</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
