<?php

$file = 'resources/views/piket/presensi-siswa.blade.php';
$content = file_get_contents($file);

$search2 = '<input id="surat" name="surat" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-xl border border-surface-alt bg-card px-3 py-3 text-sm text-ink file:mr-3 file:rounded-lg file:border-0 file:bg-surface-alt file:px-3 file:py-2 file:font-semibold" required>';

$replace2 = "<input id=\"surat\" name=\"surat\" type=\"file\" accept=\".jpg,.jpeg,.png,.pdf\" class=\"block w-full rounded-xl border border-surface-alt bg-card px-3 py-3 text-sm text-ink file:mr-3 file:rounded-lg file:border-0 file:bg-surface-alt file:px-3 file:py-2 file:font-semibold\" {{ \$presensiTerpilih?->surat_path ? '' : 'required' }}>";

$content = str_replace($search2, $replace2, $content);
file_put_contents($file, $content);
