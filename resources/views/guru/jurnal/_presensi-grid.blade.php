{{--
    Partial dipakai bareng Form Jurnal (create) & Ubah Jurnal (edit) -- kartu
    presensi per siswa, satu form dengan field jurnal lainnya (bukan langkah
    terpisah lagi).

    Variabel yang wajib ada di scope pemanggil:
      $siswas       : Collection<Siswa>, urut no_absen
      $presensiAwal : array [siswa_id => ['status' => ..., 'catatan' => ...]]
--}}
@php
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    $tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];
@endphp

<div class="mt-6">
    <h2 class="mb-1 text-sm font-bold text-ink">Presensi ({{ $siswas->count() }} siswa)</h2>
    <p class="mb-3 text-xs text-muted-2">Semua siswa awalnya <strong>Hadir</strong> — siswa dengan dispensasi disetujui pada jam ini otomatis <strong>Dispensasi</strong>. Ketuk status buat ubah manual bila perlu.</p>

    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-3">
        @foreach ($siswas as $s)
            @php
                $isiAwal = $presensiAwal[$s->id] ?? ['status' => 'hadir', 'catatan' => null];
                $statusAwal = old("presensi.{$s->id}.status", $isiAwal['status']);
                $catatanAwal = old("presensi.{$s->id}.catatan", $isiAwal['catatan']);
                $dariDispensasiOtomatis = $statusAwal === 'dispensasi' && str_starts_with((string) $catatanAwal, 'Dispensasi');
            @endphp
            <div class="flex flex-col gap-3 rounded-2xl bg-card p-3 shadow-[var(--shadow-soft)]">
                <div class="flex items-center gap-2.5">
                    <x-ui.avatar :label="$s->no_absen ?? '–'" :gender="$s->jenis_kelamin" />
                    <div class="flex min-w-0 flex-col">
                        <a href="{{ route('guru.siswa.show', $s) }}" class="truncate text-sm font-semibold text-ink hover:text-navy hover:underline">{{ $s->nama }}</a>
                        <span class="text-[11px] font-semibold text-muted-2">NIS: {{ $s->nis }}</span>
                    </div>
                </div>

                @if ($dariDispensasiOtomatis)
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-dispen">
                        <x-icon name="verified" :size="14" />
                        <span>Dispensasi disetujui untuk jam ini — bisa diubah bila perlu</span>
                    </div>
                @endif

                <x-ui.choice
                    :name="'presensi[' . $s->id . '][status]'"
                    :options="$statuses"
                    :tones="$tones"
                    :value="$statusAwal"
                    size="sm"
                />
                <x-ui.input
                    :name="'presensi[' . $s->id . '][catatan]'"
                    placeholder="Catatan (opsional)"
                    :value="$catatanAwal"
                />
            </div>
        @endforeach
    </div>
</div>
