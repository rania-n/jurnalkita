@props([
    'action',
    'method' => 'POST',
    'submit' => 'Simpan',
])

<form method="POST" action="{{ $action }}" class="max-w-xl">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="flex flex-col gap-4 rounded-xl border border-surface-alt bg-card p-6">
        {{ $slot }}
    </div>

    <div class="mt-4 flex gap-2">
        <x-ui.button type="submit" icon="save">{{ $submit }}</x-ui.button>
        @isset($cancel)
            <x-ui.button :href="$cancel" variant="secondary">Batal</x-ui.button>
        @endisset
    </div>
</form>
