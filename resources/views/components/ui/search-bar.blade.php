@props([
    'placeholder' => 'Cari...',
    'name' => 'q',
])

{{-- Putih + border (bukan abu-abu polos) biar kelihatan jelas ini kolom input,
     bukan sekadar dekorasi -- dan h-10 (bukan h-11) biar nggak kebesaran.
     type="text" (bukan "search") biar nggak muncul tombol silang bawaan
     browser dobel sama tombol Reset kita sendiri di bawah. --}}
<div class="flex h-10 items-center gap-2 rounded-lg border border-surface-alt bg-card px-3" data-search-bar>
    <x-icon name="search" :size="16" class="shrink-0 text-muted" />
    <input
        type="text"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        data-search-input
        {{ $attributes->class('w-full border-none bg-transparent text-sm text-ink outline-none placeholder:text-muted') }}
    >
    <button
        type="button"
        data-search-reset
        hidden
        class="flex shrink-0 items-center gap-1 text-xs font-semibold text-muted hover:text-alpha"
    >
        <x-icon name="close" :size="13" class="shrink-0" />
        Reset
    </button>
</div>

@once
    @push('scripts')
        <script>
            // Delegasi global -- satu skrip ini nyakup SEMUA search bar di
            // halaman manapun (nggak perlu diulang per pemanggil). Tombol
            // Reset cuma muncul kalau kolomnya keisi, dan ngosongin + kirim
            // event "input" biar filter JS masing-masing halaman ikut jalan.
            document.addEventListener('input', (e) => {
                if (!e.target.matches('[data-search-input]')) return;
                const tombol = e.target.closest('[data-search-bar]')?.querySelector('[data-search-reset]');
                if (tombol) tombol.hidden = !e.target.value;
            });

            document.addEventListener('click', (e) => {
                const tombol = e.target.closest('[data-search-reset]');
                if (!tombol) return;
                const input = tombol.closest('[data-search-bar]')?.querySelector('[data-search-input]');
                if (!input) return;
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                tombol.hidden = true;
                input.focus();
            });

            document.querySelectorAll('[data-search-input]').forEach((input) => {
                const tombol = input.closest('[data-search-bar]')?.querySelector('[data-search-reset]');
                if (tombol) tombol.hidden = !input.value;
            });
        </script>
    @endpush
@endonce
