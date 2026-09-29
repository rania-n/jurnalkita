@props(['label' => 'Hubungi Admin'])

@if ($adminContactUrl)
    <a href="{{ $adminContactUrl }}" @if (str_starts_with($adminContactUrl, 'https://')) target="_blank" rel="noopener" @endif {{ $attributes->class('font-semibold text-navy underline underline-offset-2') }}>{{ $label }}</a>
@else
    <span {{ $attributes }}>{{ $label }} di sekolah (kontak belum tersedia)</span>
@endif
