@props([
    'title' => null,
    'center' => true,   // false untuk form panjang
])

<!DOCTYPE html>
<html lang="id" class="antialiased">
<x-layouts.head :title="$title" />

<body class="min-h-screen bg-surface text-ink">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col px-4 py-8 sm:px-6 sm:py-12">
        <div @class(['w-full rounded-2xl bg-card p-6 shadow-[var(--shadow-card)] sm:p-8', 'my-auto' => $center])>
            {{ $slot }}
        </div>
    </div>

    @stack('scripts')
</body>
</html>
