{{-- Referensi jurnal publik: hanya informasi guru/kelas/mapel, tanpa data siswa. --}}
@if ($jurnals->isEmpty())
    <p class="py-6 text-center text-sm text-muted-2">Belum ada jurnal yang diisi hari ini.</p>
@else
    <x-ui.card-list class="grid-fill-last">
        @foreach ($jurnals as $jurnal)
            @php
                $judul = ($jurnal->jadwal->mapel->nama ?? '—').' — '.($jurnal->jadwal->kelas->nama ?? '—');
            @endphp
            <x-ui.list-card
                :title="$judul"
                :meta="['JP '.$jurnal->jam_ke_mulai.'–'.$jurnal->jam_ke_selesai.' · '.($jurnal->guru->nama ?? '—')]"
            >
                <x-slot:badge>
                    <x-ui.status-badge :status="$jurnal->status_guru === 'hadir' ? 'hadir' : 'alpha'">
                        {{ $jurnal->status_guru === 'hadir' ? 'Hadir' : 'Tidak Hadir' }}
                    </x-ui.status-badge>
                </x-slot:badge>
                <x-slot:actions>
                    <x-ui.action-button
                        label="Lihat"
                        icon="visibility"
                        data-modal-ajax-swap="{{ route('piket.popup-hari-ini.detail', $jurnal) }}"
                    />
                </x-slot:actions>
            </x-ui.list-card>
        @endforeach
    </x-ui.card-list>
@endif
