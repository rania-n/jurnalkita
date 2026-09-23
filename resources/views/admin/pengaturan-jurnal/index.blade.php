@php
    $deskripsi = [
        'disiplin' => 'Guru cuma bisa isi jurnal pas jam pelajarannya BENERAN lagi jalan -- dikunci otomatis ke jadwal itu. Istirahat/pergantian jam diblokir total, nggak bisa isi apa-apa. Baru bebas pilih jadwal lain kalau udah beneran pulang sekolah (buat susulan/testing).',
        'bebas_hari_ini' => 'Guru bebas pilih jadwal HARI INI kapan aja sepanjang hari -- nggak dikunci ke jam pelajaran yang lagi jalan, nggak diblokir pas istirahat. Cocok kalau sekolah belum siap disiplin ketat soal jam.',
        'bebas_kemarin' => 'Sama kayak "Bebas isi hari ini", DITAMBAH guru bisa isi susulan buat jadwal KEMARIN juga. Cocok buat kelas yang nggak rutin masuk tiap hari (mis. lagi PKL/prakerin) -- guru piket/wali tetap bisa isi jurnal susulan pas balik ngajar.',
    ];
    $icon = ['disiplin' => 'lock_clock', 'bebas_hari_ini' => 'today', 'bebas_kemarin' => 'event_repeat'];
@endphp

<x-layouts.admin title="Pengaturan Isi Jurnal" heading="Pengaturan Isi Jurnal">
    <x-admin.page title="Pengaturan Isi Jurnal" subtitle="Atur seberapa ketat aturan JAM buat guru isi jurnal mengajar" />

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
