@props([
    'title' => null,
    'menu' => 'default',
    'width' => 'form',   // 'form' (kolom sempit, untuk formulir) | 'wide' (untuk daftar/menu)
])

@php
    $maxW = $width === 'wide' ? 'lg:max-w-4xl' : 'lg:max-w-2xl';
@endphp

<!DOCTYPE html>
<html lang="id" class="antialiased">
<x-layouts.head :title="$title" />

<body class="min-h-screen bg-surface text-ink">
    <div class="lg:flex lg:min-h-screen">
        <x-side-nav :menu="$menu" />

        <div class="flex w-full flex-col lg:items-center">
            <main class="mx-auto w-full max-w-lg flex-1 px-5 pb-24 pt-8 sm:px-6 {{ $maxW }} lg:px-10 lg:pb-14 lg:pt-14">
                @if (session('success'))
                    <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
                @endif
                @if (session('error'))
                    <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
                @endif
                @if (session('info'))
                    <x-alert type="info" class="mb-4">{{ session('info') }}</x-alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-bottom-nav :menu="$menu" />

    @stack('scripts')
</body>
</html>
