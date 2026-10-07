<?php

$file = 'resources/views/components/ui/status-badge.blade.php';
$content = file_get_contents($file);

$search = "        'dispensasi' => ['bg-dispen-soft text-dispen', 'Dispensasi'],";
$replace = "        'dispensasi' => ['bg-dispen-soft text-dispen', 'Dispensasi'],
        'izin_keluar' => ['bg-alpha-soft text-alpha', 'Izin Keluar'],
        'izin_terlambat' => ['bg-alpha-soft text-alpha', 'Terlambat'],";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
