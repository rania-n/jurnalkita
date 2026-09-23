@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'alwaysRow' => false,
    // 'sm' -- buat judul yang isinya cuma nama halaman ("Beranda") dan nggak
    // nambah info baru (sidebar-nya sendiri udah nyorot menu yang lagi aktif)
    // -- dikecilin biar nggak ngalah-ngalahin konten di bawahnya yang lebih
    // penting. Judul yang isinya sesuatu (nama form, nama kelas, dll) tetap
    // pakai default 'md'.
    'size' => 'md',
])

<div {{ $attributes->class('mb-5') }}>
    {{-- Judul panjang + tombol aksi gampang sesak berdempetan di layar sempit --
         susun ke bawah (judul dulu, tombol di bawahnya) di mobile, baru sejajar
         mulai `sm:`. --}}
    <header class="flex {{ $alwaysRow ? 'flex-row items-start justify-between' : 'flex-col items-stretch sm:flex-row sm:items-start' }} gap-3">
        {{-- Panah kembali nempel SEJAJAR sama judul (bukan baris sendiri di
             atasnya) -- pola tombol "chip" ngambang sendiri kesannya kayak
             aksi terpisah, padahal ini navigasi yang nempel ke judul. --}}
        <div class="flex flex-1 min-w-0 items-start gap-2">
            @if ($back)
                <a
                    href="{{ $back }}"
                    class="press mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface-alt hover:text-ink"
                    aria-label="Kembali"
                >
                    <x-icon name="arrow_back" :size="20" />
                </a>
            @endif
            <div class="flex flex-1 flex-col gap-0.5 min-w-0">
                <h1 @class([
                    'font-bold leading-tight text-ink',
                    'text-base lg:text-lg' => $size === 'sm',
                    'text-[22px] lg:text-[26px]' => $size !== 'sm',
                ])>{{ $title }}</h1>
                @if ($subtitle)
                    <p class="text-sm leading-snug text-muted lg:text-[15px]">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if ($slot->isNotEmpty())
            <div class="flex shrink-0 flex-wrap gap-2">{{ $slot }}</div>
        @endif
    </header>
</div>
