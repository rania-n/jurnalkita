@props(['text', 'limit' => 100])

@if (mb_strlen($text) > $limit)
    <details {{ $attributes->class('min-w-0 break-words') }}>
        <summary class="flex cursor-pointer list-none items-start gap-1.5 [&::-webkit-details-marker]:hidden">
            <span>{{ \Illuminate\Support\Str::limit($text, $limit) }}</span>
            <x-icon name="help_outline" :size="16" class="mt-0.5 shrink-0" />
            <span class="sr-only">Buka keterangan lengkap</span>
        </summary>
        <p class="mt-1 whitespace-pre-line">{{ $text }}</p>
    </details>
@else
    <p {{ $attributes->class('min-w-0 break-words') }}>{{ $text }}</p>
@endif
