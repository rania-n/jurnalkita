@props(['action', 'hideButtons' => true, 'ignore' => []])

<form method="GET" action="{{ $action }}" class="mb-4 flex flex-wrap items-end gap-3">
    {{ $slot }}
</form>
