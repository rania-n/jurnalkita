<x-layouts.app title="Daftar Siswa Sekelas" width="wide">
    <x-page-header title="Daftar Siswa" :subtitle="$kelas->nama . ' · kehadiran hari ini'" />

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa di kelas ini" />
    @else
        <x-ui.search-bar id="cari-siswa-kelas" placeholder="Cari nama siswa..." class="mb-3" />
        <p class="-mt-2 mb-3 text-xs text-muted-2" id="jumlah-tampil-siswa-kelas">Menampilkan {{ $siswas->count() }} dari {{ $siswas->count() }} siswa</p>

        <div class="hidden sm:block">
            <x-admin.table :head="['No. Absen', 'Nama', 'NIS', 'Jabatan', 'Kehadiran Hari Ini']">
                @foreach ($siswas as $s)
                    @php $absenHariIni = $absensiHariIni->get($s->id); @endphp
                    <tr data-siswa-kelas-row data-nama="{{ strtolower($s->nama) }}">
                        <td class="px-4 py-2.5 text-muted">{{ $s->no_absen ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-ink">{{ $s->nama }}</td>
                        <td class="px-4 py-2.5 text-muted">{{ $s->nis }}</td>
                        <td class="px-4 py-2.5">
                            @if ($s->jabatan === 'pengurus')
                                <x-ui.status-badge status="terverifikasi">Pengurus</x-ui.status-badge>
                            @else
                                <span class="text-muted">Anggota</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($absenHariIni)
                                <x-ui.status-badge :status="$absenHariIni->status" />
                            @else
                                <span class="text-muted-2">Belum ada jurnal</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="flex flex-col gap-2 sm:hidden">
            @foreach ($siswas as $s)
                @php $absenHariIni = $absensiHariIni->get($s->id); @endphp
                <div data-siswa-kelas-row data-nama="{{ strtolower($s->nama) }}">
                    <x-ui.list-card :title="$s->nama" :meta="['No. ' . ($s->no_absen ?? '—') . ' · NIS ' . $s->nis]">
                        <x-slot:badge>
                            <div class="flex flex-col items-end gap-1">
                                @if ($absenHariIni)
                                    <x-ui.status-badge :status="$absenHariIni->status" />
                                @else
                                    <span class="text-[11px] text-muted-2">Belum ada jurnal</span>
                                @endif
                                @if ($s->jabatan === 'pengurus')
                                    <x-ui.status-badge status="terverifikasi">Pengurus</x-ui.status-badge>
                                @endif
                            </div>
                        </x-slot:badge>
                    </x-ui.list-card>
                </div>
            @endforeach
        </div>

        <p id="siswa-kelas-kosong" hidden class="mt-3 rounded-xl border border-dashed border-surface-alt bg-card p-6 text-center text-sm text-muted-2">
            Tidak ada siswa yang cocok dengan pencarian.
        </p>

        @push('scripts')
            <script>
                (function () {
                    const cari = document.getElementById('cari-siswa-kelas');
                    const rows = document.querySelectorAll('[data-siswa-kelas-row]');
                    const counter = document.getElementById('jumlah-tampil-siswa-kelas');
                    const kosong = document.getElementById('siswa-kelas-kosong');

                    // 1 siswa punya 2 baris (versi tabel desktop + versi kartu HP) --
                    // keduanya sama-sama disembunyikan/ditampilkan bareng, tapi
                    // dihitung sekali aja per siswa (bukan dobel) buat counternya.
                    cari?.addEventListener('input', () => {
                        const q = cari.value.trim().toLowerCase();
                        const namaTampil = new Set();
                        rows.forEach((row) => {
                            const cocok = !q || row.dataset.nama.includes(q);
                            row.hidden = !cocok;
                            if (cocok) namaTampil.add(row.dataset.nama);
                        });
                        counter.textContent = `Menampilkan ${namaTampil.size} dari ${rows.length / 2} siswa`;
                        kosong.hidden = namaTampil.size > 0;
                    });
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
