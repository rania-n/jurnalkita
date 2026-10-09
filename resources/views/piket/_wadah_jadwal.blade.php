@php
    $tone = [
        'hadir' => 'bg-hadir-soft text-hadir',
        'tidak_hadir' => 'bg-alpha-soft text-alpha',
        'belum_diisi' => 'bg-sakit-soft text-sakit',
        'terlambat' => 'bg-alpha-soft text-alpha',
        'tidak_diisi' => 'bg-alpha-soft text-alpha',
    ];
@endphp

@foreach ($rows as $b)
    @php
        $lawan = $mode === 'kelas' ? $b['jadwal']->guru->nama : $b['jadwal']->kelas->nama;
        $labelGrup = $mode === 'kelas' ? $b['jadwal']->kelas->nama : $b['jadwal']->guru->nama;
        $cariBaris = str($labelGrup.' '.$lawan.' '.$b['jadwal']->mapel->nama)->lower();
        $bisaDiklik = $b['jurnal'] !== null;
        $tanggalBaris = \Illuminate\Support\Carbon::parse($b['tanggal']);
        $jamBaris = \App\Support\Waktu::rentangJam($b['jadwal']->jam_ke_mulai, $b['jadwal']->jam_ke_selesai, $tanggalBaris);
    @endphp
    <tr
        data-baris-monitor
        data-status="{{ $b['status'] }}"
        data-cari="{{ $cariBaris }}"
        @class([
            'bg-sakit-soft/30' => in_array($b['status'], ['tidak_diisi', 'belum_diisi']),
            'cursor-pointer hover:bg-surface/60' => $bisaDiklik,
        ])
        @if ($bisaDiklik)
            data-modal-open="modal-jurnal-detail"
            data-modal-title="Detail Jurnal — {{ $lawan }}"
            data-ajax-url="{{ route('piket.monitor.jurnal', $b['jurnal']) }}"
            tabindex="0"
        @endif
    >
        @if ($rentangBeda)
            <td class="px-3 py-2 text-muted whitespace-nowrap">{{ $tanggalBaris->translatedFormat('d M Y') }}</td>
        @endif
        <td class="px-3 py-2 text-muted">
            JP {{ $b['jadwal']->jam_ke_mulai }}–{{ $b['jadwal']->jam_ke_selesai }}
            @if ($jamBaris)
                <span class="block text-[11px] text-muted-2 sm:inline sm:text-inherit">{{ $jamBaris }}</span>
            @endif
        </td>
        <td class="px-3 py-2 text-ink">{{ $b['jadwal']->mapel->nama }}</td>
        <td class="px-3 py-2 text-muted">{{ $lawan }}</td>
        <td class="px-3 py-2">
            @if ($b['status'] === 'terlambat')
                @php
                    $isHadir = str_starts_with($b['statusLabel'], 'Hadir');
                @endphp
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold {{ $isHadir ? 'bg-hadir-soft text-hadir' : 'bg-alpha-soft text-alpha' }}">
                    {{ Str::before($b['statusLabel'], ' (Terlambat)') }} <span class="text-alpha ml-1">(Terlambat)</span>
                </span>
            @else
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold {{ $tone[$b['status']] }}">
                    {{ $b['statusLabel'] }}
                </span>
            @endif
        </td>
        <td class="px-3 py-2 text-muted">
            {{ $b['jurnal']->materi ?? ($b['jurnal']->tugas_tambahan ?? '—') }}
            @if ($bisaDiklik)
                <x-icon name="chevron_right" :size="16" class="ml-1 inline text-muted-2 align-middle" />
            @endif
        </td>
    </tr>
@endforeach
