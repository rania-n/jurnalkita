@php
    // Manajemen Akun = akun yang SUDAH ada (approved/rejected) doang -- pendaftaran
    // yang masih menunggu diputuskan ada di halaman terpisah "Persetujuan Akun"
    // (master.akun.persetujuan), biar nggak campur aksi "putuskan" sama "kelola".
    $roleLabel = ['admin' => 'Admin', 'guru' => 'Guru', 'siswa' => 'Pengurus Kelas', 'waka' => 'Waka Kesiswaan', 'satpam' => 'Satpam'];

    $users = \App\Models\User::with('guru', 'siswa.kelas')
        ->where('status', '!=', 'pending')
        ->when(request()->query('cari'), fn ($q, $c) => $q->where(fn ($w) => $w->where('name', 'like', "%{$c}%")->orWhere('email', 'like', "%{$c}%")))
        ->when(request()->query('role'), fn ($q, $r) => $q->where('role', $r))
        ->orderByRaw(\App\Support\Db::orderByList('status', ['approved', 'rejected']))
        ->orderBy('name')
        ->get();

    $pendingCount = \App\Models\User::where('status', 'pending')->count();

    $guruTanpaAkun = \App\Models\Guru::whereNull('user_id')->orderBy('nama')->get(['id', 'nama', 'nip']);
    $siswaTanpaAkun = \App\Models\Siswa::whereNull('user_id')->where('jabatan', 'pengurus')->with('kelas')->orderBy('nama')->get();
    $kelasList = \App\Models\Kelas::orderedByHierarchy()->get(['id', 'nama']);

    // Dipisah jadi variable (bukan langsung di atribut :options="...") --
    // string berkutip di dalam atribut Blade bikin compiler gagal parse tag
    // komponennya (pernah ketemu bug ini juga di $hariOptions Jadwal Pelajaran).
    $guruOptions = collect([['id' => 'baru', 'nama' => '➕ Buat data baru']])
        ->concat($guruTanpaAkun->map(fn ($g) => ['id' => "guru:{$g->id}", 'nama' => $g->nama.($g->nip ? " · {$g->nip}" : '')]));
    $siswaOptions = collect([['id' => 'baru', 'nama' => '➕ Buat data baru']])
        ->concat($siswaTanpaAkun->map(fn ($s) => ['id' => "siswa:{$s->id}", 'nama' => $s->nama.' · '.($s->kelas?->nama ?? '?')]));
    $roleTabs = ['' => 'Semua'] + $roleLabel;
    $roleAktif = (string) request()->query('role');
    $queryTanpaRole = request()->except('page', 'role');
@endphp

<x-layouts.admin title="Manajemen Akun" heading="Manajemen Akun">
    <x-admin.page title="Manajemen Akun" subtitle="{{ $users->count() }} akun aktif/ditolak">
        <x-slot:action>
            @if ($pendingCount > 0)
                <x-ui.button :href="route('master.akun.persetujuan')" variant="secondary" icon="how_to_reg" class="w-full sm:w-auto">Persetujuan ({{ $pendingCount }})</x-ui.button>
            @endif
            <x-ui.button type="button" icon="person_add" data-modal-open="modal-akun" class="w-full sm:w-auto">Buat Akun</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($roleTabs as $key => $label)
            <a href="{{ route('master.akun.index', array_merge($queryTanpaRole, $key === '' ? [] : ['role' => $key])) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $roleAktif === $key, 'text-muted-2 hover:text-ink' => $roleAktif !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-admin.filters :action="route('master.akun.index')">
        <x-admin.f-search placeholder="Cari nama / email..." />
        @if ($roleAktif !== '')
            <input type="hidden" name="role" value="{{ $roleAktif }}">
        @endif
    </x-admin.filters>

    @if ($users->isEmpty())
        <x-ui.empty title="Tidak ada akun" />
    @else
        <x-admin.table :head="['Nama', 'Email', 'Peran', 'Terhubung ke', 'Status', 'Aksi']">
            @foreach ($users as $u)
                @php $ubahFill = ['nama' => $u->name, 'email' => $u->email, 'no_hp' => $u->no_hp, 'nip' => $u->nip]; @endphp
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $u->name }}</td>
                    <td class="px-4 py-3 text-muted">{{ $u->email }}</td>
                    <td class="px-4 py-3 text-muted">{{ $roleLabel[$u->role] ?? $u->role }}</td>
                    <td class="px-4 py-3 text-muted">
                        @if ($u->guru)
                            <a href="{{ route('master.guru.show', $u->guru) }}" class="text-navy hover:underline">Guru · {{ $u->guru->nama }}</a>
                        @elseif ($u->siswa)
                            <a href="{{ route('master.siswa.show', $u->siswa) }}" class="text-navy hover:underline">Siswa · {{ $u->siswa->kelas?->nama }}</a>
                        @else — @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.status-badge :status="match ($u->status) { 'approved' => 'disetujui', 'rejected' => 'ditolak', default => 'menunggu' }">
                            {{ ucfirst($u->status) }}
                        </x-ui.status-badge>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            @if ($u->role !== 'admin')
                                <button type="button"
                                    data-modal-open="modal-akun-ubah"
                                    data-modal-title="Ubah Akun — {{ $u->name }}"
                                    data-modal-id="{{ $u->id }}"
                                    data-modal-fill='@json($ubahFill)'
                                    data-modal-role="{{ $u->role }}"
                                    class="flex h-8 items-center gap-1 rounded-lg bg-izin-soft px-2.5 text-xs font-bold text-izin hover:bg-[#bae6fd]">
                                    <x-icon name="edit" :size="14" /> Ubah
                                </button>
                                <form method="POST" action="{{ route('master.akun.reset', $u) }}" class="contents" data-confirm="Kirim email tautan reset sandi ke {{ $u->email }}?">@csrf
                                    <button class="flex h-8 items-center gap-1 rounded-lg bg-surface-alt px-2.5 text-xs font-bold text-ink hover:bg-[#cbd5e1]">
                                        <x-icon name="mail" :size="14" /> Kirim Reset
                                    </button>
                                </form>
                            @endif

                            {{-- Hapus akun: tidak untuk admin & tidak untuk diri sendiri --}}
                            @if ($u->role !== 'admin' && $u->id !== auth()->id())
                                <form method="POST" action="{{ route('master.akun.destroy', $u) }}" class="contents"
                                      data-confirm="Hapus akun {{ $u->name }} ({{ $u->email }})? Data guru/siswa-nya tetap ada, hanya akun loginnya yang dihapus.">
                                    @csrf @method('DELETE')
                                    <button class="flex h-8 items-center gap-1 rounded-lg bg-alpha-soft px-2.5 text-xs font-bold text-alpha hover:bg-[#fecdd3]">
                                        <x-icon name="delete" :size="14" /> Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    {{-- ------------------------------------------------------------- Modal buat akun --}}
    <x-admin.modal id="modal-akun" title="Buat Akun" errorBag="buatAkun">
        <form method="POST" action="{{ route('master.akun.save') }}" class="flex flex-col gap-4" id="form-akun">
            @csrf

            <x-ui.choice
                label="Jenis Akun"
                name="role"
                :options="['guru' => 'Guru', 'siswa' => 'Pengurus Kelas', 'waka' => 'Waka Kesiswaan', 'satpam' => 'Satpam']"
                value="guru"
            />

            {{-- "Ambil dari data" -- 2 kotak cari terpisah (satu buat guru,
                 satu buat pengurus kelas), gantian ditampilin sesuai Jenis
                 Akun yang lagi kepilih (lihat setGrup() di script bawah) --
                 dulu 1 <select> isinya 2 optgroup, sekarang dipecah biar
                 masing-masing bisa diketik-cari (banyak guru/siswa). --}}
            <div data-grup="sumber-guru">
                <x-ui.cari-pilihan
                    label="Ambil dari data"
                    name="sumber"
                    id="akun-sumber-guru"
                    placeholder="Ketik nama guru, atau biarkan kosong buat data baru"
                    :options="$guruOptions"
                    value="baru"
                />
            </div>
            <div data-grup="sumber-siswa">
                <x-ui.cari-pilihan
                    label="Ambil dari data"
                    name="sumber"
                    id="akun-sumber-siswa"
                    placeholder="Ketik nama siswa, atau biarkan kosong buat data baru"
                    :options="$siswaOptions"
                    value="baru"
                />
            </div>

            <x-ui.input label="Nama Lengkap" name="nama" id="akun-nama" errorBag="buatAkun" required />
            <x-ui.input label="Email" name="email" type="email" placeholder="email@sekolah.sch.id" errorBag="buatAkun" required />

            {{-- guru baru & waka: NIP (siswa punya NIS sendiri di bawah, satpam tidak perlu) --}}
            <x-ui.input label="NIP (opsional)" name="nip" data-grup="nip" errorBag="buatAkun" />

            {{-- semua peran: no. WhatsApp, dipakai kirim link/notifikasi lewat WA --
                 wajib KHUSUS guru (guru sering butuh dihubungi langsung soal
                 jadwal/piket), peran lain tetap opsional -- lihat toggle
                 required di script bawah. --}}
            <x-ui.input
                label="No. WhatsApp"
                name="no_hp"
                id="akun-no-hp"
                inputmode="numeric"
                placeholder="08xxxxxxxxxx"
                hint="Dipakai buat kirim link persetujuan/surat lewat WhatsApp. Wajib untuk Guru."
                errorBag="buatAkun"
            />

            {{-- khusus data pengurus kelas baru --}}
            <div data-grup="siswa-baru" class="flex flex-col gap-4">
                <x-ui.cari-pilihan
                    label="Kelas"
                    name="kelas_id"
                    :options="$kelasList"
                    placeholder="Ketik nama kelas..."
                    required
                />
                <x-ui.input label="NIS" name="nis" inputmode="numeric" errorBag="buatAkun" required />
                <x-ui.choice label="Jenis Kelamin" name="jenis_kelamin" :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" value="L" required />
            </div>

            <x-ui.input label="Password" name="password" type="password" id="akun-password" placeholder="Ketik password" hint="Minimal 8 karakter." errorBag="buatAkun" required>
            </x-ui.input>
            <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" placeholder="Ulangi password" errorBag="buatAkun" required />

            <p class="rounded-lg bg-izin-soft px-3 py-2 text-xs text-izin">Beri password ini ke yang bersangkutan. Nanti dia bisa reset sendiri lewat "Lupa Sandi".</p>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Buat Akun</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    {{-- ------------------------------------------------------------- Modal ubah akun --}}
    <x-admin.modal id="modal-akun-ubah" title="Ubah Akun" errorBag="ubahAkun">
        <form method="POST" action="{{ route('master.akun.update') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.input label="Nama Lengkap" name="nama" errorBag="ubahAkun" required />
            <x-ui.input label="Email" name="email" type="email" errorBag="ubahAkun" required />
            <x-ui.input label="No. WhatsApp (opsional)" name="no_hp" inputmode="numeric" placeholder="08xxxxxxxxxx" errorBag="ubahAkun" />

            {{-- Cuma relevan buat Waka/Satpam (guru punya NIP sendiri di data
                 guru, siswa/pengurus kelas nggak punya NIP sama sekali) --
                 dulu field ini selalu tampil buat SEMUA peran walau labelnya
                 udah bilang "khusus Waka", isinya diam-diam diabaikan server
                 kalau bukan waka/satpam -- bingung-in, sekarang disembunyiin
                 beneran sesuai peran akun yang lagi diubah (lihat script bawah). --}}
            <div data-grup="nip-ubah">
                <x-ui.input label="NIP (opsional)" name="nip" errorBag="ubahAkun" />
            </div>

            <x-ui.input label="Password Baru (opsional)" name="password" type="password" id="akun-ubah-password" placeholder="Kosongkan kalau tidak diganti" hint="Kosongkan kalau tidak diganti. Kalau diisi, minimal 8 karakter." errorBag="ubahAkun" />
            <x-ui.input label="Konfirmasi Password Baru" name="password_confirmation" type="password" placeholder="Ulangi kalau ganti password" errorBag="ubahAkun" />

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan Perubahan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            (function () {
                const form = document.getElementById('form-akun');
                const roleRadios = form.querySelectorAll('input[name="role"]');
                const sumberGuruWrap = form.querySelector('[data-grup="sumber-guru"]');
                const sumberSiswaWrap = form.querySelector('[data-grup="sumber-siswa"]');
                const sumberGuruHidden = sumberGuruWrap.querySelector('[data-cari-pilihan-value]');
                const sumberSiswaHidden = sumberSiswaWrap.querySelector('[data-cari-pilihan-value]');
                const nama = document.getElementById('akun-nama');
                const grupSiswaBaru = form.querySelector('[data-grup="siswa-baru"]');
                const grupNip = form.querySelector('[data-grup="nip"]');
                const noHp = document.getElementById('akun-no-hp');

                const setGrup = (el, on) => {
                    el.hidden = !on;
                    el.querySelectorAll('input, select').forEach((i) => (i.disabled = !on));
                };

                function roleTerpilih() {
                    return [...roleRadios].find((r) => r.checked)?.value;
                }

                function refresh() {
                    const r = roleTerpilih();

                    setGrup(sumberGuruWrap, r === 'guru');
                    setGrup(sumberSiswaWrap, r === 'siswa');

                    const sumberAktif = r === 'guru' ? sumberGuruHidden : (r === 'siswa' ? sumberSiswaHidden : null);
                    const baru = !sumberAktif || !sumberAktif.value || sumberAktif.value === 'baru';

                    setGrup(grupSiswaBaru, r === 'siswa' && baru);
                    setGrup(grupNip, r === 'waka' || (r === 'guru' && baru));
                    // No. WA wajib khusus Guru (nggak lewat setGrup -- field ini
                    // sendiri tetap tampil buat semua peran, cuma required-nya aja
                    // yang beda tergantung Jenis Akun).
                    noHp.required = r === 'guru';

                    const namaTerisi = sumberAktif?.closest('[data-cari-pilihan]')?.querySelector('[data-cari-pilihan-input]')?.value;
                    if (!baru && namaTerisi) { nama.value = namaTerisi; nama.readOnly = true; }
                    else { nama.readOnly = false; }
                }

                roleRadios.forEach((r) => r.addEventListener('change', refresh));
                sumberGuruHidden.addEventListener('change', refresh);
                sumberSiswaHidden.addEventListener('change', refresh);
                document.getElementById('modal-akun').addEventListener('modal:open', refresh);
                refresh();
            })();

            (function () {
                // Modal Ubah Akun nggak punya pemilih "Jenis Akun" (role akun
                // nggak bisa diganti dari sini) -- peran akun yang lagi diubah
                // dibaca dari tombol yang diklik (data-modal-role), dipakai buat
                // nampilin/nyembunyiin NIP (cuma relevan buat Waka/Satpam).
                const grupNipUbah = document.querySelector('#modal-akun-ubah [data-grup="nip-ubah"]');
                document.querySelectorAll('[data-modal-open="modal-akun-ubah"]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const tampil = ['waka', 'satpam'].includes(btn.dataset.modalRole);
                        grupNipUbah.hidden = !tampil;
                        grupNipUbah.querySelectorAll('input').forEach((i) => (i.disabled = !tampil));
                    });
                });
            })();
        </script>
    @endpush
</x-layouts.admin>
