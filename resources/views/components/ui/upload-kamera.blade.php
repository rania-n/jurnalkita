@props([
    'label' => null,
    'name' => 'lampiran',
    'hint' => null,
    'placeholder' => 'Wajib buka kamera',
    'required' => false,
    'errorBag' => 'default',
])

@php
    $id = $attributes->get('id', $name);
    $modalId = 'modal-kamera-'.$id;
@endphp

{{--
    Beda dari x-ui.upload biasa -- ini MAKSA buka kamera (getUserMedia), bukan
    file-picker/galeri, dan jalan sama di HP MAUPUN desktop/laptop (atribut
    HTML "capture" cuma ngaruh di browser mobile, di desktop nggak ada efeknya
    sama sekali -- makanya di sini dibikin manual pakai kamera live + jepret).
    File hasil jepretan tetap disuntikkan ke <input type=file> asli lewat
    DataTransfer, jadi validasi & submit form-nya nggak berubah sama sekali.
--}}
<div {{ $attributes->only('class')->class('flex flex-col gap-1.5') }} data-kamera-wrap>
    @if ($label)
        <x-ui.label :for="$id" :required="$required">{{ $label }}</x-ui.label>
    @endif

    <input type="file" name="{{ $name }}" id="{{ $id }}" accept="image/*" class="sr-only" data-kamera-input @if ($required) required @endif>

    <div @class([
        'flex min-h-40 w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed bg-card px-5 py-6 text-center',
        'border-[#B8C4D9]' => ! $errors->has($name, $errorBag),
        '!border-alpha' => $errors->has($name, $errorBag),
    ])>
        <img data-kamera-img hidden alt="Pratinjau foto suasana kelas" class="max-h-56 w-full rounded-lg object-cover">

        <div data-kamera-placeholder class="flex flex-col items-center gap-1.5">
            <x-icon name="photo_camera" :size="28" class="text-navy" />
            <span class="text-xs font-semibold text-navy">{{ $placeholder }}</span>
            @if ($hint)
                <span class="text-[11px] text-muted-2">{{ $hint }}</span>
            @endif
            <button type="button" data-modal-open="{{ $modalId }}" data-kamera-buka class="press mt-2 inline-flex items-center gap-1.5 rounded-lg bg-navy px-3.5 py-2 text-xs font-bold text-card">
                <x-icon name="photo_camera" :size="15" /> Buka Kamera
            </button>
        </div>

        <button type="button" data-modal-open="{{ $modalId }}" data-kamera-buka data-kamera-ulang hidden class="press mt-2 inline-flex items-center gap-1.5 rounded-lg bg-surface-alt px-3.5 py-2 text-xs font-bold text-ink">
            <x-icon name="refresh" :size="15" /> Ambil Ulang
        </button>
    </div>

    @error($name, $errorBag)
        <p class="text-xs font-medium text-alpha">{{ $message }}</p>
    @enderror

    <x-ui.modal :id="$modalId" title="Ambil Foto Suasana Kelas">
        <div class="flex flex-col gap-3">
            {{-- object-contain (BUKAN object-cover) + max-height yang bisa
                 di-toggle -- dulu dipaksa kotak 4:3 & object-cover, jadi
                 kepotong kalau rasio kamera aslinya beda (umum banget,
                 kebanyakan kamera HP bukan 4:3). Sekarang gambarnya utuh
                 semua (nggak ada yang kepotong), maks tinggi 50vh biar
                 nggak kegedean, ada tombol perbesar kalau mau lihat lebih
                 gede/penuh. --}}
            <div data-kamera-box class="relative flex max-h-[50vh] items-center justify-center overflow-hidden rounded-xl bg-ink transition-[max-height]">
                <video data-kamera-video hidden autoplay playsinline muted class="max-h-[50vh] w-full object-contain"></video>
                <canvas data-kamera-canvas hidden></canvas>
                <p data-kamera-error hidden class="flex aspect-[4/3] w-full flex-col items-center justify-center gap-2 px-6 text-center text-sm font-semibold text-card">
                    Nggak bisa buka kamera. Pastikan izin kamera diaktifkan buat browser ini, lalu coba lagi.
                </p>
                {{-- Ganti kamera depan/belakang -- ditaruh mengambang di pojok
                     video, jaga-jaga kamera yang kebuka duluan bukan yang
                     diinginkan (mis. laptop/HP tertentu salah nebak default). --}}
                <button type="button" data-kamera-ganti hidden
                    class="press absolute right-2.5 top-2.5 flex h-9 w-9 items-center justify-center rounded-full bg-ink/60 text-card"
                    aria-label="Ganti kamera depan/belakang">
                    <x-icon name="cameraswitch" :size="18" />
                </button>
                {{-- Perbesar -- video default dibatasi 50vh biar nggak
                     kegedean & modal tetap muat tombol Jepret tanpa scroll,
                     tapi kalau mau lihat lebih detail sebelum jepret, bisa
                     diperbesar ke hampir sepenuh layar. --}}
                <button type="button" data-kamera-perbesar hidden
                    class="press absolute right-2.5 bottom-2.5 flex h-9 w-9 items-center justify-center rounded-full bg-ink/60 text-card"
                    aria-label="Perbesar tampilan kamera">
                    <x-icon name="fullscreen" :size="18" />
                </button>
            </div>
            <button type="button" data-kamera-jepret class="press flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-navy text-sm font-bold text-card">
                <x-icon name="photo_camera" :size="18" /> Jepret
            </button>
        </div>
    </x-ui.modal>
</div>
