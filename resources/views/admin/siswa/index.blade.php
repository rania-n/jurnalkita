@php
    // Pakai request()->query(...), BUKAN request('status') -- Route::view() ini
    // menyimpan parameter aksi internalnya sendiri (view/data/status/headers) di
    // route parameters, dan request('status') (helper __get) jatuh balik ke situ
    // kalau tidak ada di query string. Akibatnya $status selalu ke-isi 200 (kode
    // HTTP default Route::view()), bikin filter WHERE status=200 selalu aktif dan
    // tabel selalu kosong. request()->query('status') tidak punya fallback itu.
    $q = request()->query('cari');
    $kelasId = request()->query('kelas');
    $jk = request()->query('jk');
    $status = request()->query('status');
    $jabatan = request()->query('jabatan');

    $rows = \App\Models\Siswa::with('kelas')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w
            ->where('nama', 'like', "%{$q}%")
            ->orWhere('nis', 'like', "%{$q}%")
            ->orWhereHas('kelas', fn ($k) => $k->where('nama', 'like', "%{$q}%"))
        ))
        ->when($kelasId, fn ($b) => $b->where('kelas_id', $kelasId))
        ->when($jk, fn ($b) => $b->where('jenis_kelamin', $jk))
        ->when($status, fn ($b) => $b->where('status', $status))
        ->when($jabatan, fn ($b) => $b->where('jabatan', $jabatan))
        ->orderBy('nama')
        ->paginate(20)->withQueryString();

    $kelasList = \App\Models\Kelas::orderedByHierarchy()->get(['id', 'nama']);
    $urutanTingkat = array_flip(config('akademik.tingkat'));
    $urutanJurusan = array_flip(array_keys(config('akademik.jurusan')));
    // Dropdown tambah/ubah: kelas tahun ajaran aktif + kelas siswa yang lagi ditampilkan
    // (supaya siswa yang sudah lulus/pindah tetap kepilih saat modal Ubah dibuka).
    $kelasAktifList = \App\Models\Kelas::aktif()->orderedByHierarchy()->get(['id', 'nama', 'tingkat', 'jurusan', 'nomor'])
        ->concat($rows->pluck('kelas')->filter())
        ->unique('id')
        ->sortBy(fn ($kelas) => [
            $urutanTingkat[$kelas->tingkat] ?? PHP_INT_MAX,
            $urutanJurusan[$kelas->jurusan] ?? PHP_INT_MAX,
            $kelas->nomor,
            $kelas->nama,
        ])
        ->values();

    // Buat form "Ubah Status PKL Beberapa Siswa" -- SENGAJA nunggu filter
    // "Kelas" di atas dipilih dulu, baru daftar checkbox-nya kebentuk.
    // Sekolah ini siswa aktifnya ribuan; nge-render checkbox buat SEMUA
    // siswa sekaligus di tiap buka halaman ini bakal berat + kepanjangan buat
    // discroll, padahal butuhnya emang cuma 1 kelas dalam sekali jalan (kasus
    // asli: kelas XI yang PKL-nya sebagian). Dropdown "Kelas" yang sama di
    // filter atas dipakai ulang, bukan bikin filter baru lagi.
    $siswaAktifPklOptions = $kelasId
        ? \App\Models\Siswa::where('kelas_id', $kelasId)->where('status', 'aktif')
            ->orderBy('nama')->get(['id', 'nama'])
        : collect();
@endphp

<x-layouts.admin title="Data Siswa" heading="Data Siswa">
    <x-admin.page title="Data Siswa" subtitle="{{ $rows->total() }} siswa">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-siswa" data-modal-title="Tambah Siswa">Tambah Siswa</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.siswa.index')">
        <x-admin.f-search placeholder="Nama, NIS, atau kelas..." />
        <x-ui.cari-pilihan name="kelas" label="Kelas" :options="$kelasList" all="Semua Kelas" />
        <x-admin.f-select name="jabatan" label="Jabatan" :options="['anggota' => 'Anggota', 'pengurus' => 'Pengurus Kelas']" all="Semua Jabatan" />
        <x-admin.f-select name="jk" label="Jenis Kelamin" :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" all="Semua" />
        <x-admin.f-select name="status" label="Status" :options="['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah']" all="Semua Status" />
    </x-admin.filters>

    <section class="mb-5 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)] sm:p-5">
        <h2 class="text-base font-bold text-ink">Ubah Status PKL Beberapa Siswa</h2>
        <p class="mt-1 text-sm text-muted">
            Kelas yang seluruh siswanya berstatus PKL dapat diatur melalui status kelas pada menu Data Kelas.
            Bagian ini digunakan untuk kelas yang status PKL-nya hanya berlaku bagi sebagian siswa -- pilih
            kelas terlebih dahulu melalui filter "Kelas" di atas, kemudian pilih siswa yang berstatus PKL.
        </p>

        @if ($siswaAktifPklOptions->isEmpty())
            <p class="mt-4 text-sm text-muted-2">Pilih salah satu kelas melalui filter "Kelas" di atas untuk memulai.</p>
        @else
            {{-- data-confirm di TOMBOL, bukan di <form> -- kalau di form,
                 konfirmasi kepicu di SETIAP klik di dalamnya (kotak cari,
                 checkbox, radio pun kena), bukan cuma pas klik submit. --}}
            <form method="POST" action="{{ route('master.siswa.pkl-massal') }}" class="mt-4 grid gap-4 lg:grid-cols-2">
                @csrf
                @method('PATCH')
                <div>
                    <x-ui.cari-checkbox
                        label="Siswa yang diubah ({{ $kelasList->firstWhere('id', (int) $kelasId)?->nama }})"
                        name="siswa_ids"
                        :options="$siswaAktifPklOptions"
                        hint="Cari nama, lalu centang satu atau beberapa siswa."
                    />
                </div>
                <div class="flex flex-col justify-between gap-4">
                    <x-ui.choice
                        label="Status baru"
                        name="pkl"
                        :options="['1' => 'PKL', '0' => 'Bukan PKL']"
                        value="1"
                        required
                    />
                    <x-ui.button type="submit" icon="save" class="w-full sm:w-auto" data-confirm="Ubah status PKL siswa yang dipilih?">Terapkan ke Siswa Terpilih</x-ui.button>
                </div>
            </form>
        @endif
    </section>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada siswa yang cocok" />
    @else
        <x-admin.table :head="['NIS', 'Nama', 'Kelas', 'L/P', 'Jabatan', 'Status', '']">
            @foreach ($rows as $s)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 text-muted">{{ $s->nis }}</td>
                    <td class="px-4 py-3 font-semibold text-ink">{{ $s->nama }}</td>
                    <td class="px-4 py-3 text-muted">
                        @if ($s->kelas)
                            <a href="{{ route('master.kelas.show', $s->kelas) }}" class="text-navy hover:underline">{{ $s->kelas->nama }}</a>
                        @else — @endif
                    </td>
                    <td class="px-4 py-3 text-muted">{{ $s->jenis_kelamin }}</td>
                    <td class="px-4 py-3">
                        <x-ui.status-badge :status="$s->jabatan === 'pengurus' ? 'pengurus' : 'anggota'" />
                    </td>
                    <td class="px-4 py-3">
                        @if ($s->status === 'aktif')
                            <span class="text-muted">Aktif</span>
                        @elseif ($s->status === 'lulus')
                            <x-ui.status-badge status="hadir">Lulus</x-ui.status-badge>
                        @else
                            <x-ui.status-badge status="alpha">Pindah</x-ui.status-badge>
                        @endif
                        @if ($s->isPkl())
                            <x-ui.status-badge status="pkl" class="ml-1" />
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            :detail="route('master.siswa.show', $s)"
                            edit-modal="modal-siswa"
                            edit-title="Ubah Siswa"
                            :edit-id="$s->id"
                            :edit-fill="['nis' => $s->nis, 'nama' => $s->nama, 'kelas_id' => $s->kelas_id, 'no_absen' => $s->no_absen, 'jenis_kelamin' => $s->jenis_kelamin, 'jabatan' => $s->jabatan, 'status' => $s->status, 'pkl' => $s->pkl ? '1' : '0']"
                            :delete-action="route('master.siswa.destroy', $s)"
                            delete-confirm="Yakin hapus {{ $s->nama }}?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <div class="mt-4">{{ $rows->links() }}</div>
    @endif

    <x-admin.modal id="modal-siswa" title="Tambah Siswa">
        <form method="POST" action="{{ route('master.siswa.save') }}" class="flex flex-col gap-4">
            @csrf
            <p class="text-xs text-muted-2">
                Data siswa saja. Akun login (khusus pengurus kelas) + No. WhatsApp
                diisi lewat menu "Manajemen Akun → Buat Akun".
            </p>
            <x-ui.cari-pilihan
                label="Kelas"
                name="kelas_id"
                :options="$kelasAktifList->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama])"
                placeholder="Ketik nama kelas..."
                required
            />
            <x-ui.input label="NIS" name="nis" inputmode="numeric" required />
            <x-ui.input label="Nama Lengkap" name="nama" id="siswa-nama" required />

            <div class="flex flex-col gap-1.5">
                <x-ui.label>Nomor Presensi</x-ui.label>
                <div class="flex gap-2">
                    <x-ui.input name="no_absen" id="siswa-no-absen" type="number" min="1" class="flex-1" />
                    <x-ui.button type="button" variant="secondary" id="siswa-no-absen-otomatis" class="shrink-0">Otomatis</x-ui.button>
                </div>
                <span class="text-xs text-muted-2">"Otomatis" nyaranin nomor sesuai urutan abjad nama di kelas ini -- boleh diisi manual sendiri juga.</span>
            </div>

            <x-ui.choice label="Jenis Kelamin" name="jenis_kelamin" :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" required />
            <x-ui.choice label="Jabatan Kelas" name="jabatan" :options="['anggota' => 'Anggota', 'pengurus' => 'Pengurus Kelas']" value="anggota" required />
            <x-ui.choice label="Status" name="status" :options="['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah']" value="aktif" />
            <p class="-mt-2 text-xs text-muted-2">
                Biasanya biarkan "Aktif" — "Lulus" otomatis diisi lewat Kenaikan Kelas,
                "Pindah" dipakai kalau siswa pindah sekolah.
            </p>
            <x-ui.choice label="Status PKL" name="pkl" :options="['0' => 'Bukan PKL', '1' => 'PKL']" value="0" />
            <p class="-mt-2 text-xs text-muted-2">
                Kelas yang semua siswanya PKL cukup diatur di status kelas (Data Kelas).
                Ini buat siswa yang PKL-nya beda sendiri dari teman sekelasnya.
            </p>
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            (function () {
                const tombol = document.getElementById('siswa-no-absen-otomatis');
                tombol?.addEventListener('click', async () => {
                    const form = tombol.closest('form');
                    const kelasId = form.querySelector('[name="kelas_id"]')?.value;
                    const nama = form.querySelector('[name="nama"]')?.value?.trim();
                    if (!kelasId || !nama) return;

                    const kecualiId = form.querySelector('[name="id"]')?.value || '';
                    const url = new URL('{{ route('master.siswa.no-absen-otomatis') }}', window.location.origin);
                    url.searchParams.set('kelas_id', kelasId);
                    url.searchParams.set('nama', nama);
                    if (kecualiId) url.searchParams.set('kecuali_id', kecualiId);

                    const res = await fetch(url);
                    const data = await res.json();
                    if (data.no_absen) document.getElementById('siswa-no-absen').value = data.no_absen;
                });
            })();
        </script>
    @endpush
</x-layouts.admin>
