{{--
    Partial dipakai bareng Form Jurnal (create) & Ubah Jurnal (edit) -- daftar
    presensi per siswa, satu form dengan field jurnal lainnya (bukan langkah
    terpisah lagi).

    Variabel yang wajib ada di scope pemanggil:
      $siswas       : Collection<Siswa>, urut no_absen
      $presensiAwal : array [siswa_id => ['status' => ..., 'catatan' => ...]]
--}}
@php
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];
@endphp

<div class="mt-6">
    <h2 class="mb-1 text-sm font-bold text-ink">Presensi ({{ $siswas->count() }} siswa)</h2>
    <p class="mb-3 text-xs text-muted-2">Status otomatis ikut jurnal lain hari ini di kelas ini (atau dispensasi yang disetujui) kalau ada, sisanya <strong>Hadir</strong>. Ketuk status buat ubah manual bila perlu.</p>

    <x-ui.search-bar id="cari-siswa" placeholder="Cari nama atau no. absen..." />

    <div class="mt-2 flex items-center justify-between gap-2">
        <p class="text-xs text-muted-2" id="jumlah-tampil">Menampilkan {{ $siswas->count() }} dari {{ $siswas->count() }} siswa</p>
        <p class="flex items-center gap-1 text-xs text-muted-2">
            <x-icon name="unfold_more" :size="14" />
            Geser ke bawah untuk siswa lainnya
        </p>
    </div>

    {{-- Kotak sendiri -- siswanya di-scroll/dicari di dalam sini, bukan numpuk
         jadi satu halaman panjang. Lebih pendek di HP (layarnya udah sempit,
         separuh layar kerasa kebesaran), agak lega lagi di layar lebih lebar. --}}
    <div class="mt-2 max-h-[38vh] overflow-y-auto rounded-2xl border border-surface-alt bg-surface-alt/40 p-3 sm:max-h-[50vh]">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            @foreach ($siswas as $s)
                @php
                    $isiAwal = $presensiAwal[$s->id] ?? ['status' => 'hadir', 'catatan' => null];
                    $statusAwal = old("presensi.{$s->id}.status", $isiAwal['status']);
                    $catatanAwal = old("presensi.{$s->id}.catatan", $isiAwal['catatan']);
                    $dariDispensasiOtomatis = $statusAwal === 'dispensasi' && str_starts_with((string) $catatanAwal, 'Dispensasi');
                    $dariJurnalLain = ! $dariDispensasiOtomatis && $statusAwal !== 'hadir' && isset($presensiAwal[$s->id]);
                    $catatanId = 'catatan-'.$s->id;
                    // Hadir itu default buat hampir semua siswa -- nunjukkin
                    // 5 tombol status penuh tiap baris kerasa berisik. Kalau
                    // statusnya emang masih default Hadir (bukan hasil ikut
                    // dispensasi/jurnal lain), collapse jadi teks doang +
                    // tombol "Ubah"; baru pas diklik tombol pilihan lengkap
                    // (yang tetap radio asli, bukan disimulasikan) kebuka.
                    $statusRingkas = $statusAwal === 'hadir' && ! $dariDispensasiOtomatis && ! $dariJurnalLain;
                    $statusToggleId = 'status-pilihan-'.$s->id;
                @endphp
                <div
                    class="flex flex-col gap-2.5 rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]"
                    data-siswa-row
                    data-nama="{{ strtolower($s->nama) }}"
                    data-no-absen="{{ strtolower((string) $s->no_absen) }}"
                >
                    <div class="flex items-center gap-2.5">
                        <x-ui.avatar :label="$s->no_absen ?? '–'" :gender="$s->jenis_kelamin" />
                        <div class="flex min-w-0 flex-col">
                            <span class="truncate text-sm font-semibold text-ink">{{ $s->nama }}</span>
                            @if ($dariDispensasiOtomatis)
                                <span class="flex items-center gap-1 text-[11px] font-semibold text-dispen">
                                    <x-icon name="verified" :size="12" />
                                    Dispensasi disetujui untuk jam ini
                                </span>
                            @elseif ($dariJurnalLain)
                                <span class="flex items-center gap-1 text-[11px] font-semibold text-dispen">
                                    <x-icon name="verified" :size="12" />
                                    Ikut jurnal lain hari ini di kelas ini
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($statusRingkas)
                        <button
                            type="button"
                            data-toggle-status="{{ $statusToggleId }}"
                            class="flex items-center justify-between gap-2 rounded-lg border border-hadir-soft bg-hadir-soft px-3 py-2 text-left text-xs font-bold text-hadir"
                        >
                            <span class="flex items-center gap-1"><x-icon name="check_circle" :size="14" /> Hadir</span>
                            <span class="text-[11px] underline">Ubah</span>
                        </button>
                    @endif
                    <div id="{{ $statusToggleId }}" @if ($statusRingkas) hidden @endif>
                        <x-ui.choice
                            :name="'presensi[' . $s->id . '][status]'"
                            :options="$statuses"
                            :tones="$tones"
                            :value="$statusAwal"
                            size="sm"
                        />
                    </div>

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
            const cari = document.getElementById('cari-siswa');
            const rows = document.querySelectorAll('[data-siswa-row]');
            const counter = document.getElementById('jumlah-tampil');

            // Kotak cari (dan input catatan/metode lainnya) ada di DALAM form
            // besar -- pencet Enter di situ defaultnya langsung submit SELURUH
            // jurnal, padahal maksudnya cuma nyaring daftar siswa. Cegah itu
            // buat semua kotak teks satu-baris di form ini, biar Enter aman.
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

            document.querySelectorAll('[data-toggle-status]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = document.getElementById(btn.dataset.toggleStatus);
                    target.hidden = false;
                    btn.hidden = true;
                    target.querySelector('input')?.focus();
                });
            });

            document.querySelectorAll('[data-toggle-catatan]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = document.getElementById(btn.dataset.toggleCatatan);
                    target.hidden = false;
                    btn.hidden = true;
                    target.querySelector('input')?.focus();
                });
            });

            // Tombol "x" di kotak catatan -- tutup lagi & kosongin isinya (biar
            // nggak nyangkut kebuka kosongan kalau ternyata nggak jadi diisi).
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
