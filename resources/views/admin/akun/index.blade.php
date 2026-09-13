@php
    // Manajemen Akun = akun yang SUDAH ada (approved/rejected) doang -- pendaftaran
    // yang masih menunggu diputuskan ada di halaman terpisah "Persetujuan Akun"
    // (master.akun.persetujuan), biar nggak campur aksi "putuskan" sama "kelola".
    $users = \App\Models\User::with('guru', 'siswa.kelas')
        ->where('status', '!=', 'pending')
        ->when(request('cari'), fn ($q, $c) => $q->where(fn ($w) => $w->where('name', 'like', "%{$c}%")->orWhere('email', 'like', "%{$c}%")))
        ->orderByRaw(\App\Support\Db::orderByList('status', ['approved', 'rejected']))
        ->orderBy('name')
        ->get();

    $pendingCount = \App\Models\User::where('status', 'pending')->count();

    $guruTanpaAkun = \App\Models\Guru::whereNull('user_id')->orderBy('nama')->get(['id', 'nama', 'nip']);
    $siswaTanpaAkun = \App\Models\Siswa::whereNull('user_id')->where('jabatan', 'pengurus')->with('kelas')->orderBy('nama')->get();
    $kelasList = \App\Models\Kelas::orderBy('nama')->get(['id', 'nama']);
    $roleLabel = ['admin' => 'Admin', 'guru' => 'Guru', 'siswa' => 'Pengurus Kelas', 'waka' => 'Waka', 'satpam' => 'Satpam'];
@endphp

<x-layouts.admin title="Manajemen Akun" heading="Manajemen Akun">
    <x-admin.page title="Manajemen Akun" subtitle="{{ $users->count() }} akun aktif/ditolak">
        <x-slot:action>
            @if ($pendingCount > 0)
                <x-ui.button :href="route('master.akun.persetujuan')" variant="secondary" icon="how_to_reg">Persetujuan ({{ $pendingCount }})</x-ui.button>
            @endif
            <x-ui.button type="button" icon="person_add" data-modal-open="modal-akun">Buat Akun</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-admin.filters :action="route('master.akun.index')">
        <x-admin.f-search placeholder="Cari nama / email..." />
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
                        @if ($u->guru) Guru
                        @elseif ($u->siswa) Siswa · {{ $u->siswa->kelas?->nama }}
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
    <x-admin.modal id="modal-akun" title="Buat Akun">
        <form method="POST" action="{{ route('master.akun.save') }}" class="flex flex-col gap-4" id="form-akun">
            @csrf

            <x-ui.select label="Jenis Akun" name="role" id="akun-role">
                <option value="guru">Guru</option>
                <option value="siswa">Pengurus Kelas</option>
                <option value="waka">Waka Kesiswaan</option>
                <option value="satpam">Satpam</option>
            </x-ui.select>

            <x-ui.select label="Ambil dari data" name="sumber" id="akun-sumber" data-grup="sumber">
                <option value="baru">➕ Buat data baru</option>
                <optgroup label="Guru belum punya akun" data-role="guru">
                    @foreach ($guruTanpaAkun as $g)
                        <option value="guru:{{ $g->id }}" data-nama="{{ $g->nama }}">{{ $g->nama }}{{ $g->nip ? " · {$g->nip}" : '' }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="Pengurus kelas belum punya akun" data-role="siswa">
                    @foreach ($siswaTanpaAkun as $s)
                        <option value="siswa:{{ $s->id }}" data-nama="{{ $s->nama }}">{{ $s->nama }} · {{ $s->kelas?->nama }}</option>
                    @endforeach
                </optgroup>
            </x-ui.select>

            <x-ui.input label="Nama Lengkap" name="nama" id="akun-nama" />
            <x-ui.input label="Email" name="email" type="email" placeholder="email@sekolah.sch.id" />

            {{-- guru baru & waka: NIP (siswa punya NIS sendiri di bawah, satpam tidak perlu) --}}
            <x-ui.input label="NIP (opsional)" name="nip" data-grup="nip" />

            {{-- semua peran: no. WhatsApp, dipakai kirim link/notifikasi lewat WA --}}
            <x-ui.input label="No. WhatsApp (opsional)" name="no_hp" inputmode="numeric" placeholder="08xxxxxxxxxx" hint="Dipakai buat kirim link persetujuan/surat lewat WhatsApp." />

            {{-- khusus data pengurus kelas baru --}}
            <div data-grup="siswa-baru" class="flex flex-col gap-4">
                <x-ui.select label="Kelas" name="kelas_id">
                    <option value="" disabled selected hidden>Pilih kelas</option>
                    @foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
                </x-ui.select>
                <x-ui.input label="NIS" name="nis" inputmode="numeric" />
                <x-ui.select label="Jenis Kelamin" name="jenis_kelamin">
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </x-ui.select>
            </div>

            <x-ui.input label="Password" name="password" type="password" id="akun-password" placeholder="Minimal 8 karakter">
                <button type="button" data-toggle-password="#akun-password" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan">
                    <x-icon name="visibility" :size="18" />
                </button>
            </x-ui.input>
            <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" placeholder="Ulangi password" />

            <p class="rounded-lg bg-izin-soft px-3 py-2 text-xs text-izin">Beri password ini ke yang bersangkutan. Nanti dia bisa reset sendiri lewat "Lupa Sandi".</p>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Buat Akun</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    {{-- ------------------------------------------------------------- Modal ubah akun --}}
    <x-admin.modal id="modal-akun-ubah" title="Ubah Akun">
        <form method="POST" action="{{ route('master.akun.update') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.input label="Nama Lengkap" name="nama" />
            <x-ui.input label="Email" name="email" type="email" />
            <x-ui.input label="No. WhatsApp (opsional)" name="no_hp" inputmode="numeric" placeholder="08xxxxxxxxxx" />
            <x-ui.input label="NIP (opsional, khusus Waka)" name="nip" />

            <x-ui.input label="Password Baru (opsional)" name="password" type="password" id="akun-ubah-password" placeholder="Kosongkan kalau tidak diganti">
                <button type="button" data-toggle-password="#akun-ubah-password" class="flex shrink-0 items-center text-muted-2" aria-label="Tampilkan">
                    <x-icon name="visibility" :size="18" />
                </button>
            </x-ui.input>
            <x-ui.input label="Konfirmasi Password Baru" name="password_confirmation" type="password" placeholder="Ulangi kalau ganti password" />

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
                const role = document.getElementById('akun-role');
                const sumber = document.getElementById('akun-sumber');
                const nama = document.getElementById('akun-nama');
                const sumberWrap = sumber.closest('[data-grup]');
                const grupSiswaBaru = form.querySelector('[data-grup="siswa-baru"]');
                const grupNip = form.querySelector('[data-grup="nip"]');

                const setGrup = (el, on) => {
                    el.hidden = !on;
                    el.querySelectorAll('input, select').forEach((i) => (i.disabled = !on));
                };

                function refresh() {
                    const r = role.value;
                    const pakaiData = r === 'guru' || r === 'siswa';

                    sumber.querySelectorAll('optgroup').forEach((g) => {
                        const off = g.dataset.role !== r;
                        g.hidden = g.disabled = off;
                    });
                    setGrup(sumberWrap, pakaiData);
                    if (!pakaiData || sumber.selectedOptions[0]?.parentElement?.hidden) sumber.value = 'baru';

                    const baru = sumber.value === 'baru';
                    setGrup(grupSiswaBaru, r === 'siswa' && baru);
                    setGrup(grupNip, r === 'waka' || (r === 'guru' && baru));

                    const opt = sumber.selectedOptions[0];
                    if (!baru && opt?.dataset.nama) { nama.value = opt.dataset.nama; nama.readOnly = true; }
                    else { nama.readOnly = false; }
                }

                role.addEventListener('change', refresh);
                sumber.addEventListener('change', refresh);
                document.getElementById('modal-akun').addEventListener('modal:open', refresh);
                refresh();
            })();
        </script>
    @endpush
</x-layouts.admin>
