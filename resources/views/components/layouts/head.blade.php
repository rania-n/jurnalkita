@props(['title' => null])

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1B2A4A">
    <title>{{ $title ? $title . ' · jurnalkita' : 'jurnalkita — Jurnal & Absensi Guru' }}</title>

    {{-- Font Inter (di-proxy lokal oleh laravel-vite-plugin, lihat vite.config.js) --}}
    @fonts

    {{-- Ikon Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
