@props([
    'title' => null,
    'center' => true,   // false untuk form panjang yang butuh scroll dari atas
])

<!DOCTYPE html>
<html lang="id" class="antialiased">
<x-layouts.head :title="$title" />

<body class="min-h-screen bg-surface text-ink">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col px-6 py-10">
        <div @class(['w-full', 'my-auto' => $center])>
            {{ $slot }}
        </div>
    </div>

    @stack('scripts')
</body>
</html>
