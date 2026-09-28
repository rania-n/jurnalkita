{{-- Isi popup "Jurnal Hari Ini" di halaman login -- TANPA login, jadi SENGAJA
     cuma nampilin info level guru/kelas/mapel, TANPA data siswa sama sekali.
     Lihat PiketController::popupHariIni() untuk detail batasannya. --}}
@if ($jurnals->isEmpty())
    <p class="py-6 text-center text-sm text-muted-2">Belum ada jurnal yang diisi hari ini.</p>
@else
    <div class="flex flex-col gap-2">
        @foreach ($jurnals as $jurnal)
            <div class="rounded-xl border border-surface-alt p-3">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-bold text-ink">{{ $jurnal->jadwal->kelas->nama ?? '—' }} · {{ $jurnal->jadwal->mapel->nama ?? '—' }}</p>
                    <span @class(['text-xs font-semibold shrink-0', 'text-hadir' => $jurnal->status_guru === 'hadir', 'text-alpha' => $jurnal->status_guru !== 'hadir'])>
                        {{ $jurnal->status_guru === 'hadir' ? 'Hadir' : 'Tidak Hadir' }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-muted">JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }} · {{ $jurnal->guru->nama ?? '—' }}</p>
                @if ($jurnal->materi)
                    <p class="mt-1 text-xs text-muted-2">{{ \Illuminate\Support\Str::limit($jurnal->materi, 80) }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif
