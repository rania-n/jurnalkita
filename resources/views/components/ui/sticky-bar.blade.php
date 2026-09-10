@props([
    'aboveNav' => true,   // true: menempel di atas bottom-nav (layar dalam app)
                          // false: menempel di dasar layar (layar auth/guest, tanpa bottom-nav)
])

{{--
    Wadah tombol aksi di bawah layar form.
    - HP: menempel di bawah (di atas bottom-nav bila ada).
    - Desktop: mengalir biasa di akhir konten.
--}}

{{-- Ruang kosong pengganti agar konten terakhir tidak tertutup bar (hanya HP). --}}
<div class="h-28 lg:hidden" aria-hidden="true"></div>

<div
    class="fixed inset-x-0 z-30 border-t border-surface-alt bg-surface px-5 py-3 sm:px-6 lg:static lg:mt-6 lg:border-0 lg:bg-transparent lg:p-0"
    style="bottom: calc({{ $aboveNav ? '4rem + ' : '' }}env(safe-area-inset-bottom));"
>
    {{-- HP: tombol lebar penuh. Desktop: mengalir di kiri, lebar secukupnya. --}}
    <div class="mx-auto flex w-full max-w-lg flex-col gap-2 lg:mx-0 lg:max-w-sm">
        {{ $slot }}
    </div>
</div>
