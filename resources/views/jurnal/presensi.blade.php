@php
    // Data contoh — nanti dari controller.
    $siswa = [
        ['no' => '01', 'nama' => 'Ahmad Fauzi', 'nis' => '12345', 'jk' => 'L'],
        ['no' => '02', 'nama' => 'Akana Fizyla', 'nis' => '12346', 'jk' => 'P'],
        ['no' => '03', 'nama' => 'Anjana Fauzia', 'nis' => '12347', 'jk' => 'P'],
        ['no' => '04', 'nama' => 'Bina Fazaya', 'nis' => '12348', 'jk' => 'P'],
        ['no' => '05', 'nama' => 'Celo Cassano', 'nis' => '12349', 'jk' => 'L'],
        ['no' => '06', 'nama' => 'Damar Wicaksono', 'nis' => '12350', 'jk' => 'L'],
    ];
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispen' => 'Dispen'];
@endphp

<x-layouts.app title="Input Presensi Siswa">
    <x-page-header
        title="Form Jurnal Mengajar"
        subtitle="Isi jurnal mengajar dan kehadiran siswa"
        :back="route('jurnal.create')"
    />

    <form method="POST" action="{{ route('jurnal.store') }}" class="flex flex-col gap-4" id="form-presensi">
        @csrf

        <div class="flex flex-col gap-1.5">
            <x-ui.label>Input Presensi Siswa</x-ui.label>
            <x-ui.search-bar name="cari" placeholder="Cari nama atau NIS siswa..." data-filter-siswa />
        </div>

        <p class="text-xs text-muted-2">Semua siswa dianggap <strong>Hadir</strong>. Ketuk status lain hanya untuk yang berhalangan.</p>

        <div class="flex flex-col gap-3" data-daftar-siswa>
            @foreach ($siswa as $s)
                <div class="flex flex-col gap-3 rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]" data-siswa data-nama="{{ strtolower($s['nama']) }}" data-nis="{{ $s['nis'] }}">
                    <div class="flex items-center gap-2.5">
                        <x-ui.avatar :label="$s['no']" :gender="$s['jk']" />
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-ink">{{ $s['nama'] }}</span>
                            <span class="text-[11px] font-semibold text-muted-2">NIS: {{ $s['nis'] }}</span>
                        </div>
                    </div>

                    <div data-segmented class="flex items-center gap-1 rounded-lg bg-surface p-0.5">
                        @foreach ($statuses as $val => $label)
                            <button
                                type="button"
                                data-segment
                                data-presensi
                                value="{{ $val }}"
                                aria-pressed="{{ $val === 'hadir' ? 'true' : 'false' }}"
                                class="flex-1 rounded-md py-1.5 text-[11px] font-semibold text-muted-2 transition-colors aria-pressed:bg-navy aria-pressed:text-card"
                            >{{ $label }}</button>
                        @endforeach
                        <input type="hidden" name="presensi[{{ $s['nis'] }}]" value="hadir" data-segment-value>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Rekap --}}
        <div class="flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
            <x-ui.stat label="Hadir" tone="hadir" data-rekap="hadir" :value="count($siswa)" />
            <x-ui.stat label="Sakit" tone="sakit" data-rekap="sakit" :value="0" />
            <x-ui.stat label="Izin" tone="izin" data-rekap="izin" :value="0" />
            <x-ui.stat label="Alpha" tone="alpha" data-rekap="alpha" :value="0" />
            <x-ui.stat label="Dispen" tone="dispen" data-rekap="dispen" :value="0" />
        </div>

        <x-ui.upload
            label="Lampiran Foto Suasana Kelas"
            name="foto_kelas"
            title="Lampirkan Foto Suasana Kelas"
            hint="Foto bukti pembelajaran sedang berlangsung"
        />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="save">Simpan Jurnal &amp; Absensi</x-ui.button>
        </x-ui.sticky-bar>
    </form>

    @push('scripts')
        <script>
            // Rekap presensi + filter cari (khusus halaman ini)
            const form = document.getElementById('form-presensi');
            function hitungRekap() {
                const c = { hadir: 0, sakit: 0, izin: 0, alpha: 0, dispen: 0 };
                form.querySelectorAll('[data-segment-value]').forEach((i) => { c[i.value] = (c[i.value] || 0) + 1; });
                Object.entries(c).forEach(([k, v]) => {
                    const el = form.querySelector(`[data-rekap="${k}"] span:last-child`);
                    if (el) el.textContent = v;
                });
            }
            form.querySelectorAll('[data-daftar-siswa] [data-segmented]').forEach((g) => {
                g.addEventListener('segment:change', hitungRekap);
            });
            const cari = form.querySelector('[data-filter-siswa]');
            cari?.addEventListener('input', () => {
                const q = cari.value.toLowerCase();
                form.querySelectorAll('[data-siswa]').forEach((row) => {
                    row.hidden = !(row.dataset.nama.includes(q) || row.dataset.nis.includes(q));
                });
            });
        </script>
    @endpush
</x-layouts.app>
