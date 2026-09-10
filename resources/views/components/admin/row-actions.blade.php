@props([
    'edit' => null,          // href (mode halaman)
    'editModal' => null,     // id <dialog> (mode modal)
    'editId' => null,
    'editTitle' => 'Ubah Data',
    'editFill' => [],        // array field => value untuk mengisi form
    'detail' => null,
    'deleteAction' => null,
    'deleteConfirm' => 'Yakin hapus data ini?',
])

<div class="flex items-center justify-end gap-1">
    @if ($detail)
        <a href="{{ $detail }}" class="flex h-8 items-center gap-1 rounded-lg bg-surface-alt px-2.5 text-xs font-bold text-ink hover:bg-[#cbd5e1]">
            Detail <x-icon name="visibility" :size="14" />
        </a>
    @endif

    @if ($editModal)
        <button type="button"
            data-modal-open="{{ $editModal }}"
            data-modal-title="{{ $editTitle }}"
            @if ($editId) data-modal-id="{{ $editId }}" @endif
            data-modal-fill='@json($editFill)'
            class="flex h-8 items-center gap-1 rounded-lg bg-izin-soft px-2.5 text-xs font-bold text-izin hover:bg-[#bae6fd]">
            Ubah <x-icon name="edit" :size="14" />
        </button>
    @elseif ($edit)
        <a href="{{ $edit }}" class="flex h-8 items-center gap-1 rounded-lg bg-izin-soft px-2.5 text-xs font-bold text-izin hover:bg-[#bae6fd]">
            Ubah <x-icon name="edit" :size="14" />
        </a>
    @endif

    @if ($deleteAction)
        <form method="POST" action="{{ $deleteAction }}" class="contents" data-confirm="{{ $deleteConfirm }}">
            @csrf @method('DELETE')
            <button type="submit" class="flex h-8 items-center gap-1 rounded-lg bg-alpha-soft px-2.5 text-xs font-bold text-alpha hover:bg-[#fecdd3]">
                Hapus <x-icon name="delete" :size="14" />
            </button>
        </form>
    @endif

    {{ $slot }}
</div>
