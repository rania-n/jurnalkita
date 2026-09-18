@php
    $user = auth()->user();
    $perluApproval = \App\Models\Dispensasi::where('status_piket', 'approved')
        ->where('status_waka', 'pending')->count();

    $adaJadwalWaka = \App\Models\User::where('role', 'waka')->whereHas('jadwalWakas')->exists();
    $bertugasHariIni = $user->wakaBertugasHariIni();
    $wakaBertugas = (! $bertugasHariIni && $adaJadwalWaka) ? \App\Models\User::wakaUntukHariIni() : null;

    // K2: dulu dasbor cuma nampilin 1 hitungan (perlu approval) -- sekarang
    // dilengkapi statistik bulan berjalan + daftar dispensasi terbaru, biar
    // Waka lihat aktivitas tanpa harus buka Antrean Dispensasi dulu.
    $dispensasiBulanIni = \App\Models\Dispensasi::whereMonth('tanggal', now()->month)
        ->whereYear('tanggal', now()->year);
    $statistikBulanIni = [
        'diajukan' => (clone $dispensasiBulanIni)->count(),
        'disetujui' => (clone $dispensasiBulanIni)->where('status_akhir', 'approved')->count(),
        'ditolak' => (clone $dispensasiBulanIni)->where('status_akhir', 'rejected')->count(),
    ];
    $dispensasiTerbaru = \App\Models\Dispensasi::with('siswa.kelas')->latest('created_at')->limit(5)->get();

    // Waka juga bisa punya jadwal mengajar sendiri (bukan cuma approve
    // dispensasi) -- kalau akunnya terhubung ke data Guru, tampilin jadwal hari
    // ini sama kayak dashboard guru biasa.
    $guruWaka = $user->guru;
    $hariIniWaka = \App\Support\HariSekolah::hariIni();
    $jadwalHariIniWaka = $guruWaka && $hariIniWaka
        ? $guruWaka->jadwals()->with('kelas', 'mapel')->where('hari', $hariIniWaka)->orderBy('jam_ke_mulai')->get()
        : collect();
    $sudahDiisiWaka = $guruWaka
        ? $guruWaka->jurnals()->whereDate('tanggal', today())->pluck('jadwal_id')->all()
        : [];
@endphp

<x-layouts.app title="Beranda Waka" width="wide">
    <x-page-header title="Beranda Waka Kesiswaan" subtitle="Persetujuan dispensasi tahap 2" />

    <x-ui.jam-sekarang :jp-sekarang="\App\Support\Waktu::jpAktifSekarang()" />

    @if ($adaJadwalWaka)
        @if ($bertugasHariIni)
            <x-alert type="info" class="mb-4">Anda bertugas konfirmasi dispensasi <strong>hari ini</strong>.</x-alert>
        @else
            <x-alert type="info" class="mb-4">
                Bukan giliran Anda hari ini
                @if ($wakaBertugas) — link WA persetujuan otomatis dikirim ke <strong>{{ $wakaBertugas->name }}</strong>. @endif
                Anda tetap bisa membuka &amp; memutuskan dispensasi kapan saja kalau perlu.
            </x-alert>
        @endif
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <a href="{{ route('dispensasi.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="approval" :size="24" />
            </span>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">Antrean Dispensasi</p>
                <p class="text-xs text-muted">{{ $perluApproval }} pengajuan menunggu persetujuan Anda</p>
            </div>
            <x-icon name="chevron_right" :size="20" class="text-muted" />
        </a>

        <a href="{{ route('piket.monitor.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="monitoring" :size="24" />
            </span>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">Monitor Piket</p>
                <p class="text-xs text-muted">Pantau kehadiran guru hari ini di semua kelas</p>
            </div>
            <x-icon name="chevron_right" :size="20" class="text-muted" />
        </a>

        <a href="{{ route('rekap.siswa.index') }}" class="press flex items-center gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="bar_chart" :size="24" />
            </span>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">Rekap Kehadiran Siswa</p>
                <p class="text-xs text-muted">Lintas kelas, buat evaluasi kedisiplinan</p>
            </div>
            <x-icon name="chevron_right" :size="20" class="text-muted" />
        </a>
    </div>

    @if ($guruWaka)
        <div class="mb-3 mt-6 flex items-center justify-between">
            <h2 class="text-sm font-bold text-ink">Jadwal Mengajar Hari Ini</h2>
            <a href="{{ route('jurnal.index') }}" class="text-sm font-semibold text-navy">Riwayat Jurnal →</a>
        </div>

        @if ($jadwalHariIniWaka->isEmpty())
            <x-ui.empty icon="event_busy" title="Tidak ada jadwal mengajar hari ini" desc="Mau isi jurnal untuk jadwal lain? Pilih dari daftar jadwal Anda.">
                <x-ui.button :href="route('jurnal.create')" icon="edit_note" class="mt-2">Isi Jurnal</x-ui.button>
            </x-ui.empty>
        @else
            <x-ui.card-list>
                @foreach ($jadwalHariIniWaka as $j)
                    <x-ui.list-card
                        :title="$j->mapel->nama"
                        :meta="[$j->kelas->nama . ' · JP ' . $j->jam_ke_mulai . '–' . $j->jam_ke_selesai, 'Ruang ' . ($j->ruang ?? '-')]"
                    >
                        <x-slot:actions>
                            @if (in_array($j->id, $sudahDiisiWaka))
                                <x-ui.action-button label="Sudah diisi" icon="check_circle" variant="success" href="{{ route('jurnal.index') }}" />
                            @else
                                <x-ui.action-button label="Isi Jurnal" icon="edit_note" variant="info" :href="route('jurnal.create', ['jadwal' => $j->id])" />
                            @endif
                        </x-slot:actions>
                    </x-ui.list-card>
                @endforeach
            </x-ui.card-list>
        @endif
    @endif

    <h2 class="mb-2 mt-6 text-sm font-bold text-ink">Dispensasi Bulan Ini ({{ now()->translatedFormat('F Y') }})</h2>
    <div class="mb-6 flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
        <x-ui.stat label="Diajukan" tone="izin" :value="$statistikBulanIni['diajukan']" />
        <x-ui.stat label="Disetujui" tone="hadir" :value="$statistikBulanIni['disetujui']" />
        <x-ui.stat label="Ditolak" tone="alpha" :value="$statistikBulanIni['ditolak']" />
    </div>

    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-sm font-bold text-ink">Dispensasi Terbaru</h2>
        <a href="{{ route('dispensasi.index') }}" class="text-sm font-semibold text-navy">Lihat Semua →</a>
    </div>

    @if ($dispensasiTerbaru->isEmpty())
        <x-ui.empty icon="fact_check" title="Belum ada dispensasi" />
    @else
        <x-ui.card-list>
            @foreach ($dispensasiTerbaru as $d)
                <x-ui.list-card
                    :title="$d->siswa->nama"
                    :meta="[$d->siswa->kelas?->nama . ' · ' . $d->labelTanggal(), str($d->alasan)->limit(50)]"
                >
                    <x-slot:badge>
                        @if ($d->sudahKadaluarsa())
                            <x-ui.status-badge status="kadaluarsa">Kadaluarsa</x-ui.status-badge>
                        @else
                            <x-ui.status-badge :status="['pending' => 'menunggu', 'approved' => 'disetujui', 'rejected' => 'ditolak'][$d->status_akhir]">
                                {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$d->status_akhir] }}
                            </x-ui.status-badge>
                        @endif
                    </x-slot:badge>
                    <x-slot:actions>
                        {{-- Ke Riwayat Dispensasi bawa ?lihat=<id> -- popup detailnya
                             kebuka otomatis di sana (nggak ada lagi halaman detail
                             yang berdiri sendiri, lihat DispensasiController@index). --}}
                        <x-ui.action-button label="Detail" icon="badge" :href="route('dispensasi.index', ['lihat' => $d->id])" />
                    </x-slot:actions>
                </x-ui.list-card>
            @endforeach
        </x-ui.card-list>
    @endif
</x-layouts.app>
