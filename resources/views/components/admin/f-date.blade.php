@props(['name', 'label', 'value' => null])

@php
    // Field "Dari" yang dipasangkan ke field "Sampai" (data-pasangan="sampai")
    // -- submit-nya ditangani skrip bareng penyesuaian tanggal di bawah, jadi
    // onchange yang dioper caller (biasanya "this.form.submit()") sengaja
    // TIDAK ikut dipasang di sini. Kalau dipasang dobel, urutannya kebalik:
    // form keburu submit duluan sebelum sempat nyesuain nilai "Sampai".
    $pasangan = $attributes->get('data-pasangan');
    $attributesInput = $pasangan ? $attributes->except('onchange') : $attributes;
@endphp

{{-- min-w kecil (bukan 9rem kayak dulu) -- 2 field tanggal (Dari/Sampai)
     bersebelahan gampang meluber ke kanan (horizontal scroll) di HP sempit
     (~320-375px), 9rem x 2 + gap aja udah lebih lebar dari layarnya. --}}
<label class="flex min-w-[6.5rem] flex-1 flex-col gap-1">
    <span class="text-xs font-semibold text-muted-2">{{ $label }}</span>
    <input type="date" name="{{ $name }}" value="{{ $value ?? request()->query($name) }}"
        {{ $attributesInput }}
        class="h-10 w-full rounded-lg border border-surface-alt bg-card px-3 text-sm text-ink outline-none focus:border-navy">
</label>

@if ($pasangan)
    @once
        @push('scripts')
            <script>
                (function () {
                    // Kalau "Dari" diganti jadi lebih belakang dari "Sampai"
                    // yang lagi keisi, majuin "Sampai" ikut biar rentangnya
                    // gak pernah kebalik (Sampai < Dari). Perbandingan string
                    // aman soalnya <input type=date> nilainya SELALU format
                    // ISO yyyy-mm-dd, itu ngurut sama kayak ngurut tanggal asli.
                    document.querySelectorAll('[data-pasangan]').forEach((dariInput) => {
                        const form = dariInput.closest('form');
                        const sampaiInput = form?.querySelector(`[name="${dariInput.dataset.pasangan}"]`);
                        if (!form || !sampaiInput) return;
                        dariInput.addEventListener('change', () => {
                            if (dariInput.value && sampaiInput.value && dariInput.value > sampaiInput.value) {
                                sampaiInput.value = dariInput.value;
                            }
                            form.submit();
                        });
                    });
                })();
            </script>
        @endpush
    @endonce
@endif
