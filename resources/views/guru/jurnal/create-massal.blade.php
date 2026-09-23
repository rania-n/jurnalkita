<x-layouts.app title="Izin/Sakit Massal">
    <x-page-header
        title="Izin/Sakit Hari Ini"
        subtitle="Tandai beberapa kelas sekaligus, nggak perlu bolak-balik isi 1-1"
        :back="route('jurnal.create')"
    />

    @if ($jadwals->isEmpty())
        <x-ui.empty
            icon="event_busy"
            title="Nggak ada jadwal yang bisa ditandai"
            desc="Semua jadwal Anda hari ini sudah ada jurnalnya, atau memang nggak ada jadwal hari ini."
        />
    @else
        <form method="POST" action="{{ route('jurnal.massal.store') }}">
            @csrf

            <x-ui.textarea
                label="Alasan"
                name="alasan"
                :rows="2"
                placeholder="Contoh: sakit, izin keperluan keluarga..."
                required
            >{{ old('alasan') }}</x-ui.textarea>

            <x-ui.textarea
                label="Tugas Tambahan (default -- berlaku ke semua kelas di bawah, kecuali diisi khusus)"
                name="tugas_tambahan_default"
                :rows="3"
                class="mt-4"
                placeholder="Contoh: kerjakan LKS halaman 12-15."
                required
            >{{ old('tugas_tambahan_default') }}</x-ui.textarea>

            <p class="mb-2 mt-5 text-sm font-bold text-ink">Kelas yang ditandai ({{ $jadwals->count() }})</p>
            <p class="mb-3 text-xs text-muted-2">Semua tercentang otomatis -- hapus centang kelas yang nggak mau ikut ditandai (mis. mau diisi manual belakangan).</p>

            <div class="flex flex-col gap-2">
                @foreach ($jadwals as $j)
                    <div class="rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]" data-baris-jadwal-massal>
                        <label class="flex cursor-pointer items-center gap-2.5">
                            <input type="checkbox" name="jadwal_ids[]" value="{{ $j->id }}" checked
                                class="h-4 w-4 shrink-0 rounded border-surface-alt text-navy focus:ring-navy" data-checkbox-jadwal>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-ink">{{ $j->kelas->nama }} · {{ $j->mapel->nama }}</span>
                                <span class="block text-xs text-muted-2">{{ ucfirst($j->hari) }} JP {{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</span>
                            </span>
                        </label>

                        <button
                            type="button"
                            data-toggle-khusus="{{ $j->id }}"
                            class="mt-2 flex items-center gap-1 text-xs font-semibold text-navy hover:underline"
                            @if (old('tugas_khusus.'.$j->id)) hidden @endif
                        >
                            <x-icon name="add_circle" :size="14" />
                            Tugas khusus buat kelas ini
                        </button>

                        <div id="tugas-khusus-{{ $j->id }}" class="mt-2" @unless(old('tugas_khusus.'.$j->id)) hidden @endunless>
                            <x-ui.input
                                name="tugas_khusus[{{ $j->id }}]"
                                placeholder="Tugas khusus (kosongkan buat pakai default di atas)"
                                :value="old('tugas_khusus.'.$j->id)"
                            />
                        </div>
                    </div>
                @endforeach
            </div>

            <x-ui.sticky-bar>
                <x-ui.button type="submit" block icon="save">Simpan Semua</x-ui.button>
            </x-ui.sticky-bar>
        </form>

        @push('scripts')
            <script>
                (function () {
                    document.querySelectorAll('[data-toggle-khusus]').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            const target = document.getElementById('tugas-khusus-' + btn.dataset.toggleKhusus);
                            target.hidden = false;
                            btn.hidden = true;
                            target.querySelector('input')?.focus();
                        });
                    });

                    // Kelas yang nggak dicentang -> matiin field-nya (jadwal_ids[]
                    // & tugas_khusus[id]-nya) biar nggak ikut kesubmit, sama pola
                    // kayak setGrup() di halaman lain (Admin Akun dkk).
                    document.querySelectorAll('[data-baris-jadwal-massal]').forEach((row) => {
                        const checkbox = row.querySelector('[data-checkbox-jadwal]');
                        function sync() {
                            row.querySelectorAll('input[type=text]').forEach((i) => (i.disabled = !checkbox.checked));
                        }
                        checkbox.addEventListener('change', sync);
                        sync();
                    });
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
