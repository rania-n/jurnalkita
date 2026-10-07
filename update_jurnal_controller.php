<?php
$file = 'app/Http/Controllers/Guru/JurnalController.php';
$content = file_get_contents($file);

$search = "'presensi.*.status' => ['required', 'in:hadir,sakit,izin,alpha,dispensasi'],";
$replace = "'presensi.*.status' => ['required', 'in:hadir,sakit,izin,izin_keluar,izin_terlambat,alpha,dispensasi'],";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
