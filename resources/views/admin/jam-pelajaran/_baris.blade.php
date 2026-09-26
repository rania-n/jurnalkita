{{--
    1 baris jam pelajaran -- dipakai bareng modal "Edit" & "Kategori Baru"
    (dulu markup-nya dobel persis di 2 tempat, sekarang 1 sumber). JS terkait
    (bar durasi, toggle jeda, tambah/hapus baris) ada di index.blade.php,
    di-attach lewat event delegation ke [data-jp-rows] induknya -- baris baru
    hasil clone otomatis ikut kepasang, nggak perlu listener ulang per baris.

    Variabel opsional dari pemanggil:
      $jp     : JamPelajaran|null -- null buat baris kosong/kategori baru
      $nomor  : int, default 1
--}}
@php
    $jp ??= null;
    $nomor ??= 1;
@endphp
<div class="flex flex-col gap-2 border-b border-surface-alt/60 py-3 last:border-0" data-jp-row>
    <div class="flex flex-wrap items-center gap-2">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-sm font-bold text-ink" data-jp-no>{{ $nomor }}</span>
        <input type="time" name="mulai[]" data-jp-mulai value="{{ $jp?->mulai?->format('H:i') ?? '07:00' }}" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
        <input type="time" name="selesai[]" data-jp-selesai value="{{ $jp?->selesai?->format('H:i') ?? '07:40' }}" required class="h-9 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
        <input type="text" name="keterangan[]" value="{{ $jp?->keterangan }}" placeholder="Catatan (opsional)" class="h-9 min-w-[8rem] flex-1 rounded-lg border border-surface-alt bg-card px-2 text-sm outline-none focus:border-navy">
        <button type="button" data-jp-remove class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-alpha-soft text-alpha hover:bg-[#fecdd3]" aria-label="Hapus baris">
            <x-icon name="delete" :size="16" />
        </button>
    </div>

    {{-- Bar durasi -- klik salah satu, "Selesai" otomatis keisi (mulai + durasi),
         biar nggak perlu itung manual & durasinya konsisten antar baris. Tetap
         boleh diketik/diubah manual kalau durasinya nggak ada di daftar. --}}
    <div class="flex flex-wrap gap-1.5 pl-10">
        <span class="self-center text-xs text-muted-2">Durasi cepat:</span>
        @foreach ([30, 35, 40, 45, 60, 90] as $menit)
            <button type="button" data-jp-durasi="{{ $menit }}" class="rounded-md border border-surface-alt px-2 py-1 text-xs font-semibold text-muted-2 transition-colors hover:border-navy hover:text-navy">{{ $menit }} mnt</button>
        @endforeach
    </div>

    {{-- Jeda/istirahat sebelum baris ini -- checkbox reveal (pola sama kayak
         "Tugas khusus" di Isi Jurnal massal). Field-nya SENGAJA nggak di-disable
         pas disembunyiin (beda dari pola disable-blok yang biasa dipakai) --
         di sini butuh tetap KESUBMIT (walau kosong) di tiap baris, biar index
         array jeda_menit[]/jeda_label[] tetap sejajar sama mulai[]/selesai[]
         per baris (kalau di-disable, baris yang jeda-nya off bakal "bolong"
         nggak ngirim apa-apa & bikin index-nya geser). --}}
    <div class="pl-10">
        <label class="flex items-center gap-1.5 text-xs font-semibold text-navy">
            <input type="checkbox" data-jp-jeda-toggle @checked($jp?->jeda_sebelum_menit) class="h-3.5 w-3.5 rounded border-surface-alt text-navy focus:ring-navy">
            Ada jeda/istirahat sebelum baris ini
        </label>
        <div data-jp-jeda-fields class="mt-1.5 flex flex-wrap gap-2" @if (! $jp?->jeda_sebelum_menit) hidden @endif>
            <input type="number" name="jeda_menit[]" min="1" max="180" value="{{ $jp?->jeda_sebelum_menit }}" placeholder="Menit" class="h-8 w-24 rounded-lg border border-surface-alt bg-card px-2 text-xs outline-none focus:border-navy">
            <input type="text" name="jeda_label[]" maxlength="40" value="{{ $jp?->jeda_label }}" placeholder="Istirahat / MBG (opsional)" class="h-8 min-w-[8rem] flex-1 rounded-lg border border-surface-alt bg-card px-2 text-xs outline-none focus:border-navy">
        </div>
    </div>
</div>
