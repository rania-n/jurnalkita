<x-layouts.admin title="Backup Data" heading="Backup Data">
    <x-admin.page title="Backup Data" subtitle="Unduh salinan lengkap database" />

    <div class="max-w-xl rounded-2xl border border-surface-alt bg-card p-6">
        <div class="flex items-start gap-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-surface-alt text-navy">
                <x-icon name="cloud_download" :size="24" />
            </span>
            <div>
                <p class="text-sm font-bold text-ink">Unduh Backup Sekarang</p>
                <p class="mt-1 text-xs text-muted">
                    File <code>.sql</code> berisi seluruh data (akun, jurnal, presensi, dispensasi, dll)
                    diunduh langsung ke perangkat Anda saat itu juga — tidak disimpan di server, jadi
                    simpan sendiri file-nya di tempat yang aman setelah diunduh.
                </p>
            </div>
        </div>

        <a href="{{ route('master.backup.download') }}"
           class="press mt-5 flex h-12 items-center justify-center gap-2 rounded-xl bg-navy text-base font-semibold text-card hover:bg-navy-hover">
            <x-icon name="download" :size="20" /> Unduh Backup (.sql)
        </a>
    </div>

    <h2 class="mb-2 mt-6 text-sm font-bold text-ink">Riwayat Unduh Terakhir</h2>
    @if ($riwayat->isEmpty())
        <x-ui.empty title="Belum pernah ada yang mengunduh backup" />
    @else
        <x-admin.table :head="['Waktu', 'Oleh']">
            @foreach ($riwayat as $log)
                <tr>
                    <td class="px-4 py-2.5 text-muted">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td class="px-4 py-2.5 font-semibold text-ink">{{ $log->user?->name ?? '—' }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
