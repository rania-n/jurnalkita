@php
    $q       = request('cari');
    $userId  = request('user');
    $dari    = request('dari');
    $sampai  = request('sampai');

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

    {{-- Filter --}}
    <form method="GET" action="{{ route('master.audit-log.index') }}"
          class="mb-4 flex flex-wrap items-end gap-3">
        {{-- Cari --}}
        <label class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-muted-2">Cari</span>
            <input type="search" name="cari" value="{{ $q }}"
                   placeholder="Aksi atau deskripsi…"
                   class="h-10 rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy w-56" />
        </label>

        {{-- Filter user --}}
        <label class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-muted-2">User</span>
            <span class="relative">
                <select name="user" onchange="this.form.requestSubmit()"
                        class="h-10 w-48 appearance-none rounded-lg border border-surface-alt bg-card pl-3 pr-8 text-sm font-medium text-ink outline-none focus:border-navy">
                    <option value="">Semua User</option>
                    @foreach ($userList as $u)
                        <option value="{{ $u->id }}" @selected((string) $userId === (string) $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
                <x-icon name="expand_more" :size="18" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-muted" />
            </span>
        </label>

        {{-- Dari --}}
        <label class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-muted-2">Dari</span>
            <input type="date" name="dari" value="{{ $dari }}"
                   class="h-10 rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy" />
        </label>

        {{-- Sampai --}}
        <label class="flex flex-col gap-1">
            <span class="text-xs font-semibold text-muted-2">Sampai</span>
            <input type="date" name="sampai" value="{{ $sampai }}"
                   class="h-10 rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy" />
        </label>

        <button type="submit"
                class="h-10 rounded-lg bg-navy px-4 text-sm font-semibold text-white hover:bg-navy/90">
            Terapkan
        </button>

        @if ($q || $userId || $dari || $sampai)
            <a href="{{ route('master.audit-log.index') }}"
               class="h-10 flex items-center rounded-lg border border-surface-alt px-4 text-sm font-semibold text-muted hover:bg-surface">
                Reset
            </a>
        @endif
    </form>

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
