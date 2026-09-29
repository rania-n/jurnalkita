{{--
    1 baris jam pelajaran -- dipakai bareng modal "Edit" & "Kategori Baru"
    (dulu markup-nya dobel persis di 2 tempat, sekarang 1 sumber). JS terkait
    (tambah/hapus baris, penomoran) ada di index.blade.php,
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
<div class="flex items-center gap-2 border-b border-surface-alt/60 py-2 last:border-0" data-jp-row>
    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-xs font-bold text-ink" data-jp-no>{{ $nomor }}</span>
    <input type="time" name="mulai[]" data-jp-mulai value="{{ $jp?->mulai?->format('H:i') ?? '07:00' }}" required class="h-8 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-xs outline-none focus:border-navy">
    <input type="time" name="selesai[]" data-jp-selesai value="{{ $jp?->selesai?->format('H:i') ?? '07:40' }}" required class="h-8 w-28 shrink-0 rounded-lg border border-surface-alt bg-card px-2 text-xs outline-none focus:border-navy">
    <input type="text" name="keterangan[]" value="{{ $jp?->keterangan }}" placeholder="Catatan (opsional)" class="h-8 min-w-[8rem] flex-1 rounded-lg border border-surface-alt bg-card px-2.5 text-xs outline-none focus:border-navy">
    <button type="button" data-jp-remove class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-alpha-soft text-alpha hover:bg-[#fecdd3]" aria-label="Hapus baris">
        <x-icon name="delete" :size="15" />
    </button>
</div>
