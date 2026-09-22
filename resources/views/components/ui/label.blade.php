@props(['for' => null, 'required' => false])

{{--
    Tanda bintang merah kalau field-nya wajib -- biar guru/siswa/dll langsung
    kelihatan mana yang HARUS diisi vs opsional, tanpa harus coba submit dulu
    baru tau dari pesan error. Field opsional sengaja TIDAK dikasih label
    "(opsional)" apa pun -- polos aja, biar sinyalnya cuma satu arah (kalau
    nggak ada bintang = opsional) dan nggak berisik.
--}}
<label @if ($for) for="{{ $for }}" @endif {{ $attributes->class('text-sm font-semibold text-ink') }}>
    {{ $slot }}
    @if ($required)
        <span class="text-alpha" aria-hidden="true">*</span>
        <span class="sr-only">(wajib diisi)</span>
    @endif
</label>
