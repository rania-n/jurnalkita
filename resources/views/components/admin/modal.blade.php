@props([
    'id',
    'title' => '',
])

{{--
    Modal (native <dialog>) dengan background blur.
    Buka:  <button data-modal-open="{{ $id }}">
    Tutup: tombol X / "Batal" / klik luar / Esc.
--}}
<dialog
    id="{{ $id }}"
    class="fixed inset-0 m-auto h-fit max-h-[calc(100dvh-2rem)] w-[min(32rem,calc(100vw-2rem))] overflow-visible rounded-2xl border-0 bg-card p-0 text-ink shadow-2xl backdrop:bg-navy/30 backdrop:backdrop-blur-sm"
>
    <div class="flex items-center justify-between border-b border-surface-alt px-5 py-3.5">
        <h3 class="text-base font-bold text-ink" data-modal-title>{{ $title }}</h3>
        <button type="button" data-modal-close class="flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:bg-surface-alt hover:text-ink" aria-label="Tutup">
            <x-icon name="close" :size="18" />
        </button>
    </div>

    <div class="max-h-[calc(100dvh-8rem)] overflow-y-auto p-5">
        {{ $slot }}
    </div>
</dialog>
