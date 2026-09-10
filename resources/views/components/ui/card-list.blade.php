{{-- Daftar kartu: 1 kolom di HP, 2 kolom di desktop. --}}
<div {{ $attributes->class('flex flex-col gap-3 lg:grid lg:grid-cols-2') }}>
    {{ $slot }}
</div>
