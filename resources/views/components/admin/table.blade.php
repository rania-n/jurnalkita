@props(['head' => []])

{{--
    <x-admin.table :head="['Nama', 'Kelas', '']">
        @forelse ($rows as $r) <tr>...</tr> @empty ... @endforelse
    </x-admin.table>
--}}
<div class="overflow-x-auto rounded-xl border border-surface-alt bg-card">
    <table class="w-full min-w-[36rem] text-left text-sm">
        @if (! empty($head))
            <thead>
                <tr class="border-b border-surface-alt">
                    @foreach ($head as $col)
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-muted-2">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-surface-alt">
            {{ $slot }}
        </tbody>
    </table>
</div>
