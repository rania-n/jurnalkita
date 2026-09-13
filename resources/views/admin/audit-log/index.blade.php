@php
    $q       = request()->query('cari');
    $userId  = request()->query('user');
    $dari    = request()->query('dari');
    $sampai  = request()->query('sampai');

    $rows = \App\Models\AuditLog::with('user')
        ->when($q, fn ($b) => $b->where(fn ($w) => $w
            ->where('deskripsi', 'like', "%{$q}%")
            ->orWhere('aksi', 'like', "%{$q}%")
        ))
        ->when($userId, fn ($b) => $b->where('user_id', $userId))
        ->when($dari,   fn ($b) => $b->whereDate('created_at', '>=', $dari))
        ->when($sampai, fn ($b) => $b->whereDate('created_at', '<=', $sampai))
        ->latest('created_at')
        ->paginate(25)
        ->withQueryString();

    $userList = \App\Models\User::orderBy('name')->get(['id', 'name']);
@endphp

<x-layouts.admin title="Audit Log" heading="Audit Log">
    <x-admin.page title="Audit Log" subtitle="{{ $rows->total() }} entri" />

    <x-admin.filters :action="route('master.audit-log.index')">
        <x-admin.f-search placeholder="Aksi atau deskripsi..." />
        <x-admin.f-select name="user" label="User" :options="$userList->pluck('name', 'id')" all="Semua User" />
        <x-admin.f-date name="dari" label="Dari" />
        <x-admin.f-date name="sampai" label="Sampai" />
    </x-admin.filters>

    @if ($rows->isEmpty())
        <x-ui.empty title="Tidak ada log yang cocok" />
    @else
        <x-admin.table :head="['Waktu', 'User', 'Aksi', 'Deskripsi', 'IP']">
            @foreach ($rows as $log)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 text-sm text-muted whitespace-nowrap">
                        {{ $log->created_at?->format('d M Y, H:i:s') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-ink font-medium">
                        {{ $log->user?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="rounded-md bg-surface-alt px-2 py-0.5 text-xs font-bold text-ink">
                            {{ $log->aksi }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-muted">
                        {{ $log->deskripsi }}
                    </td>
                    <td class="px-4 py-3 text-xs text-muted font-mono whitespace-nowrap">
                        {{ $log->ip ?? '—' }}
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        {{-- Pagination --}}
        @if ($rows->hasPages())
            <div class="mt-4">
                {{ $rows->links() }}
            </div>
        @endif
    @endif
</x-layouts.admin>
