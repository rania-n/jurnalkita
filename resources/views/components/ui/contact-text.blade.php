@props(['text'])

@foreach (preg_split('/(hubungi admin(?:istrator)?)/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE) as $part)
    @if (preg_match('/^hubungi admin(?:istrator)?$/iu', $part))
        <x-ui.admin-contact :label="$part" />
    @else
        {{ $part }}
    @endif
@endforeach
