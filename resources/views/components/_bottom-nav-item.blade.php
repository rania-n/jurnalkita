<li class="flex-1">
    <a
        href="{{ $item['url'] }}"
        @class([
            'flex h-full flex-col items-center justify-center gap-0.5 text-[10px] font-semibold transition-colors',
            'text-navy' => $item['active'],
            'text-muted-2' => ! $item['active'],
        ])
        @if ($item['active']) aria-current="page" @endif
    >
        <x-icon :name="$item['icon']" :size="24" :fill="$item['active']" />
        <span>{{ $item['label'] }}</span>
    </a>
</li>
