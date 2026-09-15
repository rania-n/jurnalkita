<x-layouts.app title="Daftar Siswa Sekelas" width="wide">
    <x-page-header title="Daftar Siswa" :subtitle="$kelas->nama" />

    @if ($siswas->isEmpty())
        <x-ui.empty icon="school" title="Belum ada siswa di kelas ini" />
    @else
        <div class="hidden sm:block">
            <x-admin.table :head="['No. Absen', 'Nama', 'NIS', 'Jabatan']">
                @foreach ($siswas as $s)
                    <tr>
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
                    </tr>
                @endforeach
            </x-admin.table>
        </div>

        <div class="flex flex-col gap-2 sm:hidden">
            @foreach ($siswas as $s)
                <x-ui.list-card :title="$s->nama" :meta="['No. ' . ($s->no_absen ?? '—') . ' · NIS ' . $s->nis]">
                    <x-slot:badge>
                        @if ($s->jabatan === 'pengurus')
                            <x-ui.status-badge status="terverifikasi">Pengurus</x-ui.status-badge>
                        @else
                            <span class="text-xs text-muted">Anggota</span>
                        @endif
                    </x-slot:badge>
                </x-ui.list-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
