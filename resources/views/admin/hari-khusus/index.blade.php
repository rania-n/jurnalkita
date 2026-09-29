@php
    $jenisLabel = \App\Models\HariKhusus::JENIS;
    $jenisAwal = old('jenis', 'tanpa_kbm');
@endphp

<x-layouts.admin title="Hari Khusus" heading="Hari Khusus" subtitle="Atur tanggal tanpa KBM atau jadwal pulang cepat">
    <x-admin.page title="Hari Khusus" subtitle="Pengecualian kalender sekolah untuk acara dan perubahan jam pulang">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-hari-khusus" data-modal-title="Tambah Hari Khusus">Tambah Hari</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-alert type="info" class="mb-4">
        Hari tanpa KBM juga meniadakan tugas piket dan pengisian jurnal. Pulang cepat membatasi kegiatan sekolah sampai jam yang ditentukan.
    </x-alert>

    @if ($hariKhusus->isEmpty())
        <x-ui.empty icon="event_busy" title="Belum ada hari khusus" desc="Tambahkan tanggal acara, hari tanpa KBM, atau jadwal pulang cepat." />
    @else
        <x-admin.table :head="['Tanggal', 'Acara', 'Pengaturan', 'Aksi']">
            @foreach ($hariKhusus as $hari)
                <tr class="hover:bg-surface/60">
                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-ink">{{ $hari->tanggal->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3 text-ink">{{ $hari->nama }}</td>
                    <td class="px-4 py-3 text-muted">
                        {{ $jenisLabel[$hari->jenis] ?? $hari->jenis }}
                        @if ($hari->jenis === 'pulang_cepat' && $hari->jam_selesai)
                            · {{ $hari->jam_selesai->format('H:i') }}
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-hari-khusus"
                            edit-title="Ubah Hari Khusus"
                            :edit-id="$hari->id"
                            :edit-fill="[
                                'tanggal' => $hari->tanggal->toDateString(),
                                'nama' => $hari->nama,
                                'jenis' => $hari->jenis,
                                'jam_selesai' => $hari->jam_selesai?->format('H:i'),
                            ]"
                            :delete-action="route('master.hari-khusus.destroy', $hari)"
                            delete-confirm="Hapus pengaturan hari khusus ini?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-hari-khusus" title="Hari Khusus" errorBag="hariKhusus">
        @if ($errors->getBag('hariKhusus')->any())
            <x-alert type="error" class="mb-4">{{ $errors->getBag('hariKhusus')->first() }}</x-alert>
        @endif

        <form method="POST" action="{{ route('master.hari-khusus.save') }}" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="id" value="{{ old('id') }}">
            <x-ui.input label="Tanggal" name="tanggal" type="date" :value="old('tanggal', today()->toDateString())" required errorBag="hariKhusus" />
            <x-ui.input label="Nama Acara" name="nama" placeholder="Contoh: Maulid Nabi" :value="old('nama')" required errorBag="hariKhusus" />

            <label class="flex flex-col gap-1.5 text-xs font-semibold text-ink">
                <span>Jenis Hari</span>
                <select name="jenis" data-hari-khusus-jenis required class="h-[52px] rounded-xl border border-surface-alt bg-card px-4 text-sm text-ink outline-none focus:border-navy">
                    @foreach ($jenisLabel as $jenis => $label)
                        <option value="{{ $jenis }}" @selected($jenisAwal === $jenis)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis', 'hariKhusus') <span class="font-medium text-alpha">{{ $message }}</span> @enderror
            </label>

            <div data-hari-khusus-jam @if ($jenisAwal !== 'pulang_cepat') hidden @endif>
                <x-ui.input label="Jam Selesai Kegiatan" name="jam_selesai" type="time" :value="old('jam_selesai', '11:00')" :required="$jenisAwal === 'pulang_cepat'" errorBag="hariKhusus" />
                @error('jam_selesai', 'hariKhusus') <span class="mt-1 block text-xs font-medium text-alpha">{{ $message }}</span> @enderror
            </div>

            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save" class="flex-1">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close class="flex-1">Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>

    @push('scripts')
        <script>
            (() => {
                const modal = document.getElementById('modal-hari-khusus');
                const jenis = modal?.querySelector('[data-hari-khusus-jenis]');
                const wrapJam = modal?.querySelector('[data-hari-khusus-jam]');
                const jamSelesai = modal?.querySelector('[name="jam_selesai"]');

                const sinkronkanJenis = () => {
                    const pulangCepat = jenis?.value === 'pulang_cepat';
                    if (wrapJam) wrapJam.hidden = !pulangCepat;
                    if (jamSelesai) jamSelesai.required = pulangCepat;
                };

                jenis?.addEventListener('change', sinkronkanJenis);
                modal?.addEventListener('modal:open', sinkronkanJenis);
                sinkronkanJenis();
            })();
        </script>
    @endpush
</x-layouts.admin>
