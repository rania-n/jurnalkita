@php
    $statusBadge = match ($user->status) { 'approved' => 'disetujui', 'rejected' => 'ditolak', default => 'menunggu' };
@endphp

<x-layouts.admin title="Detail Akun" :heading="$user->name" :subtitle="$user->roleLabel()">
    <x-admin.page :title="$user->name" :subtitle="$user->roleLabel()" :back="route('master.akun.index')" />

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.field-static label="Email" icon="mail">{{ $user->email }}</x-ui.field-static>
        <x-ui.field-static label="Status Akun" icon="verified_user">
            <x-ui.status-badge :status="$statusBadge" />
        </x-ui.field-static>
        <x-ui.field-static label="Terhubung ke" icon="link">
            @if ($user->guru)
                <a href="{{ route('master.guru.show', $user->guru) }}" class="text-navy hover:underline">Guru · {{ $user->guru->nama }}</a>
            @elseif ($user->siswa)
                <a href="{{ route('master.siswa.show', $user->siswa) }}" class="text-navy hover:underline">Siswa · {{ $user->siswa->kelas?->nama }}</a>
            @else
                —
            @endif
        </x-ui.field-static>
        <x-ui.field-static label="No. WhatsApp" icon="call">{{ $user->no_hp ?: '—' }}</x-ui.field-static>
        @if ($user->nip)
            <x-ui.field-static label="NIP" icon="badge">{{ $user->nip }}</x-ui.field-static>
        @endif
        <x-ui.field-static label="Akun Dibuat" icon="calendar_today">{{ $user->created_at?->format('d M Y, H:i') }}</x-ui.field-static>
    </div>

    <h2 class="mb-2 text-sm font-bold text-ink">Riwayat Aktivitas Akun</h2>
    @if ($riwayat->isEmpty())
        <x-ui.empty title="Belum ada riwayat aktivitas untuk akun ini" />
    @else
        <x-admin.table :head="['Waktu', 'Aksi', 'Deskripsi', 'Oleh']">
            @foreach ($riwayat as $log)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 text-sm text-muted whitespace-nowrap">{{ $log->created_at?->format('d M Y, H:i:s') }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-md bg-surface-alt px-2 py-0.5 text-xs font-bold text-ink">{{ $log->aksi }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-muted">{{ $log->deskripsi }}</td>
                    <td class="px-4 py-3 text-sm text-ink font-medium">{{ $log->user?->name ?? '—' }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
