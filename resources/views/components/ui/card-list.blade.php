{{-- Daftar kartu: 1 kolom di HP, 2 di desktop, 3 di layar sangat lebar. --}}
<div {{ $attributes->class('grid grid-cols-1 gap-3 md:grid-cols-2 2xl:grid-cols-3') }}>
    {{ $slot }}
</div>
