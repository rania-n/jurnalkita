<x-layouts.app title="Presensi Siswa" width="wide">
    <x-page-header
        title="Presensi Siswa"
        subtitle="Catat siswa sakit atau izin dari surat yang diterima piket. Status ini disamakan ke semua jurnal kelas pada tanggal tersebut."
    />

    <form method="GET" action="{{ route('piket.presensi-siswa.index') }}" class="mb-4 grid grid-cols-1 gap-3 rounded-2xl border border-surface-alt bg-card p-4 sm:grid-cols-2">
        <x-ui.cari-pilihan name="kelas_id" label="Kelas" :options="$kelasList" all="Pilih kelas" />
        <x-admin.f-date name="tanggal" label="Tanggal presensi" :value="$tanggal->toDateString()" :max="today()->toDateString()" onchange="this.form.submit()" />
    </form>

    @if ($kelas)
        <form method="POST" action="{{ route('piket.presensi-siswa.store') }}" enctype="multipart/form-data" class="mb-6 flex flex-col gap-4 rounded-2xl border border-surface-alt bg-card p-4 sm:p-5">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">

            @if ($siswas->isEmpty())
                <x-ui.empty icon="school" title="Belum ada siswa aktif di kelas ini" />
            @else
                @if ($presensiTerpilih)
                    <x-alert type="info">Presensi {{ $presensiTerpilih->siswa->nama }} untuk tanggal ini sudah ada. Simpan lagi untuk memperbaruinya.</x-alert>
                @endif

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-ui.cari-pilihan
                        name="siswa_id"
                        label="Siswa"
                        :options="$siswas"
                        :value="request('siswa_id', $presensiTerpilih?->siswa_id)"
                        placeholder="Ketik nama siswa untuk mencari..."
                        required
                    />

                    <x-ui.choice
                        label="Status Kehadiran"
                        name="status"
                        :options="['sakit' => 'Sakit', 'izin' => 'Izin']"
                        :tones="['sakit' => 'sakit', 'izin' => 'izin']"
                        :value="old('status', $presensiTerpilih?->status)"
                        required
                    />

                    <x-ui.textarea label="Catatan" name="catatan" :rows="2" class="sm:col-span-2" placeholder="Contoh: izin keluarga / demam">{{ old('catatan', $presensiTerpilih?->catatan) }}</x-ui.textarea>

                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <x-ui.label for="surat">Surat izin atau bukti (opsional)</x-ui.label>
                        <input id="surat" name="surat" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-xl border border-surface-alt bg-card px-3 py-3 text-sm text-ink file:mr-3 file:rounded-lg file:border-0 file:bg-surface-alt file:px-3 file:py-2 file:font-semibold">
                        @error('surat')<p class="text-xs font-medium text-alpha">{{ $message }}</p>@enderror
                        <p class="text-xs text-muted-2">JPG, PNG, atau PDF maksimal 4 MB.</p>
                        @if ($presensiTerpilih?->surat_path)
                            <a class="text-sm font-semibold text-navy underline" href="{{ Storage::url($presensiTerpilih->surat_path) }}" target="_blank" rel="noopener">Lihat surat yang tersimpan</a>
                        @endif
                    </div>
                </div>

                <x-ui.button type="submit" variant="primary" icon="save" class="w-full sm:w-auto">
                    {{ $presensiTerpilih ? 'Perbarui Presensi' : 'Simpan Presensi' }}
                </x-ui.button>
            @endif
        </form>
    @else
        <x-alert type="info" class="mb-5">Pilih kelas terlebih dahulu untuk menginput surat siswa.</x-alert>
    @endif

    <section>
        <h2 class="mb-3 text-base font-bold text-ink">Catatan tanggal {{ $tanggal->translatedFormat('d M Y') }}</h2>
        @if ($catatanPresensi->isEmpty())
            <x-ui.empty icon="event_busy" title="Belum ada presensi dari piket" />
        @else
            <div class="flex flex-col gap-2">
                @foreach ($catatanPresensi as $catatan)
                    <x-ui.list-card
                        :title="$catatan->siswa->nama"
                        :meta="[$catatan->siswa->kelas->nama . ' · No. ' . ($catatan->siswa->no_absen ?? '—'), $catatan->catatan ?: 'Dicatat oleh ' . ($catatan->dicatatOleh?->name ?? 'piket')]"
                    >
                        <x-slot:badge>
                            <x-ui.status-badge :status="$catatan->status" />
                        </x-slot:badge>
                        <x-slot:actions>
                            <x-ui.action-button
                                label="Ubah"
                                icon="edit"
                                :href="route('piket.presensi-siswa.index', ['tanggal' => $tanggal->toDateString(), 'kelas_id' => $catatan->siswa->kelas_id, 'siswa_id' => $catatan->siswa_id])"
                            />
                            @if ($catatan->surat_path)
                                <x-ui.action-button label="Lihat Surat" icon="description" :href="Storage::url($catatan->surat_path)" target="_blank" />
                            @endif
                        </x-slot:actions>
                    </x-ui.list-card>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
