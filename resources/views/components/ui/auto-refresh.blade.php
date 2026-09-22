@props(['url', 'interval' => 20])

{{--
    Banner ringan "ada data baru" -- di-poll berkala (initAutoRefresh() di
    app.js) ke endpoint kecil yang cuma balikin {"versi": ...} (hitungan +
    updated_at terakhir, BUKAN render ulang HTML), jadi ringan buat dicek
    tiap ~20 detik. SENGAJA nggak auto-reload sendiri begitu ketauan beda --
    guru/sekre mungkin lagi ngetik/isi form di halaman yang sama, reload
    paksa bisa bikin kerjaan yang belum disimpan ilang. Tombol "Muat Ulang"
    biar user yang mutusin kapan reload-nya.
--}}
<div data-auto-refresh data-auto-refresh-url="{{ $url }}" data-auto-refresh-interval="{{ $interval * 1000 }}" class="mb-4">
    <div data-auto-refresh-banner hidden class="flex items-center justify-between gap-3 rounded-xl border border-navy/20 bg-navy/5 px-4 py-3 text-sm">
        <span class="flex items-center gap-2 font-semibold text-navy">
            <x-icon name="autorenew" :size="18" class="shrink-0" />
            Ada data baru dari perangkat lain.
        </span>
        <button type="button" data-auto-refresh-reload class="shrink-0 rounded-lg bg-navy px-3 py-1.5 text-xs font-bold text-card">Muat Ulang</button>
    </div>
</div>
