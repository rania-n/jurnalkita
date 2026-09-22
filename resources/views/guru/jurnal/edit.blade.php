@php
    $statusGuru = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];
    $jamJurnal = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
@endphp

<x-layouts.app title="Ubah Jurnal">
    <x-page-header
        :title="'Ubah ' . $jurnal->jadwal->mapel->nama"
        :subtitle="$jurnal->jadwal->kelas->nama . ' · ' . $jurnal->tanggal->translatedFormat('d M Y')"
        :back="route('jurnal.index', ['lihat' => $jurnal->id])"
    />

    @if ($jurnal->status_verifikasi === 'revisi')
        <x-alert type="error" class="mb-4">
            Pengurus kelas meminta perbaikan: <strong>{{ $jurnal->catatan_verifikasi ?: 'tidak ada catatan' }}</strong>.
            Perbaiki lalu simpan — jurnal akan diperiksa ulang.
        </x-alert>
    @endif

    <form id="form-jurnal" method="POST" action="{{ route('jurnal.update', $jurnal) }}" enctype="multipart/form-data">
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

            <x-ui.choice
                label="Status Kehadiran Anda"
                name="status_guru"
                class="sm:col-span-2"
                :options="$statusGuru"
                :tones="['hadir' => 'hadir', 'tidak_hadir' => 'alpha']"
                :value="old('status_guru', $jurnal->status_guru)"
            />
        </div>

        {{-- Semua field di sini full-width, jadi nggak perlu ikut grid 2-kolom di atas. --}}
        <div id="blok-hadir" class="mt-4 flex flex-col gap-4">
            <x-ui.textarea label="Materi" name="materi" :rows="3" required>{{ old('materi', $jurnal->materi) }}</x-ui.textarea>

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
            <x-ui.textarea label="Tugas Tambahan" name="tugas_tambahan" :rows="2" required>{{ old('tugas_tambahan', $jurnal->tugas_tambahan) }}</x-ui.textarea>
            <x-ui.textarea label="Alasan" name="alasan" :rows="2" required>{{ old('alasan', $jurnal->alasan) }}</x-ui.textarea>
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
            <x-ui.upload-kamera
                :label="$jurnal->foto_bukti ? 'Ganti Foto Suasana Kelas (opsional)' : 'Foto Suasana Kelas'"
                name="foto_bukti"
                :placeholder="$jurnal->foto_bukti ? 'Buka kamera buat ganti foto' : 'Wajib buka kamera'"
                :hint="$jurnal->foto_bukti ? 'Opsional — biarin kosong kalau foto lama masih dipakai' : 'Wajib diisi — bukti pembelajaran sedang berlangsung'"
                :required="! $jurnal->foto_bukti"
            />
        </div>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan Perubahan</x-ui.button>
        </x-ui.sticky-bar>
    </form>

    {{-- Ringkasan sebelum beneran terkirim -- sama pola kayak Form Jurnal baru. --}}
    <x-ui.modal id="modal-ringkasan-jurnal" title="Cek Dulu Sebelum Kirim">
        <div class="flex flex-col gap-3 text-sm">
            <x-ui.field-static label="Kelas & Mata Pelajaran">{{ $jurnal->jadwal->kelas->nama }} · {{ $jurnal->jadwal->mapel->nama }}</x-ui.field-static>
            <x-ui.field-static label="Status Kehadiran Anda"><span data-ringkasan="status-guru">—</span></x-ui.field-static>
            <x-ui.field-static label="Materi / Tugas"><span data-ringkasan="isi">—</span></x-ui.field-static>
            <x-ui.field-static label="Presensi Siswa"><span data-ringkasan="presensi">—</span></x-ui.field-static>
            <x-ui.field-static label="Foto Suasana Kelas"><span data-ringkasan="foto">—</span></x-ui.field-static>
        </div>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:gap-3">
            <x-ui.button type="button" id="tombol-kirim-jurnal" icon="send" class="flex-1">Sudah Benar, Kirim</x-ui.button>
            <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Cek Lagi</x-ui.button>
        </div>
    </x-ui.modal>

    @push('scripts')
        <script>
            (function () {
                const form = document.getElementById('form-jurnal');
                const modalRingkasan = document.getElementById('modal-ringkasan-jurnal');
                if (form && modalRingkasan) {
                    let dikonfirmasi = false;

                    function isiRingkasan() {
                        const statusGuru = form.querySelector('input[name="status_guru"]:checked')?.value;
                        const hadir = statusGuru === 'hadir';
                        modalRingkasan.querySelector('[data-ringkasan="status-guru"]').textContent = hadir ? 'Hadir' : 'Tidak Hadir';

                        const isiEl = modalRingkasan.querySelector('[data-ringkasan="isi"]');
                        if (hadir) {
                            const materi = form.querySelector('[name="materi"]')?.value.trim();
                            isiEl.textContent = materi || '(belum diisi)';
                        } else {
                            const tugas = form.querySelector('[name="tugas_tambahan"]')?.value.trim();
                            const alasan = form.querySelector('[name="alasan"]')?.value.trim();
                            isiEl.textContent = `Tugas: ${tugas || '(belum diisi)'} — Alasan: ${alasan || '(belum diisi)'}`;
                        }

                        const rekap = {};
                        form.querySelectorAll('[data-siswa-row] input[type="radio"]:checked').forEach((r) => {
                            rekap[r.value] = (rekap[r.value] || 0) + 1;
                        });
                        const label = { hadir: 'Hadir', sakit: 'Sakit', izin: 'Izin', alpha: 'Alpha', dispensasi: 'Dispensasi' };
                        const totalSiswa = form.querySelectorAll('[data-siswa-row]').length;
                        const ringkasPresensi = Object.entries(rekap)
                            .filter(([, n]) => n > 0)
                            .map(([k, n]) => `${label[k] || k} ${n}`)
                            .join(', ');
                        modalRingkasan.querySelector('[data-ringkasan="presensi"]').textContent =
                            totalSiswa ? `${ringkasPresensi || '—'} (dari ${totalSiswa} siswa)` : '—';

                        const fotoInput = form.querySelector('[data-kamera-input]');
                        const fotoBaru = fotoInput?.files?.length > 0;
                        modalRingkasan.querySelector('[data-ringkasan="foto"]').textContent = fotoBaru
                            ? 'Foto baru diambil'
                            : (@json((bool) $jurnal->foto_bukti) ? 'Pakai foto lama' : 'Belum diambil');
                    }

                    form.addEventListener('submit', (e) => {
                        if (dikonfirmasi) return;
                        e.preventDefault();
                        isiRingkasan();
                        modalRingkasan.showModal();
                    });

                    document.getElementById('tombol-kirim-jurnal')?.addEventListener('click', () => {
                        dikonfirmasi = true;
                        modalRingkasan.close();
                        form.requestSubmit();
                    });
                }
            })();

            (function () {
                // Status Kehadiran -> Hadir nampilin Materi+Metode, selain itu
                // nampilin Tugas Tambahan+Alasan.
                const blokHadir = document.getElementById('blok-hadir');
                const blokTidakHadir = document.getElementById('blok-tidak-hadir');
                function syncStatusGuru() {
                    const val = document.querySelector('input[name="status_guru"]:checked')?.value;
                    const hadir = val === 'hadir';
                    blokHadir.hidden = !hadir;
                    blokTidakHadir.hidden = hadir;

                    // "required" bawaan HTML tetap ngecek elemen yang disembunyiin
                    // lewat ancestor "hidden" -- dicopot manual (via disabled)
                    // biar form bisa lolos validitas native pas blok yang lagi
                    // disembunyiin isinya kosong.
                    blokHadir.querySelectorAll('[required]').forEach((el) => { el.disabled = !hadir; });
                    blokTidakHadir.querySelectorAll('[required]').forEach((el) => { el.disabled = hadir; });
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
