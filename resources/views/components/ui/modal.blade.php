@props([
    'id',
    'title' => '',
    'size' => 'md',   // md ~32rem | lg ~44rem
])

@php $w = $size === 'lg' ? '44rem' : '32rem'; @endphp

{{--
    Modal generik (native <dialog>) dengan background blur -- versi shell-agnostic
    dari x-admin.modal, dipakai di luar area admin (notifikasi, dll). Markup &
    perilaku sengaja identik: sama-sama digerakkan initModals() di app.js lewat
    atribut data-modal-*, jadi cukup satu tempat buat maintain JS-nya.
    Buka:  <button data-modal-open="{{ $id }}">
    Tutup: tombol X / "Batal" / klik luar / Esc.
--}}
<dialog
    id="{{ $id }}"
    style="width: min({{ $w }}, calc(100vw - 2rem))"
    class="fixed inset-0 m-auto h-fit max-h-[calc(100dvh-2rem)] overflow-visible rounded-2xl border-0 bg-card p-0 text-ink shadow-2xl backdrop:bg-navy/30 backdrop:backdrop-blur-sm"
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

@if ($errors->any())
    <script>document.getElementById(@js($id))?.showModal();</script>
@endif
