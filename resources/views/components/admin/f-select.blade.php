@props(['name', 'label', 'options' => [], 'all' => 'Semua'])

@php
    // $options: ['value' => 'Label', ...]  atau  Collection of ['id','label']
    $current = request($name);
@endphp

<label class="flex flex-col gap-1">
    <span class="text-xs font-semibold text-muted-2">{{ $label }}</span>
    <span class="relative">
        <select name="{{ $name }}" onchange="this.form.requestSubmit()"
            class="h-10 w-44 appearance-none rounded-lg border border-surface-alt bg-card pl-3 pr-8 text-sm font-medium text-ink outline-none focus:border-navy">
            <option value="">{{ $all }}</option>
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
        <x-icon name="expand_more" :size="18" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-muted" />
    </span>
</label>
