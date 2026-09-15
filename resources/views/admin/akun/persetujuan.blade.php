@php
    $users = \App\Models\User::with('guru', 'siswa.kelas')
        ->where('status', 'pending')
        ->orderBy('name')
        ->get();

    $roleLabel = ['admin' => 'Admin', 'guru' => 'Guru', 'siswa' => 'Pengurus Kelas', 'waka' => 'Waka Kesiswaan', 'satpam' => 'Satpam'];
@endphp

<x-layouts.admin title="Persetujuan Akun" heading="Persetujuan Akun">
    <x-admin.page
        title="Persetujuan Akun"
        subtitle="{{ $users->count() }} pendaftaran menunggu diputuskan"
    />

    @if ($users->isEmpty())
        <x-ui.empty icon="how_to_reg" title="Tidak ada pendaftaran yang menunggu" desc="Semua pendaftaran sudah diputuskan." />
    @else
        <x-admin.table :head="['Nama', 'Email', 'Peran', 'Terhubung ke', '']">
            @foreach ($users as $u)
                <tr class="hover:bg-surface/60">
                    <td class="px-4 py-3 font-semibold text-ink">{{ $u->name }}</td>
                    <td class="px-4 py-3 text-muted">{{ $u->email }}</td>
                    <td class="px-4 py-3 text-muted">{{ $roleLabel[$u->role] ?? $u->role }}</td>
                    <td class="px-4 py-3 text-muted">
                        @if ($u->guru) Guru
                        @elseif ($u->siswa) Siswa · {{ $u->siswa->kelas?->nama }}
                        @else — @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <form method="POST" action="{{ route('master.akun.approve', $u) }}" class="contents">
                                @csrf
                                <button class="flex h-8 items-center gap-1 rounded-lg bg-hadir-soft px-2.5 text-xs font-bold text-hadir hover:bg-[#bef3ab]">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('master.akun.reject', $u) }}" class="contents" data-confirm="Tolak pendaftaran {{ $u->name }}?">
                                @csrf
                                <button class="flex h-8 items-center gap-1 rounded-lg bg-alpha-soft px-2.5 text-xs font-bold text-alpha hover:bg-[#fecdd3]">Tolak</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
