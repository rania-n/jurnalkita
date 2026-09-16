{{--
    Partial presensi buat Isi Jurnal Pengganti (Pengurus Kelas) -- pola sama
    persis kayak resources/views/guru/jurnal/_presensi-grid.blade.php (kotak
    cari + batas scroll + catatan disembunyikan di balik tombol), cuma dibuat
    partial terpisah karena bedanya:
      - Nama siswa nggak di-link (route guru.siswa.show cuma buat role guru/waka,
        pengurus kelas nggak punya akses ke situ).
      - Nggak ada auto-isi dispensasi (createPengganti() controller nggak
        pernah pre-fill dari data dispensasi kayak punya Guru).

    Variabel yang wajib ada di scope pemanggil:
      $siswas : Collection<Siswa>, urut no_absen
--}}
@php
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];
@endphp

<div class="mt-6">
    <h2 class="mb-1 text-sm font-bold text-ink">Presensi ({{ $siswas->count() }} siswa)</h2>
    <p class="mb-3 text-xs text-muted-2">Semua siswa awalnya <strong>Hadir</strong> — ketuk status buat ubah manual kalau ada yang sakit/izin/alpha/dispensasi.</p>

    <div class="flex h-11 items-center gap-2 rounded-xl bg-surface-alt px-4">
        <x-icon name="search" :size="18" class="shrink-0 text-muted" />
        <input
            type="text"
            id="cari-siswa-pengganti"
            placeholder="Cari nama atau no. absen..."
            class="w-full border-none bg-transparent text-sm text-ink outline-none placeholder:text-muted"
        >
    </div>

    <div class="mt-2 flex items-center justify-between gap-2">
        <p class="text-xs text-muted-2" id="jumlah-tampil-pengganti">Menampilkan {{ $siswas->count() }} dari {{ $siswas->count() }} siswa</p>
        <p class="flex items-center gap-1 text-xs text-muted-2">
            <x-icon name="unfold_more" :size="14" />
            Geser ke bawah untuk siswa lainnya
        </p>
    </div>

    <div class="mt-2 max-h-[38vh] overflow-y-auto rounded-2xl border border-surface-alt bg-surface-alt/40 p-3 sm:max-h-[50vh]">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            @foreach ($siswas as $s)
                @php
                    $statusAwal = old("presensi.{$s->id}.status", 'hadir');
                    $catatanAwal = old("presensi.{$s->id}.catatan");
                    $catatanId = 'catatan-pengganti-'.$s->id;
                @endphp
                <div
                    class="flex flex-col gap-2.5 rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]"
                    data-siswa-row-pengganti
                    data-nama="{{ strtolower($s->nama) }}"
                    data-no-absen="{{ strtolower((string) $s->no_absen) }}"
                >
                    <div class="flex items-center gap-2.5">
                        <x-ui.avatar :label="$s->no_absen ?? '–'" :gender="$s->jenis_kelamin" />
                        <div class="flex min-w-0 flex-col">
                            <span class="truncate text-sm font-semibold text-ink">{{ $s->nama }}</span>
                            <span class="text-[11px] font-semibold text-muted-2">NIS: {{ $s->nis }}</span>
                        </div>
                    </div>

                    <x-ui.choice
                        :name="'presensi[' . $s->id . '][status]'"
                        :options="$statuses"
                        :tones="$tones"
                        :value="$statusAwal"
                        size="sm"
                    />

                    <div class="border-t border-surface-alt pt-2.5">
                        <button
                            type="button"
                            data-toggle-catatan="{{ $catatanId }}"
                            class="flex items-center gap-1 text-xs font-semibold text-navy hover:underline"
                            @if ($catatanAwal) hidden @endif
                        >
                            <x-icon name="add_circle" :size="14" />
                            Tambah catatan
                        </button>

                        <div id="{{ $catatanId }}" @unless($catatanAwal) hidden @endunless>
                            <x-ui.input
                                :name="'presensi[' . $s->id . '][catatan]'"
                                placeholder="Catatan (opsional)"
                                :value="$catatanAwal"
                            >
                                <button type="button" data-close-catatan="{{ $catatanId }}" class="flex shrink-0 items-center text-muted-2 hover:text-ink" tabindex="-1" aria-label="Tutup catatan">
                                    <x-icon name="close" :size="18" />
                                </button>
                            </x-ui.input>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const cari = document.getElementById('cari-siswa-pengganti');
            const rows = document.querySelectorAll('[data-siswa-row-pengganti]');
            const counter = document.getElementById('jumlah-tampil-pengganti');

            cari?.closest('form')?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && (e.target.type === 'text' || e.target.type === 'search')) {
                    e.preventDefault();
                }
            });

            cari?.addEventListener('input', () => {
                const q = cari.value.trim().toLowerCase();
                let tampil = 0;
                rows.forEach((row) => {
                    const cocok = !q || row.dataset.nama.includes(q) || row.dataset.noAbsen.includes(q);
                    row.hidden = !cocok;
                    if (cocok) tampil++;
                });
                counter.textContent = `Menampilkan ${tampil} dari ${rows.length} siswa`;
            });

            document.querySelectorAll('[data-toggle-catatan]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = document.getElementById(btn.dataset.toggleCatatan);
                    target.hidden = false;
                    btn.hidden = true;
                    target.querySelector('input')?.focus();
                });
            });

            document.querySelectorAll('[data-close-catatan]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const wrapId = btn.dataset.closeCatatan;
                    const wrap = document.getElementById(wrapId);
                    const input = wrap.querySelector('input');
                    if (input) input.value = '';
                    wrap.hidden = true;
                    document.querySelector(`[data-toggle-catatan="${wrapId}"]`).hidden = false;
                });
            });
        })();
    </script>
@endpush
