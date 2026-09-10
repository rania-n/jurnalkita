<x-layouts.app title="Ajukan Dispensasi">
    <x-page-header
        title="Form Pengajuan Dispensasi"
        subtitle="Diajukan oleh guru piket"
        :back="route('dispensasi.index')"
    />

    <form method="POST" action="{{ route('dispensasi.store') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
        @csrf

        <x-ui.select label="Siswa" name="siswa_id">
            <option value="" disabled selected hidden>Pilih siswa</option>
            @foreach ($kelasList as $k)
                <optgroup label="{{ $k->nama }}">
                    @foreach ($k->siswas->sortBy('nama') as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </x-ui.select>

        <x-ui.input label="Tanggal" name="tanggal" type="date" :value="old('tanggal', now()->toDateString())" />

        <div class="flex gap-3">
            <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai" class="flex-1">
                <option value="">Sehari penuh</option>
                @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}">Jam ke-{{ $i }}</option>@endfor
            </x-ui.select>
            <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai" class="flex-1">
                <option value="">Sehari penuh</option>
                @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}">Jam ke-{{ $i }}</option>@endfor
            </x-ui.select>
        </div>

        <x-ui.textarea label="Alasan Dispensasi" name="alasan" :rows="3" placeholder="Contoh: mengikuti lomba tingkat kabupaten.">{{ old('alasan') }}</x-ui.textarea>
        <x-ui.input label="No. HP yang bisa dihubungi (opsional)" name="no_hp" inputmode="numeric" :value="old('no_hp')" />

        <x-ui.upload label="Surat / Bukti Pendukung (opsional)" name="surat" accept="image/*,application/pdf" title="Lampirkan surat atau foto" hint="JPG, PNG, atau PDF" />

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="send">Ajukan ke Waka Kesiswaan</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
