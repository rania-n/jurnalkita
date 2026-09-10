@props([
    'title',
    'meta' => [],   // baris keterangan di bawah judul (array string)
])

{{--
    Kartu baris daftar (data guru, kelas, siswa, jadwal, dll).
    Slot:
      - default : dipakai kalau mau isi bebas (jarang)
      - leading : avatar / ikon di kiri (opsional)
      - actions : tumpukan <x-ui.action-button> di kanan
      - badge   : <x-ui.status-badge> di atas judul (opsional)
--}}
<div {{ $attributes->class('flex items-start gap-3 rounded-2xl bg-card p-4 shadow-[var(--shadow-soft)]') }}>
    @isset($leading)
        <div class="shrink-0">{{ $leading }}</div>
    @endisset

    <div class="flex min-w-0 flex-1 flex-col gap-1">
        @isset($badge)
            <div>{{ $badge }}</div>
        @endisset

        <p class="text-[15px] font-bold leading-tight text-ink">{{ $title }}</p>

        @foreach ($meta as $line)
            <p class="text-xs leading-tight text-muted">{{ $line }}</p>
        @endforeach

        {{ $slot }}
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-col gap-1">{{ $actions }}</div>
    @endisset
</div>
