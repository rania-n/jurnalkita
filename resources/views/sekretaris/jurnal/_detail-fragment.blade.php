{{--
    Isi popup "Periksa" di Verifikasi Jurnal -- ini SATU-SATUNYA cara lihat
    detail jurnal sekarang, nggak ada lagi halaman penuh terpisah
    (sekretaris.jurnal.show) buat ini -- sengaja dihapus, sama pola kayak
    Jurnal Guru & Dispensasi yang udah dibereskan duluan. Nggak ada layout &
    page-header di sini karena ini nempel di dalam modal (judulnya udah dari
    data-modal-title).

    Variabel yang wajib ada di scope pemanggil: $jurnal
--}}
@php
    $statusGuru = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];
    $statusAbsen = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $rekap = $jurnal->absensis->countBy('status');
    $bisaVerifikasi = $jurnal->menungguPemeriksaan();
    $jamJurnal = \App\Support\Waktu::rentangJam($jurnal->jam_ke_mulai, $jurnal->jam_ke_selesai, $jurnal->tanggal);
@endphp

<div class="flex flex-col gap-4">
    @if (! $bisaVerifikasi)
        <x-alert :type="$jurnal->verifikasiAbsen() || $jurnal->status_verifikasi === 'terverifikasi' ? 'success' : ($jurnal->otomatisDiverifikasi() ? 'info' : 'error')">
            @if ($jurnal->verifikasiAbsen())
                Tugas untuk siswa otomatis disetujui. Pengurus kelas tidak perlu memeriksa.
            @elseif ($jurnal->status_verifikasi === 'terverifikasi')
                Sudah diverifikasi{{ $jurnal->verifikator ? ' oleh '.$jurnal->verifikator->nama : '' }}.
            @else
                Sudah diminta revisi: {{ $jurnal->catatan_verifikasi }}
            @endif
        </x-alert>
    @endif

    {{-- Ringkasan Hadir/Sakit/Izin/Alpha/Dispensasi ditaruh paling atas --
         yang paling sering dicek duluan pas periksa jurnal. --}}
    <div class="flex gap-1.5 rounded-xl border border-surface-alt bg-card p-2">
        @foreach (['hadir', 'sakit', 'izin', 'alpha', 'dispensasi'] as $s)
            <x-ui.stat :label="$statusAbsen[$s]" :tone="$s === 'dispensasi' ? 'dispen' : $s" :value="$rekap[$s] ?? 0" />
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <x-ui.field-static label="Jam Pelajaran" class="sm:col-span-2">
            JP {{ $jurnal->jam_ke_mulai }}–{{ $jurnal->jam_ke_selesai }}
            @if ($jamJurnal)
                <span class="text-muted-2">· {{ $jamJurnal }}</span>
            @endif
        </x-ui.field-static>
        <x-ui.field-static label="Status Kehadiran Guru" class="sm:col-span-2">{{ $statusGuru[$jurnal->status_guru] ?? $jurnal->status_guru }}</x-ui.field-static>

        {{-- Sinkron sama popup Lihat punya guru sendiri -- Tidak Hadir nggak
             ada Materi/Metode (nggak beneran mengajar), jangan ditampilin
             kosong. --}}
        @if ($jurnal->status_guru === 'hadir')
            <x-ui.field-static label="Materi" class="sm:col-span-2">{{ $jurnal->materi ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Metode" class="sm:col-span-2">{{ $jurnal->metode ?: '—' }}</x-ui.field-static>
        @else
            <x-ui.field-static label="Alasan" class="sm:col-span-2">{{ $jurnal->alasan ?: '—' }}</x-ui.field-static>
            <x-ui.field-static label="Tugas untuk Siswa" class="sm:col-span-2">{{ $jurnal->tugas_tambahan ?: '—' }}</x-ui.field-static>
        @endif
    </div>

    @if ($jurnal->foto_bukti)
        <div class="flex flex-col gap-1.5">
            <x-ui.label>Foto Suasana Kelas</x-ui.label>
            <a href="{{ Storage::url($jurnal->foto_bukti) }}" target="_blank" rel="noopener">
                <img src="{{ Storage::url($jurnal->foto_bukti) }}" alt="Foto suasana kelas"
                     class="max-h-56 w-full rounded-xl border border-surface-alt object-cover">
            </a>
        </div>
    @endif

    <div>
        <p class="mb-2 text-sm font-bold text-ink">Presensi ({{ $jurnal->absensis->count() }} siswa)</p>
        <x-ui.presensi-list :absensis="$jurnal->absensis->sortBy('siswa.no_absen')" />
    </div>

    @if ($bisaVerifikasi)
        <form method="POST" action="{{ route('sekretaris.jurnal.verifikasi', $jurnal) }}" class="flex flex-col gap-3">
            @csrf
            <x-ui.textarea label="Catatan (wajib jika minta revisi)" name="catatan" :rows="2" placeholder="Contoh: materi tidak sesuai dengan yang diajarkan.">{{ old('catatan') }}</x-ui.textarea>
            {{-- flex gap-2 langsung (bukan flex-col sm:flex-row) -- sejajar
                 kanan-kiri di semua ukuran layar, samain sama pola tombol
                 berpasangan lain di app. --}}
            <div class="flex gap-2">
                {{-- Jurnal absen guru nggak ada materi/isi buat "diverifikasi"
                     beneran, jadi tombolnya dibedain -- "Setujui" (bukan
                     "Verifikasi") biar nggak kesan lagi ngecek konten yang
                     memang kosong. Cuma nyetujuin (udah tahu gurunya nggak
                     masuk), sama istilahnya kayak alur Dispensasi (lihat
                     Jurnal::verifikasiAbsen()). --}}
                <x-ui.button type="submit" name="keputusan" value="terima" variant="success" icon="check" class="flex-1">
                    {{ $jurnal->verifikasiAbsen() ? 'Setujui' : 'Sesuai — Verifikasi' }}
                </x-ui.button>
                <x-ui.button type="submit" name="keputusan" value="revisi" variant="danger" icon="edit" class="flex-1" data-tombol-minta-revisi>Minta Revisi</x-ui.button>
            </div>
        </form>

        {{-- Server sebenarnya udah nolak submit "Minta Revisi" tanpa Catatan
             (required_if:keputusan,revisi), TAPI penolakannya bikin halaman
             reload penuh -- popup ini kebuka otomatis lagi (errorBag), tapi
             ISINYA cuma keisi lewat fetch AJAX yang kepicu pas tombol
             "Periksa" diklik, jadi pas kebuka ulang gini isinya kosong
             melompong tanpa pesan apa-apa (kelihatan kayak nge-hang). Dicegah
             dari sini sebelum sempat kekirim ke server sama sekali. --}}
        <script>
            (function () {
                document.querySelector('[data-tombol-minta-revisi]')?.addEventListener('click', (e) => {
                    const catatan = document.getElementById('catatan');
                    if (!catatan?.value.trim()) {
                        e.preventDefault();
                        alert('Catatan wajib diisi kalau mau minta revisi.');
                        catatan?.focus();
                    }
                });
            })();
        </script>
    @endif
</div>
