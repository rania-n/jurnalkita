@props([
    'absensis',
    'linkSiswa' => false,
])

{{--
    Daftar presensi (No/Nama/Status/Catatan) -- dipakai di 3 tempat (Detail
    Jurnal guru, verifikasi pengurus kelas, popup Monitor Piket). Desktop:
    tabel biasa. HP: kartu (bukan tabel sama sekali), biar sesuai role-role
    non-admin yang mobile-first.
--}}
<div class="hidden sm:block">
    <x-admin.table :head="['No', 'Nama', 'Status', 'Catatan']">
        @foreach ($absensis as $a)
            <tr>
                <td class="px-4 py-2.5 text-muted">{{ $a->siswa->no_absen ?? '–' }}</td>
                <td class="px-4 py-2.5 font-semibold text-ink">
                    @if ($linkSiswa)
                        <a href="{{ route('guru.siswa.show', $a->siswa) }}" class="hover:text-navy hover:underline">{{ $a->siswa->nama }}</a>
                    @else
                        {{ $a->siswa->nama }}
                    @endif
                </td>
                <td class="px-4 py-2.5"><x-ui.status-badge :status="$a->status" /></td>
                <td class="px-4 py-2.5 text-muted">{{ $a->catatan ?: '—' }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>

<div class="flex flex-col gap-2 sm:hidden">
    @foreach ($absensis as $a)
        <x-ui.list-card
            :title="$a->siswa->nama"
            :meta="array_filter(['No. ' . ($a->siswa->no_absen ?? '—'), $a->catatan])"
        >
            <x-slot:badge><x-ui.status-badge :status="$a->status" /></x-slot:badge>
            @if ($linkSiswa)
                <x-slot:actions>
                    <x-ui.action-button label="Detail" icon="badge" :href="route('guru.siswa.show', $a->siswa)" />
                </x-slot:actions>
            @endif
        </x-ui.list-card>
    @endforeach
</div>
