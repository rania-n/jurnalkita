@php
    $deskripsi = [
        'disiplin' => 'Guru hanya dapat mengisi jurnal saat jam pelajarannya benar-benar sedang berlangsung -- dikunci otomatis ke jadwal tersebut. Istirahat dan pergantian jam diblokir sepenuhnya, tidak dapat mengisi apa pun. Guru baru bebas memilih jadwal lain apabila sudah benar-benar pulang sekolah (untuk susulan/pengujian).',
        'bebas_hari_ini' => 'Guru bebas memilih jadwal HARI INI kapan saja sepanjang hari -- tidak dikunci ke jam pelajaran yang sedang berlangsung, tidak diblokir saat istirahat. Cocok apabila sekolah belum siap menerapkan disiplin ketat terhadap jam.',
        'bebas_selamanya' => 'Guru bebas mengisi jurnal kapan saja dan untuk tanggal berapa pun (tanggal dapat dipilih bebas). Cocok agar jurnal yang sebelumnya belum sempat dibuat dapat diakses dan dilengkapi.',
    ];
    $icon = ['disiplin' => 'lock_clock', 'bebas_hari_ini' => 'today', 'bebas_selamanya' => 'event_repeat'];
@endphp

<x-layouts.admin title="Pengaturan Isi Jurnal" heading="Pengaturan Isi Jurnal">
    <x-admin.page title="Pengaturan Isi Jurnal" subtitle="Atur seberapa ketat aturan jam untuk guru mengisi jurnal mengajar" />

    <form method="POST" action="{{ route('master.pengaturan-jurnal.save') }}">
        @csrf

        <div class="flex flex-col gap-2.5">
            @foreach (\App\Models\PengaturanJurnal::MODE_LABEL as $key => $label)
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-surface-alt bg-card p-4 has-[:checked]:border-navy has-[:checked]:bg-surface-alt/60">
                    <input
                        type="radio"
                        name="mode"
                        value="{{ $key }}"
                        class="mt-1 h-4 w-4 shrink-0 accent-navy"
                        @checked(old('mode', $pengaturan->mode) === $key)
                    >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-alt text-navy">
                        <x-icon :name="$icon[$key]" :size="18" />
                    </span>
                    <span>
                        <span class="block text-sm font-bold text-ink">{{ $label }}</span>
                        <span class="mt-0.5 block text-xs text-muted">{{ $deskripsi[$key] }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        @error('mode')
            <p class="mt-2 text-xs font-medium text-alpha">{{ $message }}</p>
        @enderror

        <button type="submit" class="press mt-5 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-navy text-base font-semibold text-card hover:bg-navy-hover">
            <x-icon name="save" :size="20" /> Simpan Pengaturan
        </button>
    </form>
</x-layouts.admin>
