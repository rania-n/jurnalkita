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
      $siswas       : Collection<Siswa>, urut no_absen
      $presensiAwal : array [siswa_id => ['status' => ..., 'catatan' => ...]] (opsional)
--}}
@php
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];
@endphp

<div class="mt-6">
    <h2 class="mb-1 text-sm font-bold text-ink">Presensi ({{ $siswas->count() }} siswa)</h2>
    <p class="mb-3 text-xs text-muted-2">Semua siswa otomatis <strong>Hadir</strong> (kecuali yang udah otomatis kesorot dari jurnal lain di bawah). Ketik nama buat cari & tandai yang Sakit/Izin/Alpha/Dispensasi.</p>

    {{-- Dropdown beneran (bukan filter kartu langsung), sama pola kayak versi
         Guru -- lihat catatan lebih detail di guru/jurnal/_presensi-grid.blade.php. --}}
    <div class="relative">
        <x-ui.search-bar id="cari-siswa-pengganti" placeholder="Cari nama siswa yang tidak hadir..." />
        <div
            id="hasil-cari-siswa-pengganti"
            hidden
            class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-surface-alt bg-card py-1 shadow-lg"
        ></div>
    </div>

    <div class="mt-2 flex items-center justify-between gap-2">
        <p class="text-xs text-muted-2" id="jumlah-tampil-pengganti"></p>
        <label class="flex shrink-0 cursor-pointer items-center gap-1.5 text-xs font-semibold text-navy">
            <input type="checkbox" id="tampilkan-semua-siswa-pengganti" class="h-3.5 w-3.5 rounded border-surface-alt text-navy focus:ring-navy">
            Tampilkan semua siswa
        </label>
    </div>

    <div class="mt-2 max-h-[38vh] overflow-y-auto rounded-2xl border border-surface-alt bg-surface-alt/40 p-3 sm:max-h-[50vh]">
        {{-- Sama alasannya kayak versi Guru -- "grid-fill-last" CSS nggak
             sadar kartu yang `hidden` lewat JS, jadi stretch-nya diakalin
             manual lewat JS (lihat refresh() di bawah). --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" data-grid-presensi>
            @foreach ($siswas as $s)
                @php
                    $isiAwal = ($presensiAwal ?? [])[$s->id] ?? ['status' => 'hadir', 'catatan' => null];
                    $statusAwal = old("presensi.{$s->id}.status", $isiAwal['status']);
                    $catatanAwal = old("presensi.{$s->id}.catatan", $isiAwal['catatan']);
                    $catatanId = 'catatan-pengganti-'.$s->id;
                    // Beri tahu asalnya kenapa status/catatan udah keisi duluan
                    // (bukan "Hadir" polos) -- dispensasi ATAU ikutan jurnal lain
                    // hari ini di kelas yang sama, lihat PresensiDefault.
                    $keteranganAwal = match (true) {
                        $statusAwal === 'dispensasi' && str_starts_with((string) $catatanAwal, 'Dispensasi') => 'Dispensasi disetujui hari ini',
                        $statusAwal !== 'hadir' && isset(($presensiAwal ?? [])[$s->id]) => 'Ikut jurnal lain hari ini di kelas ini',
                        default => null,
                    };
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
                            @if ($keteranganAwal)
                                <span class="flex items-center gap-1 text-[11px] font-semibold text-dispen">
                                    <x-icon name="verified" :size="12" />
                                    {{ $keteranganAwal }}
                                </span>
                            @endif
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
            const hasil = document.getElementById('hasil-cari-siswa-pengganti');
            const rows = Array.from(document.querySelectorAll('[data-siswa-row-pengganti]'));
            const counter = document.getElementById('jumlah-tampil-pengganti');
            const tampilkanSemua = document.getElementById('tampilkan-semua-siswa-pengganti');
            const dipilihManual = new Set();

            cari?.closest('form')?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && (e.target.type === 'text' || e.target.type === 'search')) {
                    e.preventDefault();
                }
            });

            function statusRow(row) {
                return row.querySelector('input[type="radio"]:checked')?.value ?? 'hadir';
            }

            function namaAsli(row) {
                return row.querySelector('.text-ink')?.textContent.trim() ?? '';
            }

            function renderHasilCari(q) {
                if (!hasil) return;
                if (!q) {
                    hasil.hidden = true;
                    return;
                }
                const cocok = rows.filter((row) => row.dataset.nama.includes(q) || row.dataset.noAbsen.includes(q));
                if (cocok.length === 0) {
                    hasil.innerHTML = '<p class="px-3.5 py-2.5 text-sm text-muted-2">Tidak ada siswa yang cocok.</p>';
                } else {
                    hasil.innerHTML = cocok.slice(0, 30).map((row, i) => `
                        <button type="button" data-pilih-hasil="${i}" class="flex w-full flex-col gap-0.5 px-3.5 py-2.5 text-left hover:bg-surface-alt">
                            <span class="text-sm font-semibold text-ink">${namaAsli(row)}</span>
                            <span class="text-xs text-muted-2">No. ${row.dataset.noAbsen} · ${statusRow(row) === 'hadir' ? 'Hadir' : 'Sudah ditandai'}</span>
                        </button>
                    `).join('');
                    hasil.querySelectorAll('[data-pilih-hasil]').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            const row = cocok[Number(btn.dataset.pilihHasil)];
                            dipilihManual.add(row);
                            cari.value = '';
                            hasil.hidden = true;
                            refresh();
                            row.scrollIntoView({ block: 'center', behavior: 'smooth' });
                            row.querySelector('input[type="radio"]')?.focus();
                        });
                    });
                }
                hasil.hidden = false;
            }

            function refresh() {
                const semua = tampilkanSemua?.checked;
                let tidakHadir = 0;

                rows.forEach((row) => {
                    const statusNyaTidakHadir = statusRow(row) !== 'hadir';
                    if (statusNyaTidakHadir) tidakHadir++;
                    row.hidden = !(semua || statusNyaTidakHadir || dipilihManual.has(row));
                    row.classList.remove('lg:col-span-2');
                });

                const tampil = rows.filter((row) => !row.hidden);
                if (tampil.length % 2 === 1) {
                    tampil[tampil.length - 1].classList.add('lg:col-span-2');
                }

                if (semua) {
                    counter.textContent = `Menampilkan semua ${rows.length} siswa`;
                } else {
                    counter.textContent = tidakHadir > 0
                        ? `${tidakHadir} siswa ditandai tidak hadir (dari ${rows.length} siswa)`
                        : `Semua ${rows.length} siswa Hadir`;
                }
            }

            cari?.addEventListener('input', () => renderHasilCari(cari.value.trim().toLowerCase()));
            cari?.addEventListener('focus', () => { if (cari.value.trim()) renderHasilCari(cari.value.trim().toLowerCase()); });
            document.addEventListener('click', (e) => {
                if (hasil && !hasil.hidden && !e.target.closest('#hasil-cari-siswa-pengganti') && e.target !== cari) {
                    hasil.hidden = true;
                }
            });
            tampilkanSemua?.addEventListener('change', refresh);
            rows.forEach((row) => {
                row.querySelectorAll('input[type="radio"]').forEach((r) => r.addEventListener('change', refresh));
            });

            refresh();

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
