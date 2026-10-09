<x-layouts.guest title="Surat Izin Keluar" :center="false">
    @if ($qrUrl)
        <meta http-equiv="refresh" content="{{ $detikSisa }}">
    @endif

    @include('dispensasi._surat-konten')
</x-layouts.guest>
