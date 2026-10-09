@props(['text', 'limit' => 100])

@if (mb_strlen($text) > $limit)
    {{-- Teks ringkas dan teks lengkap berada di dalam summary yang sama dan saling
         bergantian lewat group-open, sehingga isinya tidak tampil dobel saat dibuka. --}}
    <details {{ $attributes->class('group min-w-0 break-words') }}>
        <summary class="flex cursor-pointer list-none items-start gap-1.5 [&::-webkit-details-marker]:hidden">
            <span class="group-open:hidden">{{ \Illuminate\Support\Str::limit($text, $limit) }}</span>
            <span class="hidden whitespace-pre-line group-open:inline">{{ $text }}</span>
            <x-icon name="help_outline" :size="16" class="mt-0.5 shrink-0" />
            <span class="sr-only">Buka keterangan lengkap</span>
        </summary>
    </details>
@else
    <p {{ $attributes->class('min-w-0 break-words') }}>{{ $text }}</p>
@endif
