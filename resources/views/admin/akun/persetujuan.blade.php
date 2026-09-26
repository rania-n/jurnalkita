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
                        @if ($u->guru)
                            <a href="{{ route('master.guru.show', $u->guru) }}" class="text-navy hover:underline">Guru · {{ $u->guru->nama }}</a>
                        @elseif ($u->siswa)
                            <a href="{{ route('master.siswa.show', $u->siswa) }}" class="text-navy hover:underline">Siswa · {{ $u->siswa->kelas?->nama }}</a>
                        @else — @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <form method="POST" action="{{ route('master.akun.approve', $u) }}" class="contents">
                                @csrf
                                <button class="flex h-8 items-center gap-1 rounded-lg bg-hadir-soft px-2.5 text-xs font-bold text-hadir hover:bg-[#bef3ab]">Setujui</button>
                            </form>
                            {{-- data-confirm di tombol, bukan di <form> -- pola yang sama
                                 dengan perbaikan bug di halaman lain hari ini. --}}
                            <form method="POST" action="{{ route('master.akun.reject', $u) }}" class="contents">
                                @csrf
                                <button class="flex h-8 items-center gap-1 rounded-lg bg-alpha-soft px-2.5 text-xs font-bold text-alpha hover:bg-[#fecdd3]" data-confirm="Tolak pendaftaran {{ $u->name }}?">Tolak</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</x-layouts.admin>
