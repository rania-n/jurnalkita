@php
    $hari = request()->query('hari', 'semua');
    $kelasId = request()->query('kelas');
    $guruId = request()->query('guru');
    $mapelId = request()->query('mapel');
    $ruang = request()->query('ruang');
    $jp = request()->query('jp');

    $rows = \App\Models\Jadwal::with('kelas', 'mapel', 'guru')
        ->when($hari !== 'semua', fn ($b) => $b->where('hari', $hari))
        ->when($kelasId, fn ($b) => $b->where('kelas_id', $kelasId))
        ->when($guruId, fn ($b) => $b->where('guru_id', $guruId))
        ->when($mapelId, fn ($b) => $b->where('mapel_id', $mapelId))
        ->when($ruang, fn ($b) => $b->where('ruang', $ruang))
        ->when($jp, fn ($b) => $b->where('jam_ke_mulai', '<=', $jp)->where('jam_ke_selesai', '>=', $jp))
        ->orderByRaw(\App\Support\Db::hariOrder())
        ->orderBy('jam_ke_mulai')
        ->get();

    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
    $hariTabs = ['semua' => 'Semua'] + $hariLabel;
    $kelasList = \App\Models\Kelas::orderBy('nama')->get(['id', 'nama']);
    $mapelList = \App\Models\Mapel::orderBy('nama')->get(['id', 'nama']);
    $guruList = \App\Models\Guru::orderBy('nama')->get(['id', 'nama']);
    $ruangList = collect(config('akademik.ruangan'))->mapWithKeys(fn ($r) => [$r => $r]);
    $jpList = collect(range(1, 13))->mapWithKeys(fn ($i) => [$i => "Jam ke-{$i}"]);
    $queryTanpaHari = request()->except('page', 'hari');

    // Modal Tambah/Ubah Jadwal -- Kelas dipilih DULUAN (bukan Hari), biar begitu
    // kelasnya diketahui, hari yang buat kelas itu JP-nya udah penuh semua bisa
    // langsung dikunci di dropdown Hari (nggak ngasih celah bikin jadwal yang
    // jelas-jelas bakal bentrok). Ganti Kelas nge-reload halaman (bukan AJAX) --
    // sengaja gitu, biar itungan "hari penuh"-nya seger dari server, bukan JS.
    // Dibaca dari 'kelas_id' -- SAMA PERSIS sama nama field select-nya di modal
    // (lihat onchange di bawah, cuma nge-set 1 param ini doang, bukan submit
    // seluruh form, biar field lain yang masih kosong nggak ikut kebawa jadi
    // query string & bikin salah kefilter di tabel atas).
    $kelasDipilih = request()->query('kelas_id');
    $hariPenuh = $kelasDipilih ? \App\Models\Jadwal::hariPenuhUntukKelas((int) $kelasDipilih) : [];
@endphp

<x-layouts.admin title="Jadwal Pelajaran" heading="Jadwal Pelajaran">
    <x-admin.page title="Jadwal Pelajaran" subtitle="{{ $rows->count() }} jadwal">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-jadwal" data-modal-title="Tambah Jadwal">Tambah Jadwal</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($hariTabs as $key => $label)
            <a href="{{ route('master.jadwal-pelajaran.index', array_merge($queryTanpaHari, $key === 'semua' ? [] : ['hari' => $key])) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $hari === $key, 'text-muted-2 hover:text-ink' => $hari !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-admin.filters :action="route('master.jadwal-pelajaran.index')">
        @if ($hari !== 'semua')
            <input type="hidden" name="hari" value="{{ $hari }}">
        @endif
        <x-admin.f-select name="kelas" label="Kelas" :options="$kelasList->pluck('nama', 'id')" all="Semua Kelas" />
        <x-admin.f-select name="guru" label="Guru" :options="$guruList->pluck('nama', 'id')" all="Semua Guru" />
        <x-admin.f-select name="mapel" label="Mapel" :options="$mapelList->pluck('nama', 'id')" all="Semua Mapel" />
        <x-admin.f-select name="ruang" label="Ruang" :options="$ruangList" all="Semua Ruang" />
        <x-admin.f-select name="jp" label="JP" :options="$jpList" all="Semua JP" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada jadwal yang cocok" />
    @else
        <x-admin.table :head="['Hari', 'Kelas', 'Mapel', 'Guru', 'JP', 'Ruang', '']">
            @foreach ($rows as $j)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $hariLabel[$j->hari] ?? $j->hari }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->kelas?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->mapel?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->guru?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->jam_ke_mulai }}–{{ $j->jam_ke_selesai }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->ruang ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-jadwal"
                            edit-title="Ubah Jadwal"
                            :edit-id="$j->id"
                            :edit-fill="['hari' => $j->hari, 'kelas_id' => $j->kelas_id, 'mapel_id' => $j->mapel_id, 'guru_id' => $j->guru_id, 'jam_ke_mulai' => $j->jam_ke_mulai, 'jam_ke_selesai' => $j->jam_ke_selesai, 'ruang' => $j->ruang]"
                            :delete-action="route('master.jadwal-pelajaran.destroy', $j)"
                            delete-confirm="Hapus jadwal ini?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-jadwal" title="Tambah Jadwal">
        {{-- Kelas dipilih DULUAN -- ganti nilainya nge-reload halaman ini (GET,
             bukan submit beneran) biar server bisa itung ulang hari mana yang
             buat kelas itu udah penuh (lihat $hariPenuh di atas), lalu modal
             kebuka lagi otomatis (data-auto-open-jadwal di bawah) dengan Kelas
             udah kepilih & opsi Hari yang penuh otomatis kekunci. --}}
        <form method="POST" action="{{ route('master.jadwal-pelajaran.save') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.select
                label="Kelas"
                name="kelas_id"
                onchange="location.href='{{ route('master.jadwal-pelajaran.index') }}?kelas_id=' + this.value"
            >
                <option value="" disabled hidden @selected(! $kelasDipilih)>Pilih kelas</option>
                @foreach ($kelasList as $k)
                    <option value="{{ $k->id }}" @selected((string) $kelasDipilih === (string) $k->id)>{{ $k->nama }}</option>
                @endforeach
            </x-ui.select>
            <div class="flex flex-col gap-1.5">
                <x-ui.select label="Hari" name="hari">
                    <option value="" disabled selected hidden>Pilih hari</option>
                    @foreach ($hariLabel as $v => $l)
                        <option value="{{ $v }}" @disabled(in_array($v, $hariPenuh, true))>{{ $l }}{{ in_array($v, $hariPenuh, true) ? ' (Penuh)' : '' }}</option>
                    @endforeach
                </x-ui.select>
                @if ($kelasDipilih)
                    <p class="text-xs text-muted-2">
                        @if (count($hariPenuh) > 0)
                            Hari yang ditandai "(Penuh)" udah nggak ada celah JP kosong buat kelas ini.
                        @else
                            Semua hari masih ada celah JP kosong buat kelas ini.
                        @endif
                    </p>
                @endif
            </div>
            <x-ui.select label="Mata Pelajaran" name="mapel_id">
                <option value="" disabled selected hidden>Pilih mapel</option>
                @foreach ($mapelList as $m)<option value="{{ $m->id }}">{{ $m->nama }}</option>@endforeach
            </x-ui.select>
            <x-ui.select label="Guru Pengajar" name="guru_id">
                <option value="" disabled selected hidden>Pilih guru</option>
                @foreach ($guruList as $g)<option value="{{ $g->id }}">{{ $g->nama }}</option>@endforeach
            </x-ui.select>
            <div class="flex gap-3">
                <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai" class="flex-1">
                    <option value="" disabled selected hidden>Pilih</option>
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}">Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
                <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai" class="flex-1">
                    <option value="" disabled selected hidden>Pilih</option>
                    @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}">Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
            </div>
            <x-ui.select label="Ruang" name="ruang">
                <option value="">— belum ditentukan —</option>
                @foreach (config('akademik.ruangan') as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </x-ui.select>
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @if ($kelasDipilih)
        {{-- Abis reload gara-gara ganti Kelas (lihat onchange di atas) -- buka
             lagi modalnya otomatis, jangan nyangkut balik ke tabel biasa. --}}
        <button
            type="button"
            hidden
            data-auto-open-jadwal
            data-modal-open="modal-jadwal"
            data-modal-title="Tambah Jadwal"
        ></button>
        @push('scripts')
            <script>window.addEventListener('load', () => document.querySelector('[data-auto-open-jadwal]')?.click());</script>
        @endpush
    @endif
</x-layouts.admin>
