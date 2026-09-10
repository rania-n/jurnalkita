<x-layouts.app title="Detail Dispensasi">
    <x-page-header
        title="Detail Dispensasi"
        subtitle="Detail dispensasi siswa."
        :back="route('dispensasi.index')"
    />

    <div class="flex flex-col gap-4">
        <div class="rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
            <p class="text-lg font-bold text-ink">Dude Fahrezi</p>
            <p class="text-sm text-muted">XI RPL 2</p>
            <p class="mt-1 text-sm font-semibold text-ink">Tanggal: 06-09-2026</p>
        </div>

        <x-ui.field-static label="Alasan Dispensasi">Lomba Futsal Tingkat Nasional</x-ui.field-static>
        <x-ui.field-static label="No. Telepon">085648830046</x-ui.field-static>

        <div class="flex flex-col gap-1.5">
            <x-ui.label>Surat Dispensasi / Izin</x-ui.label>
            <a href="#" class="flex items-center gap-2 rounded-xl border border-surface-alt bg-card px-4 py-3">
                <x-icon name="description" :size="20" class="shrink-0 text-navy" />
                <span class="flex-1 truncate text-sm text-ink">Surat_Dispensasi_Lomba_Futsal.pdf</span>
                <span class="text-xs font-bold text-navy">LIHAT</span>
            </a>
        </div>

        <div class="flex flex-col gap-1.5">
            <x-ui.label>Status Persetujuan</x-ui.label>
            <div class="flex flex-col gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]">
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-hadir-soft text-hadir">
                        <x-icon name="check" :size="18" />
                    </span>
                    <div>
                        <p class="text-sm font-bold text-ink">Staff Piket: Disetujui</p>
                        <p class="text-xs text-muted">Oleh: Bpk. Hariyadi · 09:12</p>
                    </div>
                </div>
                <div class="h-px bg-surface-alt"></div>
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sakit-soft text-sakit">
                        <x-icon name="schedule" :size="18" />
                    </span>
                    <div>
                        <p class="text-sm font-bold text-ink">Waka Kesiswaan: Menunggu</p>
                        <p class="text-xs text-muted">Proses peninjauan dokumen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-ui.sticky-bar>
        <div class="flex gap-3">
            <x-ui.button type="button" variant="success" block icon="check">Setujui</x-ui.button>
            <x-ui.button type="button" variant="danger" block icon="close" data-confirm="Yakin tolak pengajuan dispensasi ini?">Tolak</x-ui.button>
        </div>
    </x-ui.sticky-bar>
</x-layouts.app>
