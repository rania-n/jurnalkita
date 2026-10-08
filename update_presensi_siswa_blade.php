<?php

$file = 'resources/views/piket/presensi-siswa.blade.php';
$content = file_get_contents($file);

$search1 = "<x-ui.choice
                        label=\"Status Kehadiran\"
                        name=\"status\"
                        :options=\"['sakit' => 'Sakit', 'izin' => 'Izin']\"
                        :tones=\"['sakit' => 'sakit', 'izin' => 'izin']\"
                        :value=\"old('status', \$presensiTerpilih?->status)\"
                        required
                    />";

$replace1 = "<x-ui.choice
                        label=\"Status Kehadiran *\"
                        name=\"status\"
                        :options=\"['sakit' => 'Sakit', 'izin' => 'Izin', 'izin_keluar' => 'Izin Keluar', 'izin_terlambat' => 'Terlambat']\"
                        :tones=\"['sakit' => 'sakit', 'izin' => 'izin', 'izin_keluar' => 'alpha', 'izin_terlambat' => 'alpha']\"
                        :value=\"old('status', \$presensiTerpilih?->status)\"
                        required
                    />
                    
                    <x-admin.f-date name=\"tanggal_selesai\" label=\"Sampai Tanggal (Masa Berlaku)\" :value=\"old('tanggal_selesai', request('tanggal'))\" :min=\"request('tanggal')\" />";

$search2 = '<x-ui.label for="surat">Surat izin atau bukti (opsional)</x-ui.label>
                        <input id="surat" name="surat" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-xl border border-surface-alt bg-card px-3 py-3 text-sm text-ink file:mr-3 file:rounded-lg file:border-0 file:bg-surface-alt file:px-3 file:py-2 file:font-semibold">';

$replace2 = '<x-ui.label for="surat">Surat izin atau bukti *</x-ui.label>
                        <input id="surat" name="surat" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-xl border border-surface-alt bg-card px-3 py-3 text-sm text-ink file:mr-3 file:rounded-lg file:border-0 file:bg-surface-alt file:px-3 file:py-2 file:font-semibold" required>';

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);
file_put_contents($file, $content);
