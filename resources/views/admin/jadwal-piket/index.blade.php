@php
    $hari = request('hari', 'semua');
    $q = request('cari');
    $hariLabel = config('akademik.hari');
    $tabs = ['semua' => 'Semua'] + $hariLabel;

    // hari tetap keisi (diturunin dari tanggal) buat baris yang tanggalnya
    // spesifik -- jadi tab filter Hari tetap jalan normal buat baris lama
    // MAUPUN baris baru. Diurutkan tanggal PALING DEKAT duluan (yang belum
    // lewat), biar giliran yang mau datang keliatan paling atas.
    $rows = \App\Models\JadwalPiket::with('guru')
        ->when($hari !== 'semua', fn ($b) => $b->where('hari', $hari))
        ->when($q, fn ($b) => $b->whereHas('guru', fn ($g) => $g->where('nama', 'like', "%{$q}%")))
        ->orderByRaw(\App\Support\Db::hariOrder())
        ->orderByRaw('tanggal IS NULL, tanggal asc')
        ->get();

    $guruList = \App\Models\Guru::orderBy('nama')->get(['id', 'nama']);
    $semua = $hari === 'semua';
@endphp

<x-layouts.admin title="Jadwal Piket" heading="Jadwal Piket">
    <x-admin.page title="Jadwal Piket" subtitle="Penugasan piket guru per tanggal (ulang tiap 2 minggu)">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-piket-tambah" data-modal-title="Tambah Jadwal Piket">Tambah Piket</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-surface-alt bg-card p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('master.jadwal-piket.index', ['hari' => $key, 'cari' => $q]) }}"
               @class(['flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold whitespace-nowrap transition-colors', 'bg-navy text-card' => $hari === $key, 'text-muted-2 hover:text-ink' => $hari !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- hideButtons -- reset hari udah ada di tab "Semua" di atas, di sini
         cuma search doang, jangan dobel sama tombol X di search-nya sendiri. --}}
    <x-admin.filters :action="route('master.jadwal-piket.index')" hideButtons="true">
        <input type="hidden" name="hari" value="{{ $hari }}">
        <x-admin.f-search placeholder="Cari nama guru..." />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada jadwal piket" />
    @else
        <x-admin.table :head="$semua ? ['Hari', 'Tanggal', 'Guru', 'Jam', 'Keterangan', ''] : ['Tanggal', 'Guru', 'Jam', 'Keterangan', '']">
            @foreach ($rows as $p)
                <tr class="hover:bg-surface/60">
                    @if ($semua)
                        <td class="px-4 py-3 font-semibold text-ink">{{ $hariLabel[$p->hari] ?? $p->hari }}</td>
                    @endif
                    <td class="px-4 py-3 text-muted">
                        @if ($p->tanggal)
                            {{ $p->tanggal->translatedFormat('d M Y') }}
                        @else
                            <span class="italic">Berulang tiap minggu</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 {{ $semua ? 'text-muted' : 'font-semibold text-ink' }}">{{ $p->guru?->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $p->mulai?->format('H:i') ?? '—' }} – {{ $p->selesai?->format('H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-muted">{{ $p->keterangan ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-piket-ubah"
                            edit-title="Ubah Jadwal Piket"
                            :edit-id="$p->id"
                            :edit-fill="[
                                'guru_id' => $p->guru_id,
                                'tanggal' => $p->tanggal?->toDateString(),
                                'mulai' => $p->mulai?->format('H:i'),
                                'selesai' => $p->selesai?->format('H:i'),
                                'keterangan' => $p->keterangan,
                            ]"
                            :delete-action="route('master.jadwal-piket.destroy', $p)"
                            delete-confirm="Hapus jadwal piket ini?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    {{-- Modal TAMBAH -- generate banyak baris sekaligus dari 1 tanggal awal,
         diulang tiap N minggu, sebanyak M kali. Piket asli emang gilirannya
         per tanggal spesifik & ulang tiap 2 minggu, bukan "tiap Senin
         selamanya" -- jadi nggak perlu isi manual satu-satu tiap giliran. --}}
    <x-admin.modal id="modal-piket-tambah" title="Tambah Jadwal Piket">
        <form method="POST" action="{{ route('master.jadwal-piket.save') }}" class="flex flex-col gap-4">
            @csrf
            {{-- Bisa dicentang lebih dari 1 -- kadang piket hari itu emang
                 digilir bareng beberapa guru sekaligus. --}}
            <x-ui.cari-checkbox
                label="Guru Piket (boleh pilih lebih dari 1)"
                name="guru_ids"
                :options="$guruList"
            />
            <x-ui.input label="Tanggal Mulai" name="tanggal" type="date" :value="old('tanggal', today()->isWeekend() ? today()->nextWeekday()->toDateString() : today()->toDateString())" required />
            <p class="-mt-2 text-xs text-muted-2">Hari piket ditentukan otomatis dari tanggal. Jadwal berulang mengikuti interval yang dipilih.</p>
            {{-- Sesi -- shortcut isi Jam Mulai/Selesai otomatis (2 sesi yang
                 beneran dipakai sekolah), tapi field jamnya sendiri tetap
                 bisa diubah manual sesudahnya kalau memang beda. --}}
            <x-ui.choice
                label="Sesi"
                name="sesi_piket"
                data-sesi-piket
                :options="['pagi' => 'Pagi (07:00–11:00)', 'siang' => 'Siang (11:00–15:00)', 'custom' => 'Custom']"
            />
            <div class="flex gap-3">
                <x-ui.input label="Jam Mulai" name="mulai" type="time" value="07:00" class="flex-1" required />
                <x-ui.input label="Jam Selesai" name="selesai" type="time" value="11:00" class="flex-1" required />
            </div>
            <x-ui.choice
                label="Ulang Setiap"
                name="ulang_setiap_minggu"
                :options="['1' => '1 minggu', '2' => '2 minggu', '3' => '3 minggu', '4' => '4 minggu']"
                value="2"
            />
            <x-ui.input label="Jumlah Kali" name="jumlah_kali" type="number" min="1" max="52" value="10" required />
            <p class="-mt-2 text-xs text-muted-2">Sistem otomatis bikin jadwal sebanyak "Jumlah Kali", masing-masing berjarak sesuai "Ulang Setiap" dari Tanggal Mulai.</p>
            <x-ui.input label="Keterangan (opsional)" name="keterangan" />
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    {{-- Modal UBAH -- 1 baris doang, nggak ada opsi ulang (ngedit baris yang
         udah ada, bukan bikin baris baru). --}}
    <x-admin.modal id="modal-piket-ubah" title="Ubah Jadwal Piket">
        <form method="POST" action="{{ route('master.jadwal-piket.save') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.cari-pilihan
                label="Guru Piket"
                name="guru_id"
                :options="$guruList"
                placeholder="Ketik nama guru..."
                required
            />
            <x-ui.input label="Tanggal" name="tanggal" type="date" required />
            <div class="flex gap-3">
                <x-ui.input label="Jam Mulai" name="mulai" type="time" class="flex-1" required />
                <x-ui.input label="Jam Selesai" name="selesai" type="time" class="flex-1" required />
            </div>
            <x-ui.input label="Keterangan (opsional)" name="keterangan" />
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            // Sesi -- shortcut isi Jam Mulai/Selesai otomatis, jam-nya sendiri
            // tetap bisa diubah manual sesudahnya kalau memang beda.
            document.querySelectorAll('[data-sesi-piket] input[name="sesi_piket"]').forEach((r) => {
                r.addEventListener('change', function () {
                    const form = this.closest('form');
                    const m = form.querySelector('[name=mulai]'), s = form.querySelector('[name=selesai]');
                    if (this.value === 'pagi') { m.value = '07:00'; s.value = '11:00'; }
                    else if (this.value === 'siang') { m.value = '11:00'; s.value = '15:00'; }
                });
            });
        </script>
    @endpush
</x-layouts.admin>
