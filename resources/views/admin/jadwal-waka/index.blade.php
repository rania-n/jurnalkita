@php
    $hariLabel = config('akademik.hari');

    $rows = \App\Models\JadwalWaka::with('user')
        ->orderByRaw(\App\Support\Db::hariOrder())
        ->get();

    $wakaList = \App\Models\User::where('role', 'waka')->orderBy('name')->get(['id', 'name']);
    $adaJadwal = $rows->isNotEmpty();
@endphp

<x-layouts.admin title="Jadwal Waka" heading="Jadwal Waka">
    <x-admin.page title="Jadwal Shift Waka Kesiswaan" subtitle="Waka mana yang bertugas konfirmasi dispensasi tiap hari">
        <x-slot:action>
            <x-ui.button type="button" icon="add" data-modal-open="modal-waka" data-modal-title="Tambah Jadwal Waka">Tambah Jadwal</x-ui.button>
        </x-slot:action>
    </x-admin.page>

    <x-alert type="info" class="mb-4">
        @if ($adaJadwal)
            Link WA persetujuan dispensasi otomatis dikirim ke Waka yang gilirannya hari itu.
        @else
            Belum diatur sama sekali — selama kosong, <strong>semua akun Waka</strong> dianggap
            standby tiap hari (aman, tapi belum ada pembagian giliran).
        @endif
    </x-alert>

    @if ($rows->isEmpty())
        <x-ui.empty title="Belum ada jadwal shift Waka" />
    @else
        <x-admin.table :head="['Hari', 'Waka Bertugas', '']">
            @foreach ($rows as $j)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $hariLabel[$j->hari] ?? $j->hari }}</td>
                    <td class="px-4 py-3 text-muted">{{ $j->user?->name }}</td>
                    <td class="px-4 py-3">
                        <x-admin.row-actions
                            edit-modal="modal-waka"
                            edit-title="Ubah Jadwal Waka"
                            :edit-id="$j->id"
                            :edit-fill="['hari' => $j->hari, 'user_id' => $j->user_id]"
                            :delete-action="route('master.jadwal-waka.destroy', $j)"
                            delete-confirm="Hapus jadwal waka ini?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.modal id="modal-waka" title="Tambah Jadwal Waka">
        <form method="POST" action="{{ route('master.jadwal-waka.save') }}" class="flex flex-col gap-4">
            @csrf
            <x-ui.select label="Hari" name="hari">
                <option value="" disabled selected hidden>Pilih hari</option>
                @foreach ($hariLabel as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </x-ui.select>
            <x-ui.select label="Waka Bertugas" name="user_id">
                <option value="" disabled selected hidden>Pilih waka</option>
                @foreach ($wakaList as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
            </x-ui.select>
            <div class="mt-1 flex gap-2">
                <x-ui.button type="submit" icon="save">Simpan</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-close>Batal</x-ui.button>
            </div>
        </form>
    </x-admin.modal>
</x-layouts.admin>
