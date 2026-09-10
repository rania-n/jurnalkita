@php
    $tab = request('tab', 'semua');
    $users = \App\Models\User::with('guru', 'siswa.kelas')
        ->when($tab === 'pending', fn ($q) => $q->where('status', 'pending'))
        ->when(request('cari'), fn ($q, $c) => $q->where(fn ($w) => $w->where('name', 'like', "%{$c}%")->orWhere('email', 'like', "%{$c}%")))
        ->orderByRaw(\App\Support\Db::orderByList('status', ['pending', 'approved', 'rejected']))
        ->orderBy('name')
        ->get();

    $pendingCount = \App\Models\User::where('status', 'pending')->count();

    $guruTanpaAkun = \App\Models\Guru::whereNull('user_id')->orderBy('nama')->get(['id', 'nama', 'nip']);
    $siswaTanpaAkun = \App\Models\Siswa::whereNull('user_id')->where('jabatan', 'pengurus')->with('kelas')->orderBy('nama')->get();
    $kelasList = \App\Models\Kelas::orderBy('nama')->get(['id', 'nama']);
    $roleLabel = ['admin' => 'Admin', 'guru' => 'Guru', 'siswa' => 'Pengurus Kelas', 'waka' => 'Waka'];
@endphp

<x-layouts.admin title="Manajemen Akun" heading="Manajemen Akun">
    <x-admin.page title="Manajemen Akun" subtitle="{{ $users->count() }} akun · {{ $pendingCount }} menunggu persetujuan">
        <x-slot:action>
            <x-ui.button type="button" icon="person_add" data-modal-open="modal-akun">Buat Akun</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <div class="flex gap-1 rounded-xl border border-surface-alt bg-card p-1">
            @foreach (['semua' => 'Semua', 'pending' => "Menunggu ({$pendingCount})"] as $key => $label)
                <a href="{{ route('master.akun.index', ['tab' => $key]) }}"
                   @class(['rounded-lg px-3 py-1.5 text-sm font-semibold', 'bg-navy text-card' => $tab === $key, 'text-muted-2' => $tab !== $key])>{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / email..."
                class="h-9 w-56 rounded-lg border border-surface-alt bg-card px-3 text-sm outline-none focus:border-navy">
        </form>
    </div>

    @if ($users->isEmpty())
        <x-ui.empty title="Tidak ada akun" />
    @else
        <x-admin.table :head="['Nama', 'Email', 'Peran', 'Terhubung ke', 'Status', 'Aksi']">
            @foreach ($users as $u)
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
                            @if ($u->status === 'pending')
                                <form method="POST" action="{{ route('master.akun.approve', $u) }}" class="contents">@csrf
                                    <button class="flex h-8 items-center gap-1 rounded-lg bg-hadir-soft px-2.5 text-xs font-bold text-hadir hover:bg-[#bef3ab]">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('master.akun.reject', $u) }}" class="contents" data-confirm="Tolak pendaftaran {{ $u->name }}?">@csrf
                                    <button class="flex h-8 items-center gap-1 rounded-lg bg-alpha-soft px-2.5 text-xs font-bold text-alpha hover:bg-[#fecdd3]">Tolak</button>
                                </form>
                            @elseif ($u->role !== 'admin')
                                <form method="POST" action="{{ route('master.akun.reset', $u) }}" class="contents" data-confirm="Reset password {{ $u->name }}? Password lama tidak berlaku lagi.">@csrf
                                    <button class="flex h-8 items-center gap-1 rounded-lg bg-surface-alt px-2.5 text-xs font-bold text-ink hover:bg-[#cbd5e1]">Reset Sandi</button>
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
            </x-ui.select>

            <x-ui.select label="Ambil dari data" name="sumber" id="akun-sumber" data-grup="guru siswa">
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

            {{-- field data baru --}}
            <x-ui.input label="NIP (opsional)" name="nip" data-grup="guru-baru" />
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

            <p class="rounded-lg bg-izin-soft px-3 py-2 text-xs text-izin">Sistem membuat password sementara — akan ditampilkan setelah simpan.</p>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save">Buat Akun</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
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
                const grupGuruBaru = form.querySelector('[data-grup="guru-baru"]');
                const grupSiswaBaru = form.querySelector('[data-grup="siswa-baru"]');

                const setGrup = (el, on) => {
                    el.hidden = !on;
                    el.querySelectorAll('input, select').forEach((i) => (i.disabled = !on));
                };

                function refresh() {
                    const r = role.value;
                    const pakaiData = r === 'guru' || r === 'siswa';

                    sumber.querySelectorAll('optgroup').forEach((g) => (g.hidden = g.dataset.role !== r));
                    setGrup(sumberWrap, pakaiData);
                    if (!pakaiData || sumber.selectedOptions[0]?.parentElement?.hidden) sumber.value = 'baru';

                    const baru = sumber.value === 'baru';
                    setGrup(grupGuruBaru, r === 'guru' && baru);
                    setGrup(grupSiswaBaru, r === 'siswa' && baru);

                    const opt = sumber.selectedOptions[0];
                    if (!baru && opt?.dataset.nama) { nama.value = opt.dataset.nama; nama.readOnly = true; }
                    else { nama.readOnly = false; }
                }

                role.addEventListener('change', refresh);
                sumber.addEventListener('change', refresh);
                document.getElementById('modal-akun').addEventListener('toggle', refresh);
                refresh();
            })();
        </script>
    @endpush
</x-layouts.admin>
