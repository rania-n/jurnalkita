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
        ->when($ruang, function ($b, $ruang) {
            $norm = preg_replace('/^r\s*(\d+)$/i', 'R$1', trim($ruang));
            $spaced = preg_replace('/^r\s*(\d+)$/i', 'R $1', trim($ruang));
            $b->where(function ($q) use ($ruang, $norm, $spaced) {
                $q->where('ruang', $ruang)
                    ->orWhere('ruang', $norm)
                    ->orWhere('ruang', $spaced);
            });
        })
        ->when($jp, fn ($b) => $b->where('jam_ke_mulai', '<=', $jp)->where('jam_ke_selesai', '>=', $jp))
        ->orderByRaw(\App\Support\Db::hariOrder())
        ->orderBy('jam_ke_mulai')
        ->get();

    $hariLabel = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat'];
    $hariTabs = ['semua' => 'Semua'] + $hariLabel;
    $kelasList = \App\Models\Kelas::orderedByHierarchy()->get(['id', 'nama']);
    $mapelList = \App\Models\Mapel::orderBy('nama')->get(['id', 'nama']);
    $guruList = \App\Models\Guru::with('mapels:id,nama', 'mapelUtama:id,nama')->orderBy('nama')->get(['id', 'nama', 'mapel_utama_id']);
    $guruMapelMap = $guruList->map(function ($g) {
        $mapelIds = array_values(array_filter(array_unique(array_merge(
            $g->mapel_utama_id ? [(int) $g->mapel_utama_id] : [],
            $g->mapels->pluck('id')->map(fn ($id) => (int) $id)->all()
        ))));
        $mapelNamas = array_values(array_filter(array_unique(array_merge(
            $g->mapelUtama ? [$g->mapelUtama->nama] : [],
            $g->mapels->pluck('nama')->all()
        ))));
        return [
            'id' => $g->id,
            'nama' => $g->nama,
            'mapel_ids' => $mapelIds,
            'keterangan' => implode(', ', $mapelNamas),
        ];
    })->values()->all();

    $guruListFilter = $mapelId
        ? $guruList->filter(fn ($g) => $g->mapel_utama_id == $mapelId || $g->mapels->contains('id', (int) $mapelId))->values()
        : $guruList;

    $ruangList = collect(config('akademik.ruangan'))->map(fn ($r) => ['id' => $r, 'nama' => $r]);
    $jpList = collect(range(1, 13))->map(fn ($i) => ['id' => $i, 'nama' => "JP {$i}", 'keterangan' => "Jam ke-{$i}"]);
    $queryTanpaHari = request()->except('page', 'hari');

    $kelasDipilih = request()->query('kelas_id');
    $hariPenuh = $kelasDipilih ? \App\Models\Jadwal::hariPenuhUntukKelas((int) $kelasDipilih) : [];
    $hariOptions = collect($hariLabel)->map(fn ($l, $v) => in_array($v, $hariPenuh, true) ? "{$l} (Penuh)" : $l)->all();

    // Hitung hari penuh untuk semua kelas sekaligus secara cepat untuk update instan di browser tanpa reload
    $jpPerKategori = \App\Models\JamPelajaran::all()->groupBy('kategori')->map->pluck('jam_ke')->map->all()->all();
    $hariKategori = \Illuminate\Support\Facades\DB::table('jam_pelajaran_hari')->pluck('kategori', 'hari')->all();
    
    $jpPerHari = collect(['senin', 'selasa', 'rabu', 'kamis', 'jumat'])->mapWithKeys(function ($h) use ($hariKategori, $jpPerKategori) {
        $kategori = $hariKategori[$h] ?? null;
        return [$h => $kategori ? ($jpPerKategori[$kategori] ?? []) : []];
    })->all();

    $jadwalsGrouped = \App\Models\Jadwal::all(['kelas_id', 'hari', 'jam_ke_mulai', 'jam_ke_selesai'])->groupBy(['kelas_id', 'hari']);
    $hariPenuhSemuaKelas = [];
    foreach ($jadwalsGrouped as $kId => $hariGroup) {
        foreach ($hariGroup as $h => $jList) {
            $jpTersedia = $jpPerHari[$h] ?? [];
            if (empty($jpTersedia)) {
                continue;
            }
            $terpakai = [];
            foreach ($jList as $j) {
                for ($i = $j->jam_ke_mulai; $i <= $j->jam_ke_selesai; $i++) {
                    $terpakai[$i] = true;
                }
            }
            if (empty(array_diff($jpTersedia, array_keys($terpakai)))) {
                $hariPenuhSemuaKelas[$kId][] = $h;
            }
        }
    }

    $maxJpPerHari = array_map(fn($jps) => empty($jps) ? 13 : max($jps), $jpPerHari);
    $absoluteMaxJp = empty($maxJpPerHari) ? 13 : max($maxJpPerHari);
    $absoluteMaxJp = max($absoluteMaxJp, 13);
    
    // override jpList with dynamic max
    $jpList = collect(range(1, $absoluteMaxJp))->map(fn ($i) => ['id' => $i, 'nama' => "JP {$i}", 'keterangan' => "Jam ke-{$i}"]);
@endphp

<x-layouts.admin title="Jadwal Pelajaran" heading="Jadwal Pelajaran" :subtitle="$rows->count() . ' jadwal'">
    <x-admin.page title="Jadwal Pelajaran" :subtitle="$rows->count() . ' jadwal'">
        <x-slot:action>
            <div class="flex gap-2">
                <x-ui.button type="button" icon="upload_file" data-modal-open="modal-import-jadwal" variant="secondary" class="hidden sm:inline-flex">Import</x-ui.button>
                <x-ui.button type="button" icon="add" data-modal-open="modal-jadwal" data-modal-title="Tambah Jadwal">Tambah Jadwal</x-ui.button>
            </div>
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
        <x-ui.cari-pilihan name="kelas" label="Kelas" :options="$kelasList" all="Semua Kelas" />
        <x-ui.cari-pilihan name="guru" label="Guru" :options="$guruListFilter" all="Semua Guru" />
        <x-ui.cari-pilihan name="mapel" label="Mapel" :options="$mapelList" all="Semua Mapel" />
        <x-ui.cari-pilihan name="ruang" label="Ruang" :options="$ruangList" all="Semua Ruang" />
        <x-ui.cari-pilihan name="jp" label="JP" :options="$jpList" all="Semua JP" />
    </x-admin.filters>

    <x-ui.auto-refresh :url="route('master.jadwal-pelajaran.versi')" />

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
        <form method="POST" action="{{ route('master.jadwal-pelajaran.save') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.cari-pilihan
                label="Kelas"
                name="kelas_id"
                :options="$kelasList"
                placeholder="Ketik nama kelas..."
                required
            />
            <div class="flex flex-col gap-1.5">
                <x-ui.choice
                    label="Hari"
                    name="hari"
                    :options="$hariOptions"
                    :disabled="$hariPenuh"
                    required
                />
                <p data-hari-hint class="text-xs text-muted-2">
                    @if ($kelasDipilih)
                        @if (count($hariPenuh) > 0)
                            Hari yang ditandai "(Penuh)" sudah tidak memiliki celah jam pelajaran kosong untuk kelas ini, sehingga tidak dapat dipilih.
                        @else
                            Semua hari masih memiliki celah jam pelajaran kosong untuk kelas ini.
                        @endif
                    @endif
                </p>
            </div>
            <x-ui.cari-pilihan
                label="Mata Pelajaran"
                name="mapel_id"
                data-cari-pilihan-mapel
                :options="$mapelList->map(fn ($m) => ['id' => $m->id, 'nama' => $m->nama])"
                placeholder="Ketik nama mapel..."
                tambah-label="Tambah Mata Pelajaran Baru"
                :tambah-url="route('master.mapel.index')"
                required
            />
            <x-ui.cari-pilihan
                label="Guru Pengajar"
                name="guru_id"
                data-cari-pilihan-guru
                :options="$guruList->map(fn ($g) => ['id' => $g->id, 'nama' => $g->nama])"
                placeholder="Ketik nama guru..."
                required
            />
            <div class="flex gap-3">
                <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai" class="flex-1" required>
                    <option value="" disabled selected hidden>Pilih</option>
                    @for ($i = 1; $i <= $absoluteMaxJp; $i++)<option value="{{ $i }}">Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
                <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai" class="flex-1" required>
                    <option value="" disabled selected hidden>Pilih</option>
                    @for ($i = 1; $i <= $absoluteMaxJp; $i++)<option value="{{ $i }}">Jam ke-{{ $i }}</option>@endfor
                </x-ui.select>
            </div>
            <x-ui.cari-pilihan
                label="Ruang"
                name="ruang"
                :options="$ruangList"
                placeholder="Ketik nama ruang..."
                required
            />
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @if ($kelasDipilih)
        <button
            type="button"
            hidden
            data-auto-open-jadwal
            data-modal-open="modal-jadwal"
            data-modal-title="Tambah Jadwal"
        ></button>
    @endif

    <x-admin.modal id="modal-import-jadwal" title="Import Jadwal" error-bag="import_jadwal">
        <form method="POST" action="{{ route('master.jadwal-pelajaran.import') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf
            <div class="rounded-xl border border-info-soft bg-info-soft/30 p-4 text-sm text-info-dark">
                <p class="font-bold mb-1">Format File CSV yang didukung:</p>
                <ul class="list-disc pl-5 mb-2">
                    <li>Gunakan kolom Header: <code>Hari, Jam Mulai, Jam Selesai, Kelas, Mapel, Guru, Ruang</code></li>
                    <li>Pastikan nama Kelas, Mapel, dan Guru sama persis (case-insensitive) dengan data di sistem.</li>
                    <li>File harus berformat .csv dipisahkan dengan koma (,) atau titik koma (;).</li>
                </ul>
                <a href="{{ route('master.jadwal-pelajaran.template-import') }}" class="text-info hover:underline font-semibold text-xs">Download Template CSV</a>
            </div>
            
            <div>
                <label class="mb-1 block text-sm font-semibold text-ink">File CSV</label>
                <input type="file" name="file" accept=".csv" required class="block w-full rounded-md border border-surface-alt bg-card px-3 py-2 text-sm focus:border-navy focus:outline-none">
                @error('file', 'import_jadwal')
                    <p class="mt-1 text-xs font-medium text-alpha">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="upload" class="flex-1">Mulai Import</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            (function () {
                const hariPenuhMap = @json($hariPenuhSemuaKelas);
                const hariNamaMap = @json($hariLabel);
                const semuaGuru = @json($guruMapelMap);
                const semuaMapel = @json($mapelList);
                const modal = document.getElementById('modal-jadwal');
                const kelasInput = modal?.querySelector('[data-cari-pilihan-value][name="kelas_id"]');
                const wrapMapel = modal?.querySelector('[data-cari-pilihan-mapel]');
                const mapelInput = wrapMapel?.querySelector('[data-cari-pilihan-value]');
                const inputMapel = wrapMapel?.querySelector('[data-cari-pilihan-input]');
                const clearBtnMapel = wrapMapel?.querySelector('[data-cari-pilihan-clear]');
                const wrapGuru = modal?.querySelector('[data-cari-pilihan-guru]');
                const inputGuru = wrapGuru?.querySelector('[data-cari-pilihan-input]');
                const hiddenGuru = wrapGuru?.querySelector('[data-cari-pilihan-value]');
                const clearBtnGuru = wrapGuru?.querySelector('[data-cari-pilihan-clear]');

                function filterGuruByMapel(mapelId, keepSelection = false) {
                    if (!wrapGuru) return;
                    const mId = parseInt(mapelId, 10);
                    let guruTersedia = semuaGuru;
                    if (mId) {
                        guruTersedia = semuaGuru.filter((g) => g.mapel_ids && g.mapel_ids.includes(mId));
                    }

                    wrapGuru.dataset.list = JSON.stringify(guruTersedia.map((g) => ({
                        id: g.id,
                        nama: g.nama,
                        keterangan: g.keterangan || '',
                    })));

                    if (!keepSelection && hiddenGuru && hiddenGuru.value) {
                        const masihAda = guruTersedia.some((g) => String(g.id) === String(hiddenGuru.value));
                        if (!masihAda) {
                            hiddenGuru.value = '';
                            if (inputGuru) inputGuru.value = '';
                            if (clearBtnGuru) clearBtnGuru.hidden = true;
                        }
                    }
                }

                function filterMapelByGuru(guruId, keepSelection = false) {
                    if (!wrapMapel) return;
                    const gId = parseInt(guruId, 10);
                    let mapelTersedia = semuaMapel;
                    if (gId) {
                        const targetGuru = semuaGuru.find((g) => String(g.id) === String(gId));
                        if (targetGuru && targetGuru.mapel_ids && targetGuru.mapel_ids.length > 0) {
                            mapelTersedia = semuaMapel.filter((m) => targetGuru.mapel_ids.includes(m.id));
                        }
                    }

                    wrapMapel.dataset.list = JSON.stringify(mapelTersedia);

                    if (!keepSelection && mapelInput && mapelInput.value) {
                        const masihAda = mapelTersedia.some((m) => String(m.id) === String(mapelInput.value));
                        if (!masihAda) {
                            mapelInput.value = '';
                            if (inputMapel) inputMapel.value = '';
                            if (clearBtnMapel) clearBtnMapel.hidden = true;
                        }
                    }
                }

                function updateHariPenuh(kelasId) {
                    if (!modal) return;
                    const penuh = (kelasId && hariPenuhMap[kelasId]) ? hariPenuhMap[kelasId] : [];
                    const radios = modal.querySelectorAll('input[name="hari"]');
                    radios.forEach((radio) => {
                        const isPenuh = penuh.includes(radio.value);
                        radio.disabled = isPenuh;
                        const label = radio.closest('label');
                        if (label) {
                            label.classList.toggle('cursor-not-allowed', isPenuh);
                            label.classList.toggle('opacity-40', isPenuh);
                            label.classList.toggle('cursor-pointer', !isPenuh);
                            const span = label.querySelector('span');
                            if (span) {
                                const baseName = hariNamaMap[radio.value] || radio.value;
                                span.textContent = isPenuh ? (baseName + ' (Penuh)') : baseName;
                            }
                        }
                        if (isPenuh && radio.checked) {
                            radio.checked = false;
                        }
                    });

                    const hint = modal.querySelector('[data-hari-hint]');
                    if (hint) {
                        if (kelasId) {
                            hint.textContent = penuh.length > 0
                                ? 'Hari yang ditandai "(Penuh)" sudah tidak memiliki celah jam pelajaran kosong untuk kelas ini, sehingga tidak dapat dipilih.'
                                : 'Semua hari masih memiliki celah jam pelajaran kosong untuk kelas ini.';
                        } else {
                            hint.textContent = '';
                        }
                    }
                }

                const jpPerHari = @json($jpPerHari);
                const selectMulai = modal?.querySelector('select[name="jam_ke_mulai"]');
                const selectSelesai = modal?.querySelector('select[name="jam_ke_selesai"]');

                function updateJpDropdowns(hari) {
                    if (!selectMulai || !selectSelesai) return;
                    
                    const jps = jpPerHari[hari] || [];
                    const maxJp = jps.length > 0 ? Math.max(...jps) : 13;
                    const defaultMax = Math.max(13, maxJp);
                    
                    const currentMulai = selectMulai.value;
                    const currentSelesai = selectSelesai.value;
                    
                    let optionsHtml = '<option value="" disabled selected hidden>Pilih</option>';
                    for (let i = 1; i <= defaultMax; i++) {
                        optionsHtml += `<option value="${i}">Jam ke-${i}</option>`;
                    }
                    
                    selectMulai.innerHTML = optionsHtml;
                    selectSelesai.innerHTML = optionsHtml;
                    
                    if (currentMulai && currentMulai <= defaultMax) selectMulai.value = currentMulai;
                    if (currentSelesai && currentSelesai <= defaultMax) selectSelesai.value = currentSelesai;
                }

                modal?.querySelectorAll('input[name="hari"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked) updateJpDropdowns(this.value);
                    });
                });

                kelasInput?.addEventListener('change', function () {
                    updateHariPenuh(this.value);
                });

                mapelInput?.addEventListener('change', function () {
                    filterGuruByMapel(this.value, false);
                    if (!this.value && hiddenGuru?.value) {
                        filterMapelByGuru(hiddenGuru.value, true);
                    }
                });

                hiddenGuru?.addEventListener('change', function () {
                    if (!mapelInput?.value) {
                        filterMapelByGuru(this.value, false);
                    }
                });

                modal?.addEventListener('modal:open', function () {
                    filterGuruByMapel(mapelInput?.value || '', true);
                    if (hiddenGuru?.value && !mapelInput?.value) {
                        filterMapelByGuru(hiddenGuru.value, true);
                    }
                    updateHariPenuh(kelasInput?.value || '');
                    
                    const selectedHari = modal.querySelector('input[name="hari"]:checked')?.value;
                    if (selectedHari) updateJpDropdowns(selectedHari);
                });
            })();
        </script>
    @endpush
</x-layouts.admin>
