<x-layouts.app title="Ajukan Dispensasi">
    <x-page-header
        title="Form Pengajuan Dispensasi"
        subtitle="Diajukan oleh guru piket"
        :back="route('dispensasi.index')"
    />

    <form method="POST" action="{{ route('dispensasi.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-ui.select label="Siswa" name="siswa_id" class="sm:col-span-2">
                <option value="" disabled selected hidden>Pilih siswa</option>
                @foreach ($kelasList as $k)
                    <optgroup label="{{ $k->nama }}">
                        @foreach ($k->siswas->sortBy('nama') as $s)
                            <option value="{{ $s->id }}" @selected(old('siswa_id') == $s->id)>{{ $s->nama }} · {{ $s->nis }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </x-ui.select>

            <x-ui.input label="Tanggal" name="tanggal" type="date" :value="old('tanggal', now()->toDateString())" />
            <x-ui.input label="Sampai Tanggal (opsional)" name="tanggal_selesai" type="date" :value="old('tanggal_selesai')" />
            <p class="-mt-2 text-xs text-muted-2 sm:col-span-2">Kosongkan "Sampai Tanggal" kalau dispensasinya cuma 1 hari. Isi kalau lebih dari 1 hari (mis. sakit 3 hari).</p>

            <x-ui.select label="Jam ke- (mulai)" name="jam_ke_mulai">
                <option value="">Sehari penuh</option>
                @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_mulai') == $i)>Jam ke-{{ $i }}</option>@endfor
            </x-ui.select>
            <x-ui.select label="Jam ke- (selesai)" name="jam_ke_selesai">
                <option value="">Sampai selesai hari itu</option>
                @for ($i = 1; $i <= 13; $i++)<option value="{{ $i }}" @selected(old('jam_ke_selesai') == $i)>Jam ke-{{ $i }}</option>@endfor
            </x-ui.select>
            <p class="-mt-2 text-xs text-muted-2 sm:col-span-2">
                Kosongkan keduanya kalau izin berlaku sehari penuh. Kalau cuma tahu jam
                mulainya (mis. keluar dari jam ke-4), boleh isi "mulai" saja dan biarkan
                "selesai" kosong — otomatis berarti sampai selesai hari itu.
            </p>

            <x-ui.textarea label="Alasan Dispensasi" name="alasan" :rows="3" class="sm:col-span-2" placeholder="Contoh: mengikuti lomba tingkat kabupaten.">{{ old('alasan') }}</x-ui.textarea>

            <x-ui.input label="No. HP yang bisa dihubungi (opsional)" name="no_hp" inputmode="numeric" :value="old('no_hp')" />

            <x-ui.upload label="Surat / Bukti Pendukung (opsional)" name="surat" accept="image/*,application/pdf" title="Lampirkan surat atau foto" hint="JPG, PNG, atau PDF" class="sm:col-span-2" />
        </div>

        <x-ui.sticky-bar>
            <x-ui.button type="submit" block icon="send">Ajukan ke Waka Kesiswaan</x-ui.button>
        </x-ui.sticky-bar>
    </form>
</x-layouts.app>
