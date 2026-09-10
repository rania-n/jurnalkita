@props(['variant' => 'icon'])

<form method="POST" action="{{ route('logout') }}" class="contents">
    @csrf
    @if ($variant === 'full')
        <button type="submit" class="press flex w-full items-center justify-center gap-2 rounded-xl border border-alpha/25 bg-alpha-soft px-5 py-3 text-base font-semibold text-alpha hover:bg-[#fecdd3]">
            <x-icon name="logout" :size="20" />
            Keluar
        </button>
    @elseif ($variant === 'nav')
        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-alpha transition-colors hover:bg-alpha-soft">
            <x-icon name="logout" :size="20" />
            Keluar
        </button>
    @else
        <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-full bg-surface-alt text-muted transition-colors hover:bg-alpha-soft hover:text-alpha" aria-label="Keluar">
            <x-icon name="logout" :size="18" />
        </button>
    @endif
</form>
